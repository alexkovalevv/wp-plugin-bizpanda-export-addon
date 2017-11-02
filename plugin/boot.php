<?php
	/**
	 * Frontend boot
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 01.11.2017, Webcraftic
	 * @version 1.0
	 */

	// cron shedule
	function onp_bzda_export_adn_cron_tasks_function()
	{
		global $wpdb, $bizpanda;

		$get_export_tasks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}opanda_export");

		if( !empty($get_export_tasks) ) {
			foreach($get_export_tasks as $task) {

				if( intval($task->autoexport) && !get_transient('bizpanda_export_shedule_task_' . $task->ID) ) {

					require_once $bizpanda->pluginRoot . '/libs/factory/forms/includes/providers/value-provider.interface.php';
					require_once $bizpanda->pluginRoot . '/libs/factory/forms/includes/providers/options-value-provider.class.php';

					require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.values.provider.php';
					require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.excute.php';

					$provider = new BZDA_EXPORT_ADN_ExportExcute($task->ID);

					$get_shedule_time = $provider->getValue('task_shedule_time');
					$get_shedule_time_in_seconds = 0;

					switch( $get_shedule_time ) {
						case 'hourly':
							$get_shedule_time_in_seconds = HOUR_IN_SECONDS;
							break;
						case 'daily':
							$get_shedule_time_in_seconds = DAY_IN_SECONDS;
							break;
						case 'weekly':
							$get_shedule_time_in_seconds = WEEK_IN_SECONDS;
							break;
						case 'monthly':
							$get_shedule_time_in_seconds = DAY_IN_SECONDS;
							break;
					}

					if( !$provider->runExport() ) {
						if( !empty($provider->errors) ) {
							// loging
							return;
						}
					}

					set_transient('bizpanda_export_shedule_task_' . $task->ID, true, $get_shedule_time_in_seconds);
				}
			}
		}
	}

	add_action('onp_bzda_export_adn_cron_tasks', 'onp_bzda_export_adn_cron_tasks_function');