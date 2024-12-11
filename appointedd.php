<?php
/**
 * Plugin Name: Connect to Appointedd
 * Plugin URI: https://wedofruition.com/
 * Description: Plugin to communicate with Appointedd https://www.appointedd.com/.
 * Version: 1.3
 * Author: Benedict Odoom
 * Author URI: https://wedofruition.com/
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'APPOINTEDD_VERSION', '1.2' );
define( 'APPOINTEDD__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'APPOINTEDD__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//register_activation_hook( __FILE__, 'Appointedd' );
//register_deactivation_hook( __FILE__, 'Appointedd' );

require_once( APPOINTEDD__PLUGIN_DIR . 'inc/class-appointedd.php' );
require_once( APPOINTEDD__PLUGIN_DIR . 'inc/class-appointedd-widget.php' );
require_once( APPOINTEDD__PLUGIN_DIR . 'inc/class-appointedd-rest-api.php' );

add_action( 'init', array( 'Appointedd', 'init' ) );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once( APPOINTEDD__PLUGIN_DIR . 'admin/inc/class-appointedd-admin.php' );
	add_action( 'init', array( 'Appointedd_Admin', 'init' ) );
}