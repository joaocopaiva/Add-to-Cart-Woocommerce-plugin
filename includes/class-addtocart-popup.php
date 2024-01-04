<?php
if (!defined('ABSPATH')) {
    exit;
}

class AddToCart_Popup {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_nopriv_add_to_cart_notification', array($this, 'ajax_add_to_cart_notification'));
        add_action('wp_ajax_add_to_cart_notification', array($this, 'ajax_add_to_cart_notification'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts'));
    }

    public function add_admin_menu() {
        add_menu_page('Add to Cart Popup', 'Add to Cart Popup', 'manage_options', 'addtocart-popup', array($this, 'display_admin_page'));
    }

    public function display_admin_page() {
        ?>
        <!-- CSS only here temporarily -->
        <style>
            #atc-admin-table .atc-layout-option input:checked + img {
                border-color: #0073e6;
            }
            #atc-admin-table .atc-layout-option {
                display: inline-block;
                margin-right: 10px;
                cursor: pointer;
                border: 5px solid transparent;
            }
            #atc-admin-table .atc-layout-option img {
                width: 150px;
                height: auto;
                border-radius: 10px;
                border: 5px solid #ddd;
            }
            #atc-admin-table .atc-input {
                display: none; /* Hide radio buttons */
            }
            #atc-admin-table .display-position-container {
                display: flex;
                align-items: center;
            }
            #atc-admin-table .display-position-box {
                border: 1px solid #000;
                padding: 2px;
                border-radius: 2px;
                width: 335px;
            }
            #atc-admin-table .display-position-buttons {
                display: flex;
            }
            #atc-admin-table .display-position-button {
                border: none;
                border-radius: 2px;
                background-color: transparent;
                width: 50%;
                cursor: pointer;
                padding: 5px;
                text-align: center;
            }
            #atc-admin-table .display-position-button:has(input:checked) {
                background-color: #0073e6;
                color: #fff;
            }
            #atc-admin-table .close-after-slider-container {
                display: flex;
                align-items: center;
                width: 340px;
            }
            #atc-admin-table .close-after-number {
                width: 80px;
            }
            #atc-admin-table .close-after-slider {
                margin-right: 10px;
                width: -moz-available;
                width: -webkit-fill-available;
            }
            .atc-display-condition-container {
                width: 340px;
                display: flex;
                flex-direction: column;
            }
            .atc-display-condition {
                width: calc(100% - 30px);
                margin-bottom: 10px;
            }
            .atc-display-button-container {
                display: flex;
                flex-direction: row;
            }
        </style>
        <div class="wrap">
            <h1>Add to Cart Popup Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('addtocart_popup');
                do_settings_sections('addtocart_popup');
                $current_conditions = get_option('atc_display_conditions', array('all_pages'));

                echo '<table id="atc-admin-table" class="form-table">';

                // Layout selection with clickable images
                echo '<tr><th scope="row">Layout</th><td>';
                $currentLayout = get_option('atc_layout', 'product_image_left');

                    // Image for "Product Image on the Left"
                    echo '<label class="atc-layout-option">
                        <input type="radio" name="atc_layout" value="product_image_left" class="atc-input" ' . checked($currentLayout, 'product_image_left', false) . ' />
                        <img src="' . plugins_url('../assets/atc-layout-left.png', __FILE__) . '" alt="Product Image on the Left">
                    </label>';

                    // Image for "Product Image as Background"
                    echo '<label class="atc-layout-option">
                        <input type="radio" name="atc_layout" value="product_image_background" class="atc-input" ' . checked($currentLayout, 'product_image_background', false) . ' />
                        <img src="' . plugins_url('../assets/atc-layout-bg.png', __FILE__) . '" alt="Product Image as Background">
                    </label>';

                echo '</td></tr>';

                // Display Position radio buttons
                $currentLayout = get_option('atc_display_position', 'top');
                echo '<tr><th scope="row">Display Position</th><td>
                <div class="display-position-container">
                    <div class="display-position-box">
                        <div class="display-position-buttons">';
                        // Top radio button
                        echo '<label class="display-position-button">
                            <input type="radio" name="atc_display_position" value="top" class="atc-input" ' . checked($currentLayout, 'top', false) . ' />
                            Top
                        </label>';
                        // Bottom radio button
                        echo '<label class="display-position-button">
                            <input type="radio" name="atc_display_position" value="bottom" class="atc-input" ' . checked($currentLayout, 'bottom', false) . ' />
                            Bottom
                        </label>';
                    echo '</div>
                    </div>
                </div>
                </td></tr>';

                // Close After (Seconds) input with a slider
                echo '<tr><th scope="row">Close After (Seconds)</th><td>
                <div class="close-after-slider-container">
                <input type="range" class="close-after-slider" name="close_after_seconds_slider" min="0" max="10" step="1" value="' . esc_attr(get_option('close_after_seconds', 3)) . '" onchange="updateCloseAfterSeconds(this.value)" />
                <input type="number" class="close-after-number" name="close_after_seconds" value="' . esc_attr(get_option('close_after_seconds', 3)) . '" onchange="updateCloseAfterSlider(this.value)" />
                </div>
                </td></tr>';

                // Display Conditions
                echo '<tr><th scope="row">Display Conditions</th><td>';
                $this->display_conditions_section_callback();
                echo '</td></tr>';

                // Added to Cart text
                echo '<tr><th scope="row">Added to Cart text</th><td>
                <div class="close-after-slider-container">
                <input type="text" name="atc_added_text" value="' . esc_attr(get_option('atc_added_text', 'Added to Cart')) . '"/>
                </div>
                </td></tr>';

                // Price text
                echo '<tr><th scope="row">Price text</th><td>
                <div class="close-after-slider-container">
                <input type="text" name="atc_price_text" value="' . esc_attr(get_option('atc_price_text', 'Price: ')) . '"/>
                </div>
                </td></tr>';

                echo '</table>';

                submit_button();
                ?>
            </form>
        </div>
        <script>
            function updateCloseAfterSlider(value) {
                document.querySelector('.close-after-slider').value = value;
            }

            function updateCloseAfterSeconds(value) {
                document.querySelector('input[name="close_after_seconds"]').value = value;
            }

            // Add Display Condition
            const addConditionButton = document.getElementById("atc-add-condition");
            addConditionButton.onclick = addCondition;

            // Function to update the stored conditions
            function updateStoredConditions(updatedConditions) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'atc_display_conditions';
                hiddenInput.value = JSON.stringify(updatedConditions);

                // Replace the existing hidden input with the updated one
                const form = document.querySelector('form');
                const existingHiddenInput = form.querySelector('input[name="atc_display_conditions"]');
                if (existingHiddenInput) {
                    form.replaceChild(hiddenInput, existingHiddenInput);
                } else {
                    form.appendChild(hiddenInput);
                }
            }

            function addCondition() {
                // Get the container for display conditions
                const displayConditionContainer = document.querySelector('.atc-display-condition-container');

                // Create a new container for the new display condition
                const newConditionContainer = document.createElement('div');
                newConditionContainer.className = 'atc-display-button-container';

                var index = <?php echo count(get_option('atc_display_conditions', array('all_pages')));?>

                // Create a new select element for the dropdown
                const newSelect = document.createElement('select');
                newSelect.name = 'atc_display_conditions[' + index + ']';
                newSelect.className = 'atc-display-condition';

                // Define the display condition options
                const options = {
                    'all_pages': 'All pages',
                    'shop_archive': 'Shop Archive',
                    'shop_archive_categories': 'Shop Archive Categories (product categories)',
                    'shop_archive_tags': 'Shop Archive Tags (product tags)',
                    'shop_archive_product_attributes': 'Shop Archive Product Attributes',
                    'single_products': 'Single Products',
                };

                // Add options to the new select element
                for (const value in options) {
                    const option = document.createElement('option');
                    option.value = value;
                    option.text = options[value];
                    newSelect.appendChild(option);
                }

                // Append the new select button to the new condition container
                newConditionContainer.appendChild(newSelect);

                // Find the last existing condition container
                const lastConditionContainer = displayConditionContainer.lastElementChild;

                // Insert the new condition container before the last existing container
                displayConditionContainer.insertBefore(newConditionContainer, lastConditionContainer);
            }

            // Function to remove a condition
            function removeCondition(index) {
                return function() {
                    // Remove the associated stored data
                    const displayConditions = document.querySelectorAll('.atc-display-condition');
                    const removedConditionValue = displayConditions[index].value;
                    const currentConditions = <?php echo json_encode($current_conditions); ?>;

                    const updatedConditions = currentConditions.filter(condition => condition !== removedConditionValue);
                    updateStoredConditions(updatedConditions);

                    // Remove the entire condition container when the remove button is clicked
                    const conditionContainer = document.querySelector('.atc-display-button-container:has(button#remove_display_' + index + ')');
                    conditionContainer.remove();
                };
            }

            // Add event listeners to existing remove buttons
            let removeButtons = document.querySelectorAll(".button.remove-condition");

            removeButtons.forEach((removeButton, index) => {
                const removeButtonIndex = removeButton.id.replace('remove_display_', '');
                removeButton.addEventListener('click', removeCondition(removeButtonIndex));
            });
        </script>
        <?php
    }

    function display_conditions_sanitize_callback($value) {
        if (is_array($value)) {
            // If it's already an array, return it as is
            return $value;
        } elseif (is_string($value) && !empty($value)) {
            // If it's a non-empty string, assume it's JSON-encoded and decode it
            return json_decode($value, true);
        } else {
            // Otherwise, return the default value (an empty array)
            return array('all_pages');
        }
    }
    
    public function register_settings() {
        register_setting('addtocart_popup', 'atc_layout', array(
            'type' => 'string',
            'default' => 'product_image_left',
        ));

        register_setting('addtocart_popup', 'atc_display_position', array(
            'type' => 'string',
            'default' => 'top',
        ));

        register_setting('addtocart_popup', 'close_after_seconds', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 3,
        ));
        // Allow filtering of 'close_after_seconds' option value
        $close_after_seconds = apply_filters('addtocart_popup_close_after_seconds', get_option('close_after_seconds', 3));
        // Set the 'close_after_seconds' option value
        update_option('close_after_seconds', $close_after_seconds);

        register_setting('addtocart_popup', 'atc_added_text', array(
            'type' => 'string',
            'default' => 'Added to Cart',
        ));

        register_setting('addtocart_popup', 'atc_price_text', array(
            'type' => 'string',
            'default' => 'Price: ',
        ));

        register_setting('addtocart_popup', 'atc_display_conditions', array(
            'type' => 'array',
            'default' => array('all_pages'),
            'sanitize_callback' => array($this, 'display_conditions_sanitize_callback'),
        ));
        
    }

    // Callback function for the Display Conditions section
    public function display_conditions_section_callback() {
        $options = array(
            'all_pages' => 'All pages',
            'shop_archive' => 'Shop Archive',
            'shop_archive_categories' => 'Shop Archive Categories (product categories)',
            'shop_archive_tags' => 'Shop Archive Tags (product tags)',
            'shop_archive_product_attributes' => 'Shop Archive Product Attributes',
            'single_products' => 'Single Products',
        );
    
        $display_conditions = get_option('atc_display_conditions', array('all_pages'));
    
        echo '<p style="margin-bottom: 10px;">Select where you want the Add to Cart notification to appear:</p>';
        echo '<div class="atc-display-condition-container">';
    
        foreach ($display_conditions as $index => $condition) {
            $count = count($display_conditions);
            echo '<div class="atc-display-button-container"><select name="atc_display_conditions['.$index.']" class="atc-display-condition">';
            foreach ($options as $value => $label) {
                echo '<option value="' . esc_attr($value) . '" ' . selected($condition, $value, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            if ($count > 1) {
                echo '<button id="remove_display_'. $index .'" class="button remove-condition" style="height: 25px; margin-left: 10px;" type="button">Remove</button>';
            }
            echo '</div>';
        }
    
        echo '<button id="atc-add-condition" class="button" type="button">Add Display Condition</button>';
        echo '</div>';
    } 

    private function check_display_conditions() {
        $current_conditions = get_option('atc_display_conditions', array('all_pages'));

        // Check if 'all_pages' is selected
        if (in_array('all_pages', $current_conditions)) {
            return true; // Display on all pages
        }

        // Check other conditions based on page type or custom logic
        $is_shop_archive = is_shop() || is_product_category() || is_product_tag();
        $is_single_product = is_product();

        if (in_array('shop_archive', $current_conditions) && $is_shop_archive) {
            return true;
        }

        if (in_array('shop_archive_categories', $current_conditions) && is_product_category()) {
            return true;
        }

        if (in_array('shop_archive_tags', $current_conditions) && is_product_tag()) {
            return true;
        }

        if (in_array('single_products', $current_conditions) && $is_single_product) {
            return true;
        }

        // Add more conditions as needed

        return false; // Default: do not display
    }

    public function ajax_add_to_cart_notification() {
        // Check if current page should display notification
        if ($this->check_display_conditions()) {
            $layout = get_option('atc_layout', 'product_image_left');
            $position = get_option('atc_display_position', 'top');
            $close_after_seconds = get_option('close_after_seconds', 3);
            $addedtxt = get_option('atc_added_text', 'Added to Cart');
            $pricetxt = get_option('atc_price_text', 'Price: ');

            // Get product data from the AJAX request
            $product_image = isset($_POST['product_image']) ? esc_url($_POST['product_image']) : '';
            $product_name = isset($_POST['product_name']) ? sanitize_text_field($_POST['product_name']) : '';
            $product_price = isset($_POST['product_price']) ? $_POST['product_price'] : '';
            $view_cart_text = isset($_POST['view_cart_text']) ? sanitize_text_field($_POST['view_cart_text']) : '';

            $notification_content = '
            <div class="addtocart-popup' . (($layout == 'product_image_background') ? ' popup-bg' : '') . '">
                <div class="popup-header">
                    <p class="popup-added">' . $addedtxt . '</p>
                    <p class="popup-close">x</p>
                </div>
                <div class="popup-body">';
                if ($layout == 'product_image_left') {
                    $notification_content .= '
                    <img id="addtocart-img" src="' . $product_image . '" alt="Product Image">';
                }
            $notification_content .= '
                    <div class="popup-mid' . (($layout == 'product_image_background') ? '-bg' : '') . '">
                        <p class="popup-title">' . $product_name . '</p>
                        <p class="popup-price">' . $pricetxt .$product_price . '</p>
                    </div>
                </div>
                <div class="popup-cart-button">
                    <a href="' . wc_get_cart_url() . '">' . $view_cart_text . '</a>
                </div>
            </div>';

            // Send the notification content as the AJAX response
            echo $notification_content;
        }

        ?>
        <style>
            .addtocart-popup {
                position: fixed;
                <?php
                echo ($position == 'top') ? 'top: 50px; bottom: unset;' : 'bottom: 50px; top: unset;';
                ?>
                right: -250px;
                width: 250px;
                height: 250px;
                background: #FFF;
                border: 1px solid #ddd;
                padding: 25px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                z-index: 999;
                transition: right 0.5s ease-in-out;
                display: flex;
                flex-direction: column;
            }
            .popup-header {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
            }
            .popup-added,
            .popup-close {
                margin-top: 0px;
            }
            .popup-close {
                width: 25px;
                text-align: right;
                cursor: pointer;
            }
            .popup-body {
                display: flex;
                flex-direction: row;
                height: -moz-available;
                height: -webkit-fill-available;
            }
            .popup-bg {
                <?php
                echo 'background-image: linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.7)), url('. $product_image .');';
                ?>
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }
            #addtocart-img {
                max-width: 45%;
                height: fit-content;
            }
            .popup-mid {
                margin-left: 10px;
            }
            .popup-mid-bg {
                display: flex;
                flex-direction: column;
                justify-content: space-evenly;
            }
            .popup-title {
                line-height: 1em;
                margin: 0;
            }
            .popup-price {
                margin: 0px;
                margin-top: 20px;
                line-height: 1em;
                font-size: 0.8em;
                color: #8b8b8b;
            }
            .popup-cart-button {
                display: flex;
                margin-top: 10px;
            }
            .popup-cart-button a {
                text-decoration: none;
                padding: 15px;
                background: var(--wp--preset--color--contrast);
                color: var(--wp--preset--color--base);
                width: 100%;
                text-align: center;
            }
        </style>
        <?php

        // Exit to avoid extra output
        wp_die();
    }

    public function enqueue_styles_and_scripts() {
        // Enqueue scripts with dependencies (in this case, jQuery)
        wp_enqueue_script('addtocart-popup-script', plugin_dir_url(__FILE__) . 'js/addtocart-popup-script.js', array('jquery'), null, true);
    
        // Pass variables to JavaScript
        wp_localize_script('addtocart-popup-script', 'addToCartPopupSettings', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'closeAfterSeconds' => get_option('close_after_seconds', 3),
        ));
    }
}
