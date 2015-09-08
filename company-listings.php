<?php
/*
Plugin Name: Company Listings
plugin URI:
Description:
version: 0.9
Author: JuiceTank
Author URI: http://juicetank.com
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
    define('STARTUP_DIRECTORY_VERSION_NUM', '0.9.0');


/**
 * Activatation / Deactivation
 */

register_activation_hook( __FILE__, array('CompanyListings', 'register_activation'));

/**
 * Hooks / Filter
 */

add_action('init', array('CompanyListings', 'load_textdoamin'));
add_action('init', array('CompanyListings', 'register_companies'));
add_action('admin_menu', array('CompanyListings', 'startup_directory_menu_page'));
add_action('admin_enqueue_scripts', array('CompanyListings', 'startup_directory_include_admin_scripts'));
add_shortcode('company_listings_admin', array('CompanyListings', 'add_shortcode_admin'));
add_shortcode('company_listings_portal', array('CompanyListings', 'add_shortcode_portal'));

/**
 *  CompanyListings main class
 *
 * @since 1.0.0
 * @using Wordpress 3.8
 */

class CompanyListings {

	/**
	 * text_domain
	 *
	 * (default value: 'company-listings')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $text_domain = 'company-listings';

	/**
	 * prefix
	 *
	 * (default value: 'startup_directory_')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $prefix = 'company_listings_';

	/**
	 * prefix_dash
	 *
	 * (default value: 'com-lis-')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $prefix_dash = 'com-lis-';

	/**
	 * settings_page
	 *
	 * (default value: 'company-listings-admin-menu-settings')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $settings_page = 'company-listings-admin-menu-settings';

	/**
	 * usage_page
	 *
	 * (default value: 'company-listings-admin-menu-usage')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $usage_page = 'company-listings-admin-menu-usage';

	/**
	 * option_version
	 *
	 * (default value: 'company_listings_version')
	 *
	 * @var string
	 * @access private
	 * @static
	 */
	private static $option_version = 'company_listings_version';

	/**
	 * default_types
	 *
	 * @var mixed
	 * @access public
	 * @static
	 */
	public static $default_types = array(
		array(
			'name'			=> 'Startups',
			'description'	=> '',
			'slug'			=> 'startups',
		),
		array(
			'name'			=> 'Accelerators',
			'description'	=> '',
			'slug'			=> 'accelerators',
		),
		array(
			'name'			=> 'Incubators',
			'description'	=> '',
			'slug'			=> 'incubators',
		),
		array(
			'name'			=> 'Coworking',
			'description'	=> '',
			'slug'			=> 'coworking',
		),
		array(
			'name'			=> 'Investors',
			'description'	=> '',
			'slug'			=> 'investors',
		),
		array(
			'name'			=> 'Consulting',
			'description'	=> '',
			'slug'			=> 'consulting',
		)
	);

	/**
	 * Load the text domain
	 *
	 * @since 1.0.0
	 */
	static function load_textdoamin() {
		load_plugin_textdomain(self::$text_domain, false, STARTUP_DIRECTORY_PLUGIN_DIR . '/languages');

	}


	/**
	 * Register the Company Post Type
	 *
	 * @since 1.0.0
	 */
	static function register_companies() {
		// Register Custom Post Types

		$labels = array(
			'name'                => _x( 'Companies', 'Post Type General Name', self::$text_domain ),
			'singular_name'       => _x( 'Company', 'Post Type Singular Name', self::$text_domain ),
			'menu_name'           => __( 'Company', self::$text_domain ),
			'parent_item_colon'   => __( 'Parent Item:', self::$text_domain ),
			'all_items'           => __( 'All Companies', self::$text_domain ),
			'view_item'           => __( 'View Company', self::$text_domain ),
			'add_new_item'        => __( 'Add New Company', self::$text_domain ),
			'add_new'             => __( 'Add Company', self::$text_domain ),
			'edit_item'           => __( 'Edit Company', self::$text_domain ),
			'update_item'         => __( 'Update Company', self::$text_domain ),
			'search_items'        => __( 'Search Company', self::$text_domain ),
			'not_found'           => __( 'Not found', self::$text_domain ),
			'not_found_in_trash'  => __( 'Not found in Trash', self::$text_domain ),
		);
		$args = array(
			'label'               => __( 'cl_company', self::$text_domain ),
			'description'         => __( 'A company listing', self::$text_domain ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'post-formats'),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'cl_company', $args );

		// Register Custom Taxonomies

		$labels = array(
			'name'                       => _x( 'Type', 'Taxonomy General Name', self::$text_domain ),
			'singular_name'              => _x( 'Types', 'Taxonomy Singular Name', self::$text_domain ),
			'menu_name'                  => __( 'Types', self::$text_domain ),
			'all_items'                  => __( 'All Types', self::$text_domain ),
			'parent_item'                => __( 'Parent Item', self::$text_domain ),
			'parent_item_colon'          => __( 'Parent Item:', self::$text_domain ),
			'new_item_name'              => __( 'New Type Name', self::$text_domain ),
			'add_new_item'               => __( 'Add New Type', self::$text_domain ),
			'edit_item'                  => __( 'Edit Type', self::$text_domain ),
			'update_item'                => __( 'Update Type', self::$text_domain ),
			'separate_items_with_commas' => __( 'Separate types with commas', self::$text_domain ),
			'search_items'               => __( 'Search Types', self::$text_domain ),
			'add_or_remove_items'        => __( 'Add or remove types', self::$text_domain ),
			'choose_from_most_used'      => __( 'Choose from the most used types', self::$text_domain ),
			'not_found'                  => __( 'Not Found', self::$text_domain ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'cl_type', array( 'cl_company' ), $args );
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

		self::register_companies();

		// Add Default Types

		foreach (self::$default_types as $type) {
			wp_insert_term(
				$type['name'], // the term
				'cl_type', // the taxonomy
				array(
					'description'	=> $type['description'],
					'slug'     		=> $type['slug']
				)
			);
		}

		flush_rewrite_rules();

		/*
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
*/
	}

	/**
	 * Hooks to 'admin_menu'
	 *
	 * @since 1.0.0
	 */
	static function startup_directory_menu_page() {

		/* Add the menu Page */

		$page_settings_load = add_submenu_page(
			'tools.php',
			__('Company Listings', self::$text_domain),					// Page Title
			__('Company Listings', self::$text_domain), 					// Menu Name
	    	'manage_options', 											// Capabilities
	    	self::$settings_page, 										// slug
	    	array('CompanyListings', 'startup_directory_admin_settings')	// Callback function
	    );

	    add_action('admin_print_scripts-' . $page_settings_load, array('CompanyListings', 'include_scripts_inline'));
	}

	/**
	 * Hooks to 'admin_print_scripts-'
	 *
	 * @since 1.0.0
	 */
	static function include_scripts_inline() {

		/* CSS */

		wp_register_style('startup_directory_admin_css', STARTUP_DIRECTORY_PLUGIN_URL . '/css/startup_directory_admin.css');
		wp_enqueue_style('startup_directory_admin_css');
	}

	/**
	 * Displays the HTML for the 'startup-directory-admin-menu-settings' admin page
	 *
	 * @since 1.0.0
	 */
	static function startup_directory_admin_settings() {
		?>

		<h1><?php _e('Using the Shortcode', self::$text_domain); ?></h1>

		<div class="wrap metabox-holder">

			<div class="postbox">
				<h3><?php _e('Admin', self::$text_domain); ?></h3>
				<div class="inside">
					<span><?php _e('[company_listings_admin]', self::$text_domain); ?></span>
				</div>
			</div>

			<div class="postbox">
				<h3><?php _e('Portal', self::$text_domain); ?></h3>
				<div class="inside">
					<span><?php _e('[company_listings_portal]', self::$text_domain); ?></span>
				</div>
			</div>

		</div>

		<?php
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

			wp_register_script('startup_directory_admin_js', STARTUP_DIRECTORY_PLUGIN_URL . '/js/startup_directory_admin.js');
			wp_enqueue_script('startup_directory_admin_js');
		}
	}

	/**
	 * Adds Shortcode to show admin panel
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function add_shortcode_admin() {

		global $post;

		if (is_singular($post)) {
			require_once(STARTUP_DIRECTORY_PLUGIN_DIR . '/include/api_src/represent-map/admin/index.php');

			wp_register_style('startup_directory_admin_css', STARTUP_DIRECTORY_PLUGIN_URL . '/css/startup_directory_admin.css');
			wp_enqueue_style('startup_directory_admin_css');
		}
	}

	/**
	 * Adds Shortcode to show admin portal
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function add_shortcode_portal() {

		global $post;

		if (is_singular($post)) {
			include_once(STARTUP_DIRECTORY_PLUGIN_DIR . '/include/api_src/represent-map/index.php');

			wp_register_style('startup_directory_portal_css', STARTUP_DIRECTORY_PLUGIN_URL . '/css/startup_directory_portal.css');
			wp_enqueue_style('startup_directory_portal_css');
		}
	}
}

?>