<?php
/*
Plugin Name: Add to Cart Popup
Description: WooCommerce Add to Cart Popup Custom Notifications Plugin
Version: 1.0
Author: João Paiva
*/

if (!defined('ABSPATH')) {
    exit;
}

// Include main plugin class.
require_once plugin_dir_path(__FILE__) . 'includes/class-addtocart-popup.php';

// Instantiate main class.
$addtocart_popup = new AddToCart_Popup();
