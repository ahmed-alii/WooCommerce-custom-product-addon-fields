<?php
/*
Plugin Name: Custom Product Input Fields
Description: Adds custom input fields to WooCommerce product pages before the checkout button based on JSON configuration.
Version: 1.2
Author: Ahmed @codecapital
*/

// Add a menu item to the admin dashboard
add_action('admin_menu', 'cpif_add_admin_menu');
function cpif_add_admin_menu() {
    add_menu_page('Custom Product Input Fields', 'Product Input Fields', 'manage_options', 'cpif', 'cpif_options_page');
}

// Display the plugin options page
function cpif_options_page() {
    ?>
    <div class="wrap">
        <h1>Custom Product Input Fields</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cpif_options_group');
            do_settings_sections('cpif');
            submit_button();
            ?>
        </form>
        <div id="json-editor" style="height: 500px; width: 100%;"></div>
        <textarea id="cpif_json_config" name="cpif_json_config" style="display: none;"><?php echo esc_textarea(get_option('cpif_json_config', '[]')); ?></textarea>
    </div>
    <?php
}

// Register and define the settings
add_action('admin_init', 'cpif_settings_init');
function cpif_settings_init() {
    register_setting('cpif_options_group', 'cpif_json_config');
    
    add_settings_section(
        'cpif_settings_section', 
        __('Custom Input Fields Configuration', 'cpif'), 
        null, 
        'cpif'
    );
    
    add_settings_field(
        'cpif_json_config', 
        __('JSON Configuration', 'cpif'), 
        'cpif_json_config_render', 
        'cpif', 
        'cpif_settings_section'
    );
}

function cpif_json_config_render() {
    ?>
    <div id="json-editor" style="height: 500px; width: 100%;"></div>
    <textarea id="cpif_json_config" name="cpif_json_config" style="display: none;"><?php echo esc_textarea(get_option('cpif_json_config', '[]')); ?></textarea>
    <?php
}

// Enqueue ACE Editor script
add_action('admin_enqueue_scripts', 'cpif_enqueue_ace_editor');
function cpif_enqueue_ace_editor() {
    wp_enqueue_script('ace-editor', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js', array(), '1.4.12', true);
    wp_enqueue_script('cpif-json-editor', plugin_dir_url(__FILE__) . 'cpif-json-editor.js', array('ace-editor'), '1.0', true);
}


// Add custom fields to product pages
add_action('woocommerce_before_add_to_cart_button', 'cpif_display_custom_fields');
function cpif_display_custom_fields() {
    global $product;

    $json_config = get_option('cpif_json_config', '[]');
    $fields = json_decode($json_config, true);

    if ($fields && is_array($fields)) {
        foreach ($fields as $field) {
            $field_id = sanitize_title($field['label']);
            $required = isset($field['required']) && $field['required'] ? 'required' : '';
            switch ($field['type']) {
                case 'text':
                case 'number':
                    echo '<p class="form-row form-row-wide">
                            <label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>
                            <input type="' . esc_attr($field['type']) . '" class="input-text" name="' . esc_attr($field_id) . '" id="' . esc_attr($field_id) . '" ' . $required . '>
                          </p>';
                    break;
                case 'radio':
                    echo '<p class="form-row form-row-wide"><label>' . esc_html($field['label']) . '</label><br>';
                    if (is_array($field['options'])) {
                        foreach ($field['options'] as $option) {
                            echo '<label><input type="radio" name="' . esc_attr($field_id) . '" value="' . esc_attr($option) . '" ' . $required . '> ' . esc_html($option) . '</label><br>';
                        }
                    }
                    echo '</p>';
                    break;
                case 'color':
                    echo '<p class="form-row form-row-wide">
                            <label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>
                            <input type="color" class="input-text" name="' . esc_attr($field_id) . '" id="' . esc_attr($field_id) . '" ' . $required . '>
                          </p>';
                    break;
                case 'file':
                    echo '<p class="form-row form-row-wide">
                            <label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>
                            <input type="file" class="input-text" data-name="' . esc_attr($field_id) . '" id="' . esc_attr($field_id) . '" ' . $required . '>
                          </p>';
                    break;
            }
        }
    }
}

// Validate and save custom fields in the cart
add_filter('woocommerce_add_cart_item_data', 'cpif_add_cart_item_data', 10, 2);
function cpif_add_cart_item_data($cart_item_data, $product_id) {
    $json_config = get_option('cpif_json_config', '[]');
    $fields = json_decode($json_config, true);

    if ($fields && is_array($fields)) {
        foreach ($fields as $field) {
            $field_id = sanitize_title($field['label']);
            if (isset($_POST[$field_id])) {
                $cart_item_data[$field_id] = sanitize_text_field($_POST[$field_id]);
            } elseif (isset($_POST[$field_id . '_url'])) {
                $cart_item_data[$field_id] = sanitize_text_field($_POST[$field_id . '_url']);
            }
        }
    }

    return $cart_item_data;
}

// Display custom fields in the cart
add_filter('woocommerce_get_item_data', 'cpif_get_item_data', 10, 2);
function cpif_get_item_data($item_data, $cart_item) {
    $json_config = get_option('cpif_json_config', '[]');
    $fields = json_decode($json_config, true);

    if ($fields && is_array($fields)) {
        foreach ($fields as $field) {
            $field_id = sanitize_title($field['label']);
            if (!empty($cart_item[$field_id])) {
                $value = $cart_item[$field_id];
                if ($field['type'] === 'file') {
                    $value = basename($cart_item[$field_id]);
                }
                $item_data[] = array(
                    'key' => $field['label'],
                    'value' => wc_clean($value),
                );
            }
        }
    }

    return $item_data;
}

// Save custom fields to order meta
add_action('woocommerce_checkout_create_order_line_item', 'cpif_checkout_create_order_line_item', 10, 4);
function cpif_checkout_create_order_line_item($item, $cart_item_key, $values, $order) {
    $json_config = get_option('cpif_json_config', '[]');
    $fields = json_decode($json_config, true);

    if ($fields && is_array($fields)) {
        foreach ($fields as $field) {
            $field_id = sanitize_title($field['label']);
            if (!empty($values[$field_id])) {
                $item->add_meta_data($field['label'], $values[$field_id], true);
            }
        }
    }
}

// Display custom fields in the admin order details
add_action('woocommerce_admin_order_data_after_order_details', 'cpif_display_custom_fields_admin_order', 10, 1);
function cpif_display_custom_fields_admin_order($order) {
    $items = $order->get_items();

    foreach ($items as $item_id => $item) {
        $json_config = get_option('cpif_json_config', '[]');
        $fields = json_decode($json_config, true);

        if ($fields && is_array($fields)) {
            echo '<div class="cpif_admin_order_meta">';
            foreach ($fields as $field) {
                $field_id = sanitize_title($field['label']);
                $value = $item->get_meta($field['label']);
                if (!empty($value)) {
                    if ($field['type'] === 'file') {
                        $value = '<a href="' . esc_url($value) . '" target="_blank">' . __('View file', 'cpif') . '</a>';
                    }
                    echo '<p><strong>' . esc_html($field['label']) . ':</strong> ' . $value . '</p>';
                }
            }
            echo '</div>';
        }
    }
}

// Enqueue the necessary scripts for file upload
add_action('wp_enqueue_scripts', 'cpif_enqueue_scripts');
function cpif_enqueue_scripts() {
    wp_enqueue_script('cpif-file-upload', plugin_dir_url(__FILE__) . 'cpif-file-upload.js', array('jquery'), '1.0', true);
    wp_localize_script('cpif-file-upload', 'cpif_nonce', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cpif_nonce')
    ));
}

// AJAX handler for file upload
add_action('wp_ajax_cpif_upload_file', 'cpif_upload_file');
add_action('wp_ajax_nopriv_cpif_upload_file', 'cpif_upload_file');
function cpif_upload_file() {
    check_ajax_referer('cpif_nonce', 'nonce');

    $file = $_FILES['file'];
    $uploaded_file = wp_handle_upload($file, array('test_form' => false));

    if (isset($uploaded_file['url'])) {
        wp_send_json_success(array('url' => $uploaded_file['url']));
    } else {
        wp_send_json_error(array('error' => __('File upload failed.', 'cpif')));
    }

    wp_die();
}
