<?php

	class BZDA_ADN_ExportListTable extends WP_List_Table {

		public function __construct($options = array())
		{

			$options['singular'] = __('All export', 'bizpanda-export-addon');
			$options['plural'] = __('All export', 'bizpanda-export-addon');
			$options['ajax'] = false;

			parent::__construct($options);
			$this->bulk_delete();
		}

		/**
		 * Generates and display row actions links for the list table.
		 *
		 * @since 4.3.0
		 * @access protected
		 *
		 * @param object $item The item being acted upon.
		 * @param string $column_name Current column name.
		 * @param string $primary Primary column name.
		 * @return string The row actions HTML, or an empty string if the current column is the primary column.
		 */
		protected function handle_row_actions($item, $column_name, $primary)
		{
			return $column_name === 'name'
				? '<br><a href="' . admin_url('admin.php?page=export-bizpanda&task_id=' . $item->ID . '&action=export&export_type=leads') . '">' . __('Edit') . '</a> | <a style="color:red;" href="' . admin_url('admin.php?page=export-bizpanda&task_id=' . $item->ID . '&action=remove_task') . '">' . __('Remove') . '</a>'
				: '';
		}

		public function no_items()
		{
			echo __('No leads found. ', 'bizpanda-export-addon');
		}

		/**
		 * Define the columns that are going to be used in the table
		 * @return array $columns, the array of columns to use with the table
		 */
		function get_columns()
		{

			return $columns = array(

				'cb' => '<input type="checkbox" />',
				'id' => __('ID', 'bizpanda-export-addon'),
				'name' => __('Name', 'bizpanda-export-addon'),
				'summary' => __('Summary', 'bizpanda-export-addon'),
				'autoexport' => __('Auto export', 'bizpanda-export-addon'),
				'action' => '',
			);
		}


		public function get_bulk_actions()
		{
			$actions = array(
				'delete' => __('Delete', 'bizpanda-export-addon')
			);

			return $actions;
		}

		/**
		 * Checks and runs the bulk action 'delete'.
		 */
		public function bulk_delete()
		{
			global $wpdb;

			$action = $this->current_action();

			if( 'delete' !== $action ) {
				return;
			}

			if( isset($_POST['bizpanda_export_tasks']) ) {
				$checked_tasks = is_array($_POST['bizpanda_export_tasks'])
					? implode(',', array_map('intval', $_POST['bizpanda_export_tasks']))
					: null;

				$wpdb->query("DELETE FROM {$wpdb->prefix}opanda_export WHERE ID IN($checked_tasks)");

				$get_logs = $wpdb->get_results("SELECT filepath FROM {$wpdb->prefix}opanda_export_logs WHERE task_id IN($checked_tasks)");

				if( !empty($get_logs) ) {
					foreach($get_logs as $value) {
						if( file_exists($value->filepath) ) {
							unlink($value->filepath);
						}
					}
					$wpdb->query("DELETE FROM {$wpdb->prefix}opanda_export_logs WHERE task_id IN($checked_tasks)");
				}
			}
			//more
		}

		/**
		 * Prepare the table with different parameters, pagination, columns and table elements
		 */
		function prepare_items()
		{
			global $wpdb;

			$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}opanda_export");
			$this->items = $results;
		}

		/**
		 * Shows a checkbox.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_cb($record)
		{
			return sprintf('<input type="checkbox" name="bizpanda_export_tasks[]" value="%s" />', $record->ID);
		}

		/**
		 * Shows an avatar of the lead.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_id($record)
		{
			echo $record->ID;
		}

		/**
		 * Shows a name of the lead.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_name($record)
		{
			echo '<strong>' . $record->task_title . '</strong>';
		}


		/**
		 * Shows when the lead was added.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_action($record)
		{
			echo '<a class="add-new-h2 bizpanda-export-run-button" href="' . admin_url('admin.php?page=export-bizpanda&action=export-process&task_id=' . $record->ID) . '&referrer_action=index">Run Export</a>';
			echo '<a class="add-new-h2 bizpanda-export-run-button" href="' . admin_url('admin.php?page=export-bizpanda&action=export-logs&task_id=' . $record->ID) . '&referrer_action=index">Download</a>';
		}

		/**
		 * Shows when the lead was added.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_summary($record)
		{
			echo 'Last task update: ' . date('Y-m-d H:i:s', $record->updated_at);
		}

		/**
		 * Shows a status of the lead.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_autoexport($record)
		{
			$color_class = 'green';

			if( !$record->autoexport ) {
				$color_class = 'grey';
			}

			echo '<span class="bizpanda-table-circle ' . $color_class . '"></span>';
		}
	}
	/*@mix:place*/