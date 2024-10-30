<?php

/**
 * Login To Viberent
 * 
 * Plugin Name: Login To Viberent
 * Plugin URI: https://wordpress.org/plugins/login-to-viberent
 * Description: Viberent is a cloud-based rental management system that allows equipment rental businesses to further improve their rental operations through strong inventory management, invoicing integration to Xero, MYOB and QBO, and much more. The Viberent plugin for WordPress allows you to enable your customers to check availability of your rental items and place orders via your website. You no longer have to manually re-enter orders from your website into your Viberent system.
 * Version: 1.0.1
 * Author: Viberent
 * Author URI: https://viberenthq.com/
 * License: GPLv2 or later
 * Text Domain: viberent rental management login
 */
define('VIBERENT__PLUGIN_DIR', plugin_dir_path(__FILE__));

//register the css files
function viberent_admin_scripts()
{
  wp_register_style('viberent_style', plugins_url('assets/css/viberent.css', __FILE__));
  wp_enqueue_style('viberent_style');
  wp_register_style('font-awesome', plugins_url('assets/css/font-awesome.css', __FILE__));
  wp_enqueue_style('font-awesome');
}
add_action('admin_enqueue_scripts', 'viberent_admin_scripts');

include_once('functions-layout-api.php');
include_once('viberent-hook.php');

?>