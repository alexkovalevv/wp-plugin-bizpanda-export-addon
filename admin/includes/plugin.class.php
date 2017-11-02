<?php
	/**
	 * Extend factory plugin class
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 20.10.2017, Webcraftic
	 * @version 1.0
	 */

	/**
	 * Factory Plugin
	 *
	 * @since 1.0.0
	 */
	class BZDA_EXPORT_ADN_Factory000_Plugin extends Factory000_Plugin {

		public $component = 'yvprafr';

		/**
		 * Loads a specified module.
		 *
		 * @since 3.2.0
		 * @param string $modulePath
		 * @param string $moduleVersion
		 * @return void
		 */
		public function loadModule($module)
		{
			global $bizpanda;

			$scope = isset($module[2])
				? $module[2]
				: 'all';

			if( $scope == 'all' || (is_admin() && $scope == 'admin') || (!is_admin() && $scope == 'public') ) {

				require $bizpanda->pluginRoot . '/' . $module[0] . '/boot.php';
				do_action($module[1] . '_plugin_created', $this);
			}
		}

		public function isPluginLoaded()
		{
			#comp remove
			/*if( onp_build('premium') ) {
				return true;
			}*/
			#endcomp

			$component_name = str_rot13($this->component);

			return $this->$component_name && $this->$component_name->hasKey();
		}
	}