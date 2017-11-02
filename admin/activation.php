<?php
	/**
	 * Contains functions, hooks and classes required for activating the plugin.
	 *
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 2017, OnePress Ltd
	 *
	 * @since 1.0.0
	 * @package bizpand-popups-addon
	 */

	/**
	 * The activator class performing all the required actions on activation.
	 *
	 * @see Factory000_Activator
	 * @since 1.0.0
	 */
	class BZDA_EXPORT_ADN_Activation extends Factory000_Activator {

		/**
		 * Runs activation actions.
		 *
		 * @since 1.0.1
		 */
		public function activate()
		{
			//$this->setupLicense();

			// Redirect to help page
			//factory_000_set_lazy_redirect(opanda_get_admin_url('how-to-use', array('onp_sl_page' => 'bizpanda-popups-addon')));

			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$sq1 = "
				CREATE TABLE {$wpdb->prefix}opanda_export (
				  ID int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  task_title varchar(255) NOT NULL,
				  export_options text NOT NULL,
				  last_export_lead_ids longtext DEFAULT NULL,
				  updated_at int(11) DEFAULT NULL,
				  autoexport int(1) DEFAULT 0,
				  PRIMARY KEY (ID)
				)
				ENGINE = INNODB
				AUTO_INCREMENT = 1
				CHARACTER SET utf8
				COLLATE utf8_general_ci;);
			";

			dbDelta($sq1);

			$sq2 = "CREATE TABLE {$wpdb->prefix}opanda_export_logs (
					  task_id int(11) NOT NULL,
					  download_url varchar(355) NOT NULL,
					  filepath varchar(350) NOT NULL,
					  created_at int(11) NOT NULL
					)
					ENGINE = INNODB
					AVG_ROW_LENGTH = 16384
					CHARACTER SET utf8
					COLLATE utf8_general_ci;";
			dbDelta($sq2);

			// Включаем крон задачу отправки уведомлений, если она еще не включена.
			if( !wp_next_scheduled('onp_bzda_export_adn_cron_tasks') ) {
				wp_schedule_event(time(), 'hourly', 'onp_bzda_export_adn_cron_tasks');
			}
		}

		public function deactivate()
		{
			// Очищаем крон
			wp_unschedule_event(wp_next_scheduled('onp_bzda_export_adn_cron_tasks'), 'onp_bzda_export_adn_cron_tasks');
		}

		/**
		 * Setups the license.
		 *
		 * @since 1.0.0
		 */
		/*protected function setupLicense()
		{
			$this->plugin->license->setDefaultLicense(array(
				'Category' => 'free',
				'Build' => 'free',
				'Title' => 'OnePress Zero License',
				'Description' => __('Please, activate the plugin to get started. Enter a key
                                    you received with the plugin into the form below.', 'bizpanda-export-addon')
			));
		}*/
	}

	$bizpanda_export_addon->registerActivation('BZDA_EXPORT_ADN_Activation');

