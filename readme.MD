# Simple Block Builder - Custom ACF Blocks Made Easy

![WordPress Version](https://img.shields.io/badge/WordPress-%3E%3D6.0-blue)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)
![License](https://img.shields.io/badge/License-GPLv2-green)

**Simple Block Builder** is a WordPress plugin that empowers developers and site builders to create custom Gutenberg blocks directly from the WordPress dashboard using **Advanced Custom Fields (ACF) Pro**.

Say goodbye to manual block registration and boilerplate code. Just create a "Custom Block", add your fields, and write your template code right in the admin area.

## 🚀 Features

- **Zero Boilerplate:** No need to touch `functions.php` or create manual `acf_register_block_type` calls.
- **In-Dashboard Editor:** Write HTML, PHP, CSS, and JavaScript directly in the plugin interface.
- **Dynamic Asset Management:** Automatically generates and enqueues unique CSS and JS files for each block instance.
- **ACF Integration:** Fully compatible with ACF's rendering engine and field types.
- **Icon Picker:** Built-in support for WordPress Dashicons.
- **Developer Friendly:** Supports PHP execution in templates for advanced logic (e.g., `get_field()`).

## 📋 Prerequisites

- **WordPress** 6.0 or higher.
- **Advanced Custom Fields Pro** (Required for block registration features).

## 🛠 Installation

1.  **Download:** Clone this repository or download the ZIP file.
2.  **Upload:** Place the `simple-block-builder` folder into your `/wp-content/plugins/` directory.
3.  **Activate:** Go to the WordPress Admin > Plugins and activate **Simple Block Builder**.
4.  **Check ACF:** Ensure **Advanced Custom Fields Pro** is installed and active.

## 📖 Usage

### 1. Create a New Block

Navigate to **Custom Blocks > Add New** in the WordPress admin sidebar.

### 2. Configure Settings

- **Block Title:** Enter a name for your block (e.g., "Hero Header").
- **Block Slug:** A unique identifier (e.g., `hero-header`).
- **Icon:** Select a Dashicon to represent your block in the Gutenberg inserter.

### 3. Add Content & Logic

Use the provided editor fields to build your block:

- **Template (HTML/PHP):**
  ```php
  <div class="hero-container">
      <h1><?php echo get_field('headline'); ?></h1>
      <p><?php echo get_field('subtext'); ?></p>
  </div>
  ```
- **CSS:**
  Target your block using the `selector` keyword (automatically replaced with the unique block class).
  ```css
  selector {
    background: #f5f5f5;
    padding: 2rem;
  }
  selector h1 {
    color: #333;
  }
  ```
- **JavaScript:**
  Run scripts when the block renders.
  ```javascript
  console.log('Hero block loaded!');
  ```

### 4. Add Fields via ACF

1.  Go to **Custom Fields > Field Groups**.
2.  Create a new Field Group.
3.  In **Locations**, set the rule to **Block is equal to [Your New Block Name]**.
4.  Add your fields (Text, Image, Repeater, etc.).

### 5. Use It!

Go to any Page or Post, search for your new block in the Gutenberg editor, and start adding content.

## 📂 Project Structure

```text
simple-block-builder/
├── simple-block-builder.php  # Main plugin file
├── readme.txt                # WordPress.org repository readme
├── README.md                 # GitHub documentation
└── ...
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1.  Fork the project
2.  Create your feature branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

## 📄 License

Distributed under the GPLv2 License. See `simple-block-builder.php` for more information.
