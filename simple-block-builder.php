<?php
/**
 * Plugin Name: Simple Block Builder - Custom ACF Blocks Made Easy
 * Description: Build custom Gutenberg blocks directly in the WordPress dashboard using ACF Pro. Write HTML, PHP, CSS, and JS instantly.
 * Version: 1.0
 * Author: The Code Beast LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simple_Block_Builder {

	public function __construct() {
		// 1. Register the "Custom Block" Post Type
		add_action( 'init', [ $this, 'register_post_type' ] );
		// 2. Add Settings Fields (Template, CSS, JS)
		add_action( 'init', [ $this, 'register_meta_fields' ] );
		// 3. Register the actual Gutenberg Blocks
		add_action( 'init', [ $this, 'register_dynamic_blocks' ], 20 );
		// 4. Generate CSS/JS files on save
		add_action( 'acf/save_post', [ $this, 'generate_block_assets' ], 20 );
		// 5. Enhance Admin UI (Icon Picker)
		add_action( 'acf/input/admin_head', [ $this, 'admin_head' ] );
	}

	/**
	 * Register the Post Type to manage blocks.
	 */
	public function register_post_type() {
		$labels = [
			'name'               => 'Custom Blocks',
			'singular_name'      => 'Custom Block',
			'menu_name'          => 'Custom Blocks',
			'name_admin_bar'     => 'Custom Block',
			'add_new'            => 'Add Block',
			'add_new_item'       => 'Add New Block',
			'new_item'           => 'New Block',
			'edit_item'          => 'Edit Block',
			'view_item'          => 'View Block',
			'all_items'          => 'All Blocks',
			'search_items'       => 'Search Blocks',
			'parent_item_colon'  => 'Parent Blocks:',
			'not_found'          => 'No blocks found.',
			'not_found_in_trash' => 'No blocks found in Trash.',
		];

		register_post_type( 'sbb_block', [
			'label'               => 'Custom Blocks',
			'public'              => false, // Internal use only
			'show_ui'             => true,
			'show_in_menu'        => true,
			'supports'            => [ 'title', 'revisions' ], // We use ACF for the rest
			'menu_icon'           => 'dashicons-layout',
			'menu_position'       => 25,
		] );
	}

	/**
	 * Register ACF fields for the Block Builder itself.
	 * These fields appear when you edit a "Custom Block".
	 */
	public function register_meta_fields() {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			acf_add_local_field_group( [
				'key' => 'group_sbb_settings',
				'title' => 'Block Configuration',
				'fields' => [
					[
						'key' => 'field_sbb_slug',
						'label' => 'Block Slug',
						'name' => 'sbb_slug',
						'type' => 'text',
						'instructions' => 'Unique ID (e.g., hero-header).',
						'required' => 1,
					],
					[
						'key' => 'field_sbb_icon',
						'label' => 'Icon',
						'name' => 'sbb_icon',
						'type' => 'select',
						'ui' => 1,
						'choices' => $this->get_dashicons_list(),
						'default_value' => 'admin-generic',
						'wrapper' => [
							'class' => 'sbb-icon-select',
						],
					],
					[
						'key' => 'field_sbb_template',
						'label' => 'Template Code (HTML/PHP)',
						'name' => 'sbb_template',
						'type' => 'textarea',
						'instructions' => 'Enter your HTML/PHP. Use get_field("field_name") to show data.',
						'rows' => 12,
						'new_lines' => '', // Important: Disable auto-paragraphs
					],
					[
						'key' => 'field_sbb_css',
						'label' => 'Custom CSS',
						'name' => 'sbb_css',
						'type' => 'textarea',
						'instructions' => 'Use "selector" to target this block wrapper (e.g., selector h1 { color: red; }).',
						'rows' => 6,
						'new_lines' => '',
					],
					[
						'key' => 'field_sbb_js',
						'label' => 'Custom JS',
						'name' => 'sbb_js',
						'type' => 'textarea',
						'instructions' => 'JavaScript to run when this block is rendered.',
						'rows' => 6,
						'new_lines' => '',
					],
				],
				'location' => [
					[
						[
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'sbb_block',
						],
					],
				],
			] );
		}
	}

	/**
	 * Generate CSS/JS files when a Custom Block is saved.
	 */
	public function generate_block_assets( $post_id ) {
		// Only run for our post type
		if ( get_post_type( $post_id ) !== 'sbb_block' ) {
			return;
		}

		$css = get_field( 'sbb_css', $post_id );
		$js  = get_field( 'sbb_js', $post_id );

		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'] . '/simple-block-builder';

		if ( ! file_exists( $base_dir ) ) {
			wp_mkdir_p( $base_dir );
		}

		// Handle CSS
		$css_file = $base_dir . '/block-' . $post_id . '.css';
		if ( $css ) {
			// Replace 'selector' with a shared class based on Post ID
			// We use a class .sbb-block-{id} so one file serves all instances
			$parsed_css = str_replace( 'selector', '.sbb-block-' . $post_id, $css );
			file_put_contents( $css_file, $parsed_css );
		} elseif ( file_exists( $css_file ) ) {
			unlink( $css_file );
		}

		// Handle JS
		$js_file = $base_dir . '/block-' . $post_id . '.js';
		if ( $js ) {
			file_put_contents( $js_file, $js );
		} elseif ( file_exists( $js_file ) ) {
			unlink( $js_file );
		}
	}

	/**
	 * Loop through "Custom Block" posts and register them as ACF Blocks.
	 */
	public function register_dynamic_blocks() {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}

		$blocks = get_posts( [
			'post_type'   => 'sbb_block',
			'numberposts' => -1,
			'post_status' => 'publish',
		] );

		foreach ( $blocks as $block_post ) {
			$slug = get_field( 'sbb_slug', $block_post->ID );
			if ( ! $slug ) {
				$slug = sanitize_title( $block_post->post_title );
			}

			acf_register_block_type( [
				'name'            => $slug,
				'title'           => $block_post->post_title,
				'description'     => 'Custom block created via Backend.',
				'render_callback' => [ $this, 'render_block_callback' ],
				'category'        => 'formatting',
				'icon'            => get_field( 'sbb_icon', $block_post->ID ) ?: 'admin-generic',
				'keywords'        => [ 'custom', $slug ],
				'mode'            => 'preview',
				// Pass the definition ID so we can find the code later
				'sbb_post_id'     => $block_post->ID,
			] );
		}
	}

	/**
	 * The Render Callback
	 * Outputs the CSS, HTML/PHP, and JS for the block.
	 */
	public function render_block_callback( $block, $content = '', $is_preview = false, $post_id = 0 ) {
		// Retrieve the ID of the "Custom Block" post that defines this block
		$sbb_post_id = isset( $block['sbb_post_id'] ) ? $block['sbb_post_id'] : 0;

		if ( ! $sbb_post_id ) {
			echo '<p>Block definition not found.</p>';
			return;
		}

		$template = get_field( 'sbb_template', $sbb_post_id );
		
		// Prepare paths for external assets
		$upload_dir = wp_upload_dir();
		$base_url   = $upload_dir['baseurl'] . '/simple-block-builder';
		$base_path  = $upload_dir['basedir'] . '/simple-block-builder';
		
		$css_path = $base_path . '/block-' . $sbb_post_id . '.css';
		$js_path  = $base_path . '/block-' . $sbb_post_id . '.js';

		// 1. Enqueue CSS
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style( 'sbb-block-' . $sbb_post_id, $base_url . '/block-' . $sbb_post_id . '.css', [], filemtime( $css_path ) );
		} else {
			// Fallback: Inline CSS (Legacy)
			$css = get_field( 'sbb_css', $sbb_post_id );
			if ( $css ) {
				// Replace 'selector' with the unique block ID (instance specific)
				$parsed_css = str_replace( 'selector', '#' . $block['id'], $css );
				echo '<style>' . $parsed_css . '</style>';
			}
		}

		// 2. Output Template (HTML/PHP)
		// Add the shared class used by the external CSS
		$wrapper_class = 'sbb-block-' . $sbb_post_id;
		if ( ! empty( $block['className'] ) ) {
			$wrapper_class .= ' ' . $block['className'];
		}

		echo '<div id="' . esc_attr( $block['id'] ) . '" class="' . esc_attr( $wrapper_class ) . '">';
		if ( $template ) {
			// Evaluate PHP code stored in the database
			ob_start();
			// We prepend closing PHP tag because eval expects PHP code, 
			// but we want to allow HTML by default.
			eval( '?>' . $template );
			echo ob_get_clean();
		}
		echo '</div>';

		// 3. Enqueue JS
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script( 'sbb-block-' . $sbb_post_id, $base_url . '/block-' . $sbb_post_id . '.js', ['jquery'], filemtime( $js_path ), true );
		} else {
			// Fallback: Inline JS (Legacy)
			$js = get_field( 'sbb_js', $sbb_post_id );
			if ( $js ) {
				echo '<script>' . $js . '</script>';
			}
		}
	}

	/**
	 * Output custom JS/CSS for the ACF Admin to render Dashicons in Select2.
	 */
	public function admin_head() {
		?>
		<style>
			.sbb-icon-select .select2-results__option { display: flex; align-items: center; }
			.sbb-icon-select .select2-results__option .dashicons { margin-right: 10px; font-size: 20px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }
			.sbb-icon-select .select2-selection__rendered { display: flex !important; align-items: center; }
			.sbb-icon-select .select2-selection__rendered .dashicons { margin-right: 8px; margin-top:3px; }
		</style>
		<script>
		(function($){
			if( typeof acf === 'undefined' ) return;
			
			function formatDashicon(state) {
				if (!state.id) { return state.text; }
				var iconClass = 'dashicons-' + state.id;
				return $('<span><span class="dashicons ' + iconClass + '"></span> ' + state.text + '</span>');
			}

			acf.add_filter('select2_args', function( args, $select, settings, field, instance ){
				if( $select.closest('.acf-field').hasClass('sbb-icon-select') ) {
					args.templateResult = formatDashicon;
					args.templateSelection = formatDashicon;
				}
				return args;
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Returns a list of Dashicons for the picker.
	 */
	public function get_dashicons_list() {
		return [
			'admin-generic' => 'Generic',
			'admin-home' => 'Home',
			'admin-media' => 'Media',
			'admin-page' => 'Page',
			'admin-comments' => 'Comments',
			'admin-appearance' => 'Appearance',
			'admin-plugins' => 'Plugins',
			'admin-users' => 'Users',
			'admin-tools' => 'Tools',
			'admin-settings' => 'Settings',
			'admin-network' => 'Network',
			'welcome-widgets-menus' => 'Widgets',
			'welcome-write-blog' => 'Write',
			'welcome-edit-page' => 'Edit Page',
			'welcome-add-page' => 'Add Page',
			'welcome-view-site' => 'View Site',
			'format-aside' => 'Aside',
			'format-image' => 'Image',
			'format-gallery' => 'Gallery',
			'format-video' => 'Video',
			'format-status' => 'Status',
			'format-quote' => 'Quote',
			'format-chat' => 'Chat',
			'format-audio' => 'Audio',
			'camera' => 'Camera',
			'images-alt' => 'Images',
			'images-alt2' => 'Images 2',
			'video-alt' => 'Video Alt',
			'video-alt2' => 'Video Alt 2',
			'video-alt3' => 'Video Alt 3',
			'saved' => 'Saved',
			'building' => 'Building',
			'store' => 'Store',
			'cart' => 'Cart',
			'clipboard' => 'Clipboard',
			'tickets' => 'Tickets',
			'money' => 'Money',
			'layout' => 'Layout',
			'editor-bold' => 'Bold',
			'editor-italic' => 'Italic',
			'editor-ul' => 'List',
			'editor-ol' => 'Ordered List',
			'editor-quote' => 'Quote',
			'editor-alignleft' => 'Align Left',
			'editor-aligncenter' => 'Align Center',
			'editor-alignright' => 'Align Right',
			'editor-insertmore' => 'Insert More',
			'editor-spellcheck' => 'Spellcheck',
			'editor-expand' => 'Expand',
			'editor-contract' => 'Contract',
			'editor-kitchensink' => 'Kitchen Sink',
			'editor-underline' => 'Underline',
			'editor-justify' => 'Justify',
			'editor-textcolor' => 'Text Color',
			'editor-paste-word' => 'Paste Word',
			'editor-paste-text' => 'Paste Text',
			'editor-removeformatting' => 'Remove Formatting',
			'editor-video' => 'Editor Video',
			'editor-customchar' => 'Custom Char',
			'editor-outdent' => 'Outdent',
			'editor-indent' => 'Indent',
			'editor-help' => 'Help',
			'editor-strikethrough' => 'Strikethrough',
			'editor-unlink' => 'Unlink',
			'editor-rtl' => 'RTL',
			'align-left' => 'Align Left',
			'align-right' => 'Align Right',
			'align-center' => 'Align Center',
			'align-none' => 'Align None',
			'lock' => 'Lock',
			'unlock' => 'Unlock',
			'calendar' => 'Calendar',
			'calendar-alt' => 'Calendar Alt',
			'visibility' => 'Visibility',
			'hidden' => 'Hidden',
			'email' => 'Email',
			'email-alt' => 'Email Alt',
			'edit' => 'Edit',
			'trash' => 'Trash',
			'star-filled' => 'Star Filled',
			'star-half' => 'Star Half',
			'star-empty' => 'Star Empty',
			'flag' => 'Flag',
			'info' => 'Info',
			'warning' => 'Warning',
			'share' => 'Share',
			'share-alt' => 'Share Alt',
			'share-alt2' => 'Share Alt 2',
			'twitter' => 'Twitter',
			'rss' => 'RSS',
			'facebook' => 'Facebook',
			'facebook-alt' => 'Facebook Alt',
			'googleplus' => 'Google+',
			'networking' => 'Networking',
			'hammer' => 'Hammer',
			'art' => 'Art',
			'migrate' => 'Migrate',
			'performance' => 'Performance',
			'wordpress' => 'WordPress',
			'wordpress-alt' => 'WordPress Alt',
			'pressthis' => 'PressThis',
			'update' => 'Update',
			'screenoptions' => 'Screen Options',
			'cart' => 'Cart',
			'feedback' => 'Feedback',
			'cloud' => 'Cloud',
			'translation' => 'Translation',
			'tag' => 'Tag',
			'category' => 'Category',
			'archive' => 'Archive',
			'tagcloud' => 'Tag Cloud',
			'text' => 'Text',
			'media-archive' => 'Media Archive',
			'media-audio' => 'Media Audio',
			'media-code' => 'Media Code',
			'media-default' => 'Media Default',
			'media-document' => 'Media Document',
			'media-interactive' => 'Media Interactive',
			'media-spreadsheet' => 'Media Spreadsheet',
			'media-text' => 'Media Text',
			'media-video' => 'Media Video',
			'playlist-audio' => 'Playlist Audio',
			'playlist-video' => 'Playlist Video',
			'controls-play' => 'Play',
			'controls-pause' => 'Pause',
			'controls-forward' => 'Forward',
			'controls-skipforward' => 'Skip Forward',
			'controls-back' => 'Back',
			'controls-skipback' => 'Skip Back',
			'controls-repeat' => 'Repeat',
			'controls-volumeon' => 'Volume On',
			'controls-volumeoff' => 'Volume Off',
			'google' => 'Google',
			'twitter' => 'Twitter',
			'facebook' => 'Facebook',
			'dismiss' => 'Dismiss',
			'no' => 'No',
			'yes' => 'Yes',
			'arrow-up-alt' => 'Arrow Up Alt',
			'arrow-down-alt' => 'Arrow Down Alt',
			'arrow-left-alt' => 'Arrow Left Alt',
			'arrow-right-alt' => 'Arrow Right Alt',
			'arrow-up-alt2' => 'Arrow Up Alt 2',
			'arrow-down-alt2' => 'Arrow Down Alt 2',
			'arrow-left-alt2' => 'Arrow Left Alt 2',
			'arrow-right-alt2' => 'Arrow Right Alt 2',
			'leftright' => 'Left Right',
			'sort' => 'Sort',
			'randomize' => 'Randomize',
			'list-view' => 'List View',
			'exerpt-view' => 'Excerpt View',
			'grid-view' => 'Grid View',
		];
	}
}

new Simple_Block_Builder();