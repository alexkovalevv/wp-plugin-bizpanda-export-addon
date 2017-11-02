<?php
	/**
	 * All Export page
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 29.10.2017, Webcraftic
	 * @version 1.0
	 */
	
	/**
	 * Common Settings
	 */
	class BZDA_AND_ExportPage extends OPanda_AdminPage {
		
		public $internal = false;
		
		public function __construct($plugin)
		{
			$this->menuPostType = OPANDA_POST_TYPE;
			
			$this->id = "export";
			$this->pageTitle = __('All Export', 'bizpanda-export-addon');
			
			parent::__construct($plugin);
		}
		
		public function assets($scripts, $styles)
		{
			$this->scripts->request('jquery');
			
			$this->scripts->request(array(
				'control.checkbox',
				'control.dropdown'
			), 'bootstrap');
			
			$this->styles->request(array(
				'bootstrap.core',
				'bootstrap.form-group',
				'bootstrap.separator',
				'control.dropdown',
				'control.checkbox',
			), 'bootstrap');

			$this->styles->add(BZDA_EXPORT_ADN_PLUGIN_URL . '/admin/assets/css/export-page.css');
		}

		public function renderBody($body, $in_bootstrap = true)
		{
			?>
			<div class="wrap" id="bizpanda-export-page">
				<div class="factory-bootstrap-000 factory-fontawesome-000">
					<h2>
						<?php _e('All Export', 'bizpanda') ?>
						<a href="<?php $this->actionUrl('select-export-type') ?>" class="add-new-h2"><?php _e('New export', 'bizpanda'); ?></a>
					</h2>

					<?php if( isset($_GET['bzda_success_remove']) ) { ?>
						<div class="alert alert-success">
							<?php _e('The data has been successfully cleared.', 'bizpanda') ?>
						</div>
					<?php } ?>
					<?php if( $in_bootstrap ) {
						echo $body;
					} ?>
				</div>

				<?php if( !$in_bootstrap ) {
					echo $body;
				} ?>
			</div>
		<?php
		}
		
		public function indexAction()
		{
			if( !class_exists('WP_List_Table') ) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}
			
			require_once(BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.tasks.table.php');
			
			$table = new BZDA_ADN_ExportListTable();
			$table->prepare_items();

			ob_start();
			?>
			<form method="post" action=""><?php $table->display() ?></form>
			<?php
			$output = ob_get_contents();
			ob_get_clean();
			
			$this->renderBody($output, false);
		}

		public function exportLogsAction()
		{
			$task_id = (int)$this->request->get('task_id', 0);

			if( !class_exists('WP_List_Table') ) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}

			require_once(BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.logs.table.php');

			$table = new BZDA_ADN_ExportLogListTable();
			$table->prepare_items($task_id);

			ob_start();
			?>
			<form method="post" action=""><?php $table->display() ?></form>
			<?php
			$output = ob_get_contents();
			ob_get_clean();

			$this->renderBody($output, false);
		}

		public function selectExportTypeAction()
		{
			ob_start();

			?>
			<div class="factory-bootstrap-000 factory-fontawesome-000">
				<div class="bizpanda-export-items">
					<div class="postbox bizpanda-export-item">
						<h4 class="bizpanda-export-title">Export Leads</h4>

						<div class="bizpanda-export-description">
							<p>This mode will allow you to export all your leads collected through our plugins.</p>

							<p>You can choose which to export and you can filter some contacts.</p>

							<div class="bizpanda-export-buttons">
								<a href="<?php $this->actionUrl('export', array('export_type' => 'leads')) ?>" class="button button-large bizpanda-export-start">
									<i class="fa fa-plus"></i> <span>Create new task</span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!--<div class="bizpanda-export-items">
					<div class="postbox bizpanda-export-item">
						<h4 class="bizpanda-export-title">Export Lockers</h4>

						<div class="bizpanda-export-description">
							<p>This mode will allow you to export all your leads collected through our plugins.</p>

							<p>You can choose which to export and you can filter some contacts.</p>

							<div class="bizpanda-export-buttons">
								<a href="<?php $this->actionUrl('export', array('export_type' => 'lockers')) ?>" class="button button-large bizpanda-export-start">
									<i class="fa fa-plus"></i> <span>Start export</span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="bizpanda-export-items">
					<div class="postbox bizpanda-export-item">
						<h4 class="bizpanda-export-title">Export Settings</h4>

						<div class="bizpanda-export-description">
							<p>This mode will allow you to export all your leads collected through our plugins.</p>

							<p>You can choose which to export and you can filter some contacts.</p>

							<div class="bizpanda-export-buttons">
								<a href="<?php $this->actionUrl('export', array('export_type' => 'settings')) ?>" class="button button-large bizpanda-export-start">
									<i class="fa fa-plus"></i> <span>Start export</span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="bizpanda-export-items">
					<div class="postbox bizpanda-export-item">
						<h4 class="bizpanda-export-title">Export all data</h4>

						<div class="bizpanda-export-description">
							<p>This mode will allow you to export all your leads collected through our plugins.</p>

							<p>You can choose which to export and you can filter some contacts.</p>

							<div class="bizpanda-export-buttons">
								<a href="<?php $this->actionUrl('export', array('export_type' => 'boundle')) ?>" class="button button-large bizpanda-export-start">
									<i class="fa fa-plus"></i> <span>Start export</span>
								</a>
							</div>
						</div>
					</div>
				</div>-->
			</div>
			<?php

			$output = ob_get_contents();
			ob_get_clean();

			$this->renderBody($output);
		}

		public function exportAction()
		{

			global $bizpanda, $wpdb;

			$task_id = (int)$this->request->get('task_id', 0);
			$export_type = $this->request->get('export_type', 'boundle', true);

			// creating a form

			$form = new FactoryForms000_Form(array(
				'scope' => 'bizpanda',
				'name' => 'export'
			), $bizpanda);

			require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.values.provider.php';

			$form->setProvider(new BZDA_EXPORT_ADN_OptionsValueProvider($task_id));

			$options = array();

			$options[] = array(
				'type' => 'separator'
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'task_title',
				'title' => __('Export Task title', 'bizpanda'),
				'hint' => __('Enter the task title for export, in the future it will help you to distinguish this task from the others.', 'bizpanda'),
				'default' => __('Export leads task ' . date('Y-m-d H:i:s'), 'bizpanda')
			);

			switch( $export_type ) {
				case 'leads':

					$data = $wpdb->get_results("SELECT l.lead_item_id AS locker_id, COUNT(l.ID) AS count, p.post_title AS locker_title " . "FROM {$wpdb->prefix}opanda_leads AS l " . "LEFT JOIN {$wpdb->prefix}posts AS p ON p.ID = l.lead_item_id " . "GROUP BY l.lead_item_id", ARRAY_A);

					$lockerList = array(
						array('all', __('Mark All', 'bizpanda'))
					);

					$lockerIds = array();

					foreach($data as $items) {
						$lockerList[] = array(
							$items['locker_id'],
							$items['locker_title'] . ' (' . $items['count'] . ' leads)'
						);
						$lockerIds[] = $items['locker_id'];
					}

					/*$options[] = array(
						'type' => 'radio',
						'name' => 'format',
						'title' => __('Format', 'bizpanda'),
						'hint' => __('Only the CSV format is available currently.', 'bizpanda'),
						'data' => array(
							array('csv', __('CSV File', 'bizpanda'))
						),
						'default' => 'csv'
					);*/
					$options[] = array(
						'type' => 'dropdown',
						'way' => 'buttons',
						'name' => 'delimiter',
						'title' => __('Delimiter', 'bizpanda'),
						'hint' => __('Choose a delimiter for a CSV document.', 'bizpanda'),
						'data' => array(
							array(',', __('Comma', 'bizpanda')),
							array(';', __('Semicolon', 'bizpanda'))
						),
						'default' => ','
					);
					$options[] = array(
						'type' => 'separator'
					);
					$options[] = array(
						'type' => 'list',
						'way' => 'checklist',
						'name' => 'lockers',
						'title' => __('Channels', 'bizpanda'),
						'hint' => __('Mark lockers which attracted leads you wish to export.', 'bizpanda'),
						'data' => $lockerList,
						'default' => implode(',', $lockerIds)
					);
					$options[] = array(
						'type' => 'dropdown',
						'way' => 'buttons',
						'name' => 'status',
						'title' => __('Email Status', 'bizpanda'),
						'hint' => __('Choose the email status of leads to export.', 'bizpanda'),
						'data' => array(
							array('all', __('All', 'bizpanda')),
							array('confirmed', __('Only Confirmed Emails', 'bizpanda')),
							array('not-confirmed', __('Only Not Confirmed', 'bizpanda'))
						),
						'default' => 'confirmed'
					);
					$options[] = array(
						'type' => 'separator'
					);
					$options[] = array(
						'type' => 'list',
						'way' => 'checklist',
						'name' => 'fields',
						'title' => __('Fields To Export', 'bizpanda'),
						'data' => array(
							array('lead_email', __('Email', 'bizpanda')),
							array('lead_display_name', __('Display Name', 'bizpanda')),
							array('lead_name', __('Firstname', 'bizpanda')),
							array('lead_family', __('Lastname', 'bizpanda')),
							array('lead_ip', __('IP', 'bizpanda')),
							//array('custom_facebook_id', __('Facebook App Scoped Id', 'bizpanda'))
						),
						'default' => implode(',', array('lead_email', 'lead_display_name'))
					);

					// custom fields

					$customFields = OPanda_Leads::getCustomFields();

					$customFieldsForList = array();
					foreach($customFields as $customField) {
						$customFieldsForList[] = array($customField->field_name, $customField->field_name);
					}

					if( !empty($customFieldsForList) ) {
						$options[] = array(
							'type' => 'list',
							'way' => 'checklist',
							'name' => 'custom_fields',
							'title' => __('Custom Fields', 'bizpanda'),
							'data' => $customFieldsForList,
							'default' => implode(',', array())
						);
					}

					$options[] = array(
						'type' => 'checkbox',
						'way' => 'buttons',
						'name' => 'exclude_previous_data',
						'title' => __('Exclude previous exports', 'bizpanda'),
						'hint' => __('If this option is enabled, then each time you create an export file, the previous exported records will be excluded from it.', 'bizpanda'),
						'default' => false
					);

					break;
				case 'lockers':
					//$options = array();
					break;
				case 'settings':
					//$options = array();
					break;
				case 'stats':
					//$options = array();
					break;
				case 'boundle':
					//$options = array();
					break;
			}

			$options[] = array(
				'type' => 'textbox',
				'name' => 'local_folder_path',
				'title' => __('Enter local foler path', 'bizpanda'),
				'hint' => __('Add a path to the local folder where the exported data will be stored. Use only the relative path!', 'bizpanda'),
				'default' => 'wp-content/uploads/bizpanda-all-export'
			);

			$options[] = array(
				'type' => 'checkbox',
				'way' => 'buttons',
				'name' => 'autoexport',
				'title' => __('Automatic export shedule', 'bizpanda'),
				'hint' => __('Set On, to enable automatic export on a schedule.', 'bizpanda'),
				'default' => false,
				'eventsOn' => array(
					'show' => '.factory-control-task_shedule_time'
				),
				'eventsOff' => array(
					'hide' => '.factory-control-task_shedule_time'
				)
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'task_shedule_time',
				'title' => __('How often do you export?', 'bizpanda'),
				'hint' => __('Choose the email status of leads to export.', 'bizpanda'),
				'data' => array(
					array('hourly', __('Hourly', 'bizpanda')),
					array('daily', __('Daily', 'bizpanda')),
					array('weekly', __('Weekly', 'bizpanda')),
					array('monthly', __('Monthly', 'bizpanda'))
				),
				'default' => 'week'
			);

			$options[] = array(
				'type' => 'separator'
			);

			$form->add($options);

			if( $this->request->post('bizpanda_export') ) {
				$inserted_id = $form->save();

				if( !empty($inserted_id) ) {
					$this->redirectToAction('export-process', array(
						'task_id' => $inserted_id,
						'referrer_action' => 'export'
					));
				} else {
					// error
				}
			}
			ob_start();
			?>
			<script>
				jQuery(function() {
					jQuery('#factory-checklist-bizpanda_lockers-all').click(function() {
						jQuery('input[name="bizpanda_lockers[]"]').prop('checked', true);
					});
				});
			</script>
			<form method="post" class="form-horizontal">
				<?php $form->html(); ?>

				<div class="form-group form-horizontal">
					<label class="col-sm-2 control-label"> </label>

					<div class="control-group controls col-sm-10">
						<input name="bizpanda_export" class="btn btn-primary" type="submit" value="<?php _e('Export Leads', 'bizpanda') ?>"/>
					</div>
				</div>
			</form>
			<?php

			$output = ob_get_contents();
			ob_get_clean();

			$this->renderBody($output);
		}

		public function exportProcessAction()
		{
			$task_id = (int)$this->request->get('task_id', 0);
			$referrer_action = $this->request->get('referrer_action', 'index');

			$comeback_redirect_args = array();

			if( $referrer_action !== 'index' ) {
				$comeback_redirect_args = array(
					'export_type' => 'leads',
					'task_id' => $task_id
				);
			}

			$data_provider = $this->getExportDataProvider($task_id);
			$leads = $data_provider->getLeads();

			$leads_count = !empty($leads)
				? sizeof($leads)
				: 0;

			ob_start();

			?>
			<div class="bizpanda-export-save-task-screen">
				<div class="bizpanda-details-contanier">
					<h3>
						<span <?php if (!$leads_count): ?>style="color:red;"<?php endif; ?>>
							<?= $leads_count ?>
						</span> leads for export
					</h3>

					<p>Export files will be saved in storage folder. You can download different versions of the files at
						any time.</p>

					<?php if( !empty($data_provider->errors) ): ?>
						<?php foreach($data_provider->errors as $message) {
							echo '<p style="color:red;">' . $message . '</p>';
						}
						?>
					<?php endif; ?>
				</div>
				<div class="bizpanda-next-buttons">
					<?php if( $leads_count ): ?>
						<a class="bizpanda-next-run-button" href="<?php $this->actionUrl('run_export', array('task_id' => $task_id)) ?>">
							<?php _e('Start export', 'bizpanda-export-addon') ?>
						</a>
					<?php endif; ?>
					<a class="bizpanda-come-back-button" href="<?php $this->actionUrl($referrer_action, $comeback_redirect_args) ?>">
						<?php _e('Comeback', 'bizpanda-export-addon') ?>
					</a>
				</div>
			</div>

			<?php
			$output = ob_get_contents();
			ob_get_clean();

			$this->renderBody($output);
		}

		public function removeTaskAction()
		{
			global $wpdb;

			$task_id = (int)$this->request->get('task_id', 0);

			$wpdb->delete($wpdb->prefix . 'opanda_export', array('ID' => $task_id), array('%d'));

			$get_logs = $wpdb->get_results($wpdb->prepare("SELECT filepath FROM {$wpdb->prefix}opanda_export_logs WHERE task_id = '%d'", $task_id));

			if( !empty($get_logs) ) {
				foreach($get_logs as $value) {
					if( file_exists($value->filepath) ) {
						unlink($value->filepath);
					}
				}

				$wpdb->delete($wpdb->prefix . 'opanda_export_logs', array('task_id' => $task_id), array('%d'));
			}

			$this->redirectToAction('index', array('bzda_success_remove' => 1));
		}

		public function runExportAction()
		{
			$task_id = (int)$this->request->get('task_id', 0);

			$data_provider = $this->getExportDataProvider($task_id);

			if( !$data_provider->runExport() ) {
				if( !empty($data_provider->errors) ) {
					foreach($data_provider->errors as $error) {
						echo $error;
					}

					return;
				}
			}

			$this->redirectToAction('export-logs', array('task_id' => $task_id));
		}

		public function getExportDataProvider($task_id)
		{
			require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.values.provider.php';
			require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.excute.php';

			return new BZDA_EXPORT_ADN_ExportExcute($task_id);
		}
	}
	
	FactoryPages000::register($bizpanda, 'BZDA_AND_ExportPage');
/*@mix:place*/