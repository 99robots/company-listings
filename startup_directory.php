<?php
/*
Plugin Name: Company Listings
plugin URI: startup-directory
Description:
version: 1.0
Author: Kyle Benk
Author URI: http://kylebenkapps.com
License: GPL2
*/

/** 
 * Global Definitions 
 */

/* Plugin Name */

if (!defined('STARTUP_DIRECTORY_PLUGIN_NAME'))
    define('STARTUP_DIRECTORY_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

/* Plugin directory */

if (!defined('STARTUP_DIRECTORY_PLUGIN_DIR'))
    define('STARTUP_DIRECTORY_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . STARTUP_DIRECTORY_PLUGIN_NAME);

/* Plugin url */

if (!defined('STARTUP_DIRECTORY_PLUGIN_URL'))
    define('STARTUP_DIRECTORY_PLUGIN_URL', WP_PLUGIN_URL . '/' . STARTUP_DIRECTORY_PLUGIN_NAME);
  
/* Plugin verison */

if (!defined('STARTUP_DIRECTORY_VERSION_NUM'))
    define('STARTUP_DIRECTORY_VERSION_NUM', '1.0.0');
 
 
/** 
 * Activatation / Deactivation 
 */  

register_activation_hook( __FILE__, array('StartUpDirectory', 'register_activation'));

/** 
 * Hooks / Filter 
 */
 
add_action('init', array('StartUpDirectory', 'load_textdoamin'));
//add_action('admin_menu', array('StartUpDirectory', 'startup_directory_menu_page'));
//add_action('admin_enqueue_scripts', array('StartUpDirectory', 'startup_directory_include_admin_scripts'));

/** 
 *  StartUpDirectory main class
 *
 * @since 1.0.0
 * @using Wordpress 3.8
 */

class StartUpDirectory {

	/* Properties */
	
	private static $jquery_latest = 'http://code.jquery.com/jquery-latest.min.js';
	
	private static $text_domain = 'startup-directory';
	
	private static $prefix = 'startup_directory_';
	
	private static $settings_page = 'startup-directory-admin-menu-settings';
	
	private static $usage_page = 'startup-directory-admin-menu-usage';
	
	private static $option_version = 'startup_directory_version';

	/**
	 * Load the text domain 
	 * 
	 * @since 1.0.0
	 */
	static function load_textdoamin() {
		load_plugin_textdomain(self::$text_domain, false, STARTUP_DIRECTORY_PLUGIN_DIR . '/languages');
	}
	
	/**
	 * Hooks to 'init' 
	 * 
	 * @since 1.0.0
	 */
	static function register_activation() {
	
		/* Check if multisite, if so then save as site option */
		
		if (is_multisite()) {
			add_site_option(self::$option_version, STARTUP_DIRECTORY_VERSION_NUM);
		} else {
			add_option(self::$option_version, STARTUP_DIRECTORY_VERSION_NUM);
		}
		
		global $wpdb;
		
		// Events
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `events` (
		  `id` int(9) NOT NULL AUTO_INCREMENT,
		  `id_eventbrite` varchar(15) NOT NULL,
		  `title` varchar(200) NOT NULL,
		  `created` int(14) NOT NULL,
		  `organizer_name` varchar(100) NOT NULL,
		  `uri` varchar(200) NOT NULL,
		  `start_date` int(14) NOT NULL,
		  `end_date` int(14) NOT NULL,
		  `lat` float NOT NULL,
		  `lng` float NOT NULL,
		  `address` varchar(200) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;");
		
		// Places
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `places` (
		  `id` int(9) NOT NULL AUTO_INCREMENT,
		  `approved` int(1) DEFAULT NULL,
		  `title` varchar(100) NOT NULL,
		  `type` varchar(20) NOT NULL,
		  `lat` float NOT NULL,
		  `lng` float NOT NULL,
		  `address` varchar(200) NOT NULL,
		  `uri` varchar(200) NOT NULL,
		  `description` varchar(255) NOT NULL,
		  `sector` varchar(50) NOT NULL,
		  `owner_name` varchar(100) NOT NULL,
		  `owner_email` varchar(100) NOT NULL,  
		  `sg_organization_id` int(9) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `id` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ");
		
		// Settings
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `settings` (
		  `sg_lastupdate` int(14) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
		
		$wpdb->query("INSERT INTO `settings` (`sg_lastupdate`) VALUES (0);");
	}
	
	/**
	 * Hooks to 'admin_menu' 
	 * 
	 * @since 1.0.0
	 */
	static function startup_directory_menu_page() {
		
		/* Add the menu Page */
		
		$page_settings_load = add_menu_page(
			__('StartUps', self::$text_domain),					// Page Title
			__('StartUps', self::$text_domain), 					// Menu Name
	    	'manage_options', 											// Capabilities
	    	self::$settings_page, 										// slug
	    	array('StartUpDirectory', 'startup_directory_admin_settings')	// Callback function
	    );
	    
	    //add_action('admin_print_scripts-' . $page_settings_load, array('StartUpDirectory', 'include_scripts_inline'));
	}
	
	/**
	 * Hooks to 'admin_print_scripts-' 
	 * 
	 * @since 1.0.0
	 */
	static function include_scripts_inline() {
		
		/* CSS */
			
		wp_register_style('startup_directory_admin_css', STARTUP_DIRECTORY_PLUGIN_URL . '/include/css/startup_directory_admin.css');
		wp_enqueue_style('startup_directory_admin_css');
	}
	
	/**
	 * Displays the HTML for the 'startup-directory-admin-menu-settings' admin page
	 * 
	 * @since 1.0.0
	 */
	static function startup_directory_admin_settings() {
	
		require_once(plugin_dir_path( __FILE__ ) . 'include/api_src/represent-map/admin/index.php');
		
	}
	
	/**
	 * Displays the HTML for the 'startup-directory-admin-menu-usage' admin page 
	 * 
	 * @since 1.0.0
	 */
	static function startup_directory_admin_usage() {
		?>
		
		<h1><?php _e('Usage Page', self::$text_domain); ?></h1>
		
		<?php
	}
	
	/**
	 * Hooks to 'admin_enqueue_scripts' 
	 * 
	 * @since 1.0.0
	 */
	static function startup_directory_include_admin_scripts() {
		
		/* Include Admin scripts */
		
		if (isset($_GET['page']) && ($_GET['page'] == self::$settings_page || $_GET['page'] == self::$usage_page)) {
		
			/* Javascript */
			
			wp_register_script('startup_directory_admin_js', STARTUP_DIRECTORY_PLUGIN_URL . '/include/js/startup_directory_admin.js');
			wp_enqueue_script('startup_directory_admin_js');	
		}
	}
}

?>