<?php
	/*
	* Plugin Name: [Bizpanda addon] All Export
	* Description: Export leads from the Optin panda plugin, using the Wp all import plugin.
	* Version: 1.0.0
	* Author: Webcraftic <wordpress.webraftic@gmail.com>
	* Author URI: https://profiles.wordpress.org/webcraftic
	*/

	define('BZDA_EXPORT_ADN_INIT', true);

	define('BZDA_EXPORT_ADN_PLUGIN_DIR', dirname(__FILE__));
	define('BZDA_EXPORT_ADN_PLUGIN_URL', plugins_url(null, __FILE__));

	#comp remove
	// build: free, premium, ultimate
	if( !defined('BUILD_TYPE') ) {
		define('BUILD_TYPE', 'premium');
	}

	// license: free, paid
	if( !defined('LICENSE_TYPE') ) {
		define('LICENSE_TYPE', 'paid');
	}
	#endcomp

	function onp_bzda_export_adn_init()
	{
		if( defined('OPTINPANDA_PLUGIN_ACTIVE') || defined('SOCIALLOCKER_PLUGIN_ACTIVE') ) {

			global $bizpanda, $bizpanda_export_addon;

			load_textdomain('bizpanda-export-addon', BZDA_EXPORT_ADN_PLUGIN_DIR . '/langs/' . get_locale() . '.mo');

			require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/plugin.class.php';

			$bizpanda_export_addon = new BZDA_EXPORT_ADN_Factory000_Plugin(__FILE__, array(
				'name' => 'bizpanda-export-addon',
				'title' => '[Bizpanda Addon] All export',
				'plugin_type' => 'addon',
				'version' => '1.0.0',
				'assembly' => BUILD_TYPE,
				'lang' => get_locale(),
				'api' => 'http://api.byonepress.com/1.1/',
				'account' => 'http://accounts.byonepress.com/',
				'updates' => BZDA_EXPORT_ADN_PLUGIN_DIR . '/plugin/updates/'
			));

			// requires factory modules extend global bizpanda
			/*$bizpanda_export_addon->load(array(
				array('libs/factory/bootstrap', 'factory_bootstrap_000', 'admin'),
			));*/

			BizPanda::registerPlugin($bizpanda_export_addon, 'bizpanda-export-addon', BUILD_TYPE);

			if( is_admin() ) {
				require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/boot.php';
			}

			require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/plugin/boot.php';
		}
	}

	add_action('bizpanda_init', 'onp_bzda_export_adn_init', 20);

	/**
	 * Activates the plugin.
	 *
	 * TThe activation hook has to be registered before loading the plugin.
	 * The deactivateion hook can be registered in any place (currently in the file plugin.class.php).
	 */
	function onp_bzda_export_adn_activation()
	{
		if( defined('OPTINPANDA_PLUGIN_ACTIVE') || defined('SOCIALLOCKER_PLUGIN_ACTIVE') ) {
			onp_bzda_export_adn_init();

			global $bizpanda_export_addon;
			$bizpanda_export_addon->activate();
		}
	}

	register_activation_hook(__FILE__, 'onp_bzda_export_adn_activation');