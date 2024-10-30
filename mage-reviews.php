<?php
/**
 * Plugin Name: Mage Reviews
 * Plugin URI:  http://www.maximusbusiness.com/plugins/mage-reviews-pro/
 * Description: A simple customizable rating option will be integrated within the comment form and display rating average and author ratings.
 * Author:      Mage Cast
 * Author URI:  http://www.maximusbusiness.com/plugins/mage-reviews-pro/
 * Version:     1.2.4
 * Text Domain: mage-reviews
 * Domain Path: /lang/
 * License:     GPLv2 or later (license.txt)
 */
?>
<?php
if (!defined('ABSPATH')) exit;
define('MAGECAST_REVIEWS_VER', '1.2.4');
define('MAGECAST_REVIEWS', dirname( __FILE__ ). '/');
define('MAGECAST_REVIEWS_URL',plugins_url('/',__FILE__));
define('MAGECAST_REVIEWS_SOURCE',MAGECAST_REVIEWS_URL.'source/');
register_deactivation_hook( __FILE__, 'mage_reviews_deactivation' );
register_activation_hook( __FILE__, 'mage_reviews_activation' );
add_action('after_setup_theme','load_magecast_reviews');
add_action('init', 'load_magecast_reviews_lang');
function mage_reviews_activation() {
     add_option( 'mage_reviews_activation','activated' );
}
function load_magecast_reviews(){	
	require_once MAGECAST_REVIEWS.'core/mage-cast.php';
	require_once MAGECAST_REVIEWS.'cast/attributes.php';
	if (file_exists(MAGECAST_REVIEWS.'mage-reviews-pro.php')) require_once MAGECAST_REVIEWS.'mage-reviews-pro.php';
	require_once MAGECAST_REVIEWS.'cast/mage-reviews.php';
	require_once MAGECAST_REVIEWS.'cast/craft.php';
	add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'mage_reviews_settings_link' );
	add_filter( 'mage_core_plugin_mage_reviews', 'mage_reviews_dashboard' );
}
function load_magecast_reviews_lang() {
	load_plugin_textdomain('mage-reviews', false, dirname( plugin_basename( __FILE__ ) ) .'/lang/');
}

function mage_reviews_settings_link( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=mage_reviews') .'">Settings</a>';
   return $links;
}
function mage_reviews_deactivation() {
    flush_rewrite_rules();
}
function mage_reviews_dashboard( $links ) {
   $data = array(
   'name'=>'Mage Reviews',
   'active'=>true,
   'version'=>MAGECAST_REVIEWS_VER,
   'settings'=>get_admin_url(null, 'admin.php?page=mage_reviews'),
   'support'=>'http://wordpress.org/support/plugin/mage-reviews',
   'pro'=>'http://www.maximusbusiness.com/plugins/mage-reviews-pro/'
   );
   if(defined('MAGECAST_REVIEWS_PRO')) $data['pro_active'] = true;
   return $data;
}
