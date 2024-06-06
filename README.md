# Custom Product Input Fields

**Custom Product Input Fields** is a WooCommerce plugin that allows you to add custom input fields to product pages. These fields can be configured via a JSON editor in the WordPress admin panel. The input fields will be displayed on the product page before the checkout button, and their values will be stored and displayed in the cart, order details, and admin order details.

## Features

- Add custom input fields to WooCommerce product pages.
- Supports various input types: text, number, radio, color, file.
- Easy configuration using a JSON editor in the WordPress admin panel.
- Display custom field values in the cart, order details, and admin order details.
- Handle file uploads and display file names in the cart and file URLs in the order details.

## Installation

1. **Download** the plugin files.
2. **Upload** the plugin to your WordPress site. You can do this by uploading the files to the `wp-content/plugins` directory or by uploading the ZIP file via the WordPress admin panel.
3. **Activate** the plugin through the 'Plugins' menu in WordPress.

## Usage

1. Navigate to the **Product Input Fields** menu in the WordPress admin panel.
2. Configure your custom input fields using the JSON editor.

### Example JSON Configuration

```json
[
    {
        "type": "text",
        "label": "Custom Text Field",
        "required": true
    },
    {
        "type": "number",
        "label": "Custom Number Field"
    },
    {
        "type": "radio",
        "label": "Custom Radio Field",
        "options": ["Option 1", "Option 2", "Option 3"],
        "required": true
    },
    {
        "type": "color",
        "label": "Custom Color Field"
    },
    {
        "type": "file",
        "label": "Custom File Field"
    }
]
