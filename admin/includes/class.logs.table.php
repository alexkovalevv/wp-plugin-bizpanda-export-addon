<?php

	class BZDA_ADN_ExportLogListTable extends WP_List_Table {

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
				? '<br><a href="' . admin_url('admin.php?page=export-bizpanda&task_id=' . $item->ID . '&action=export&export_type=leads') . '">' . __('Edit') . '</a> | <a href="' . admin_url('admin.php?page=export-bizpanda&task_id=' . $item->ID . '&action=remove_task') . '">' . __('Remove') . '</a>'
				: '';
		}

		public function no_items()
		{
			echo __('No logs found. ', 'bizpanda-export-addon');
		}

		/**
		 * Define the columns that are going to be used in the table
		 * @return array $columns, the array of columns to use with the table
		 */
		function get_columns()
		{
			return $columns = array(
				'download' => '',
				'filename' => __('File name', 'bizpanda-export-addon'),
				'date' => __('Created', 'bizpanda-export-addon'),
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
			//more
		}

		/**
		 * Prepare the table with different parameters, pagination, columns and table elements
		 */
		function prepare_items()
		{
			global $wpdb;

			$task_id = isset($_GET['task_id'])
				? (int)$_GET['task_id']
				: null;

			$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}opanda_export_logs WHERE task_id = '{$task_id}' ORDER BY created_at DESC");
			$this->items = $results;
		}

		/**
		 * Shows when the lead was added.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_date($record)
		{
			echo date('d-M-Y H:i:s', $record->created_at);
		}

		/**
		 * Shows when the lead was added.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_filename($record)
		{
			echo basename($record->filepath) . ' (' . $this->formatSizeUnits(filesize($record->filepath)) . ')';
		}

		public function formatSizeUnits($bytes)
		{
			if( $bytes >= 1073741824 ) {
				$bytes = number_format($bytes / 1073741824, 2) . ' GB';
			} elseif( $bytes >= 1048576 ) {
				$bytes = number_format($bytes / 1048576, 2) . ' MB';
			} elseif( $bytes >= 1024 ) {
				$bytes = number_format($bytes / 1024, 2) . ' KB';
			} elseif( $bytes > 1 ) {
				$bytes = $bytes . ' bytes';
			} elseif( $bytes == 1 ) {
				$bytes = $bytes . ' byte';
			} else {
				$bytes = '0 bytes';
			}

			return $bytes;
		}

		/**
		 * Shows when the lead was added.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_download($record)
		{
			echo '<a class="add-new-h2 bizpanda-export-run-button" href="' . $record->download_url . '">Download</a>';
		}
	}
	/*@mix:place*/