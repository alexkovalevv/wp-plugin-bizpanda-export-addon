<?php

	/**
	 * Export excute class
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 30.10.2017, Webcraftic
	 * @version 1.0
	 */
	class BZDA_EXPORT_ADN_ExportExcute extends BZDA_EXPORT_ADN_OptionsValueProvider {

		public $errors = array();

		private $leads = array();


		public function getLeads()
		{
			global $wpdb;

			if( !empty($this->leads) ) {
				return $this->leads;
			}

			if( empty($this->ID) ) {
				$this->errors['empty_id'] = __('Task ID is empty', 'bizpanda-export-addon');

				return null;
			}

			$selected_custom_fields = array();

			foreach($this->getValue('custom_fields', array()) as $custom_field) {
				$selected_custom_fields[] = $custom_field;
			}

			$lockers_string = $this->getValue('lockers');
			$lockers_array = array_filter(explode(',', $lockers_string), array($this, 'filterLockersList'));
			$lockers = implode(',', $lockers_array);

			$fields_str = $this->getValue('fields', array());
			$fields_array = explode(',', $fields_str);

			if( empty($lockers) || empty($fields_str) ) {
				$this->errors['not_selected_channel_or_fields'] = __('Please make sure that you selected at least one channel and field.', 'bizpanda');

				return null;
			}

			$sql = 'SELECT leads.ID,';

			$sqlFields = array();
			foreach($fields_array as $field) {
				$sqlFields[] = 'leads.' . $field;
			}

			$sql .= implode(',', $sqlFields);

			if( !empty($selected_custom_fields) ) {
				$sql .= ',fields.field_name,fields.field_value';
			}

			$sql .= ' FROM ' . $wpdb->prefix . 'opanda_leads AS leads ';

			if( !empty($selected_custom_fields) ) {
				$sql .= 'LEFT JOIN ' . $wpdb->prefix . 'opanda_leads_fields AS fields ON fields.lead_id = leads.ID ';
			}

			$sql .= 'WHERE leads.lead_item_id IN (' . $lockers . ')';

			if( 'all' != $this->getValue('status') ) {
				$sql .= ' AND leads.lead_email_confirmed = ' . (('confirmed' == $this->getValue('status'))
						? '1'
						: '0');
			}

			$result = $wpdb->get_results($sql, ARRAY_A);

			$leads = array();

			$exclude_previous_data = $this->getValue('exclude_previous_data', false);

			foreach($result as $item) {
				$id = $item['ID'];

				if( !empty($exclude_previous_data) && is_array($this->last_export_lead_ids) && in_array($id, $this->last_export_lead_ids) ) {
					continue;
				}

				if( !isset($leads[$id]) ) {
					$leads[$id] = array();
					foreach($fields_array as $field)
						$leads[$id][$field] = $item[$field];
					foreach($selected_custom_fields as $field)
						$leads[$id][$field] = null;
				}

				if( !empty($item['field_name']) && in_array($item['field_name'], $selected_custom_fields) ) {
					$leads[$id][$item['field_name']] = $item['field_value'];
				}
			}

			$this->leads = $leads;

			if( $exclude_previous_data && !empty($this->last_export_lead_ids) && empty($leads) ) {
				$this->errors['nothing_found'] = __('Since the last export, no new leads have been found.', 'bizpanda');

				return null;
			}

			return $this->leads;
		}

		public function runExport()
		{
			global $wpdb;

			$leads = $this->getLeads();

			if( empty($this->ID) || empty($leads) ) {
				$this->errors['empty_export_data'] = __('Export data is empty', 'bizpanda-export-addon');

				return false;
			}

			$filename = 'leads-' . date('Y-m-d-H-i-s');

			$relative_path = $this->getValue('local_folder_path', 'wp-content/uploads/bizpanda-all-export');
			$abs_path = ABSPATH . $relative_path;
			$dir_temp = $abs_path . '/temp/';

			$filepath = $dir_temp . $filename . '.csv';

			if( !file_exists($dir_temp) ) {
				if( !mkdir($dir_temp, 0777, true) ) {
					$this->errors['folder_created_error'] = __('Plugin could not create a directory for storing export files.', 'bizpanda');
				}
			}

			$file = fopen($filepath, "w");

			foreach($leads as $lead_id => $row) {
				fputcsv($file, $row, $this->getValue('delimiter'));
			}

			fclose($file);

			if( !file_exists($filepath) ) {
				$this->errors['file_created_error'] = __('A failed attempt to create a file.', 'bizpanda');

				return false;
			}

			$download_file_name = $filename . '.zip';
			$download_file_path = $abs_path . '/' . $download_file_name;
			$download_file_url = site_url($relative_path) . '/' . $download_file_name;

			require_once BZDA_EXPORT_ADN_PLUGIN_DIR . '/admin/includes/class.zip-archive.php';

			Bizpanda_ExtendedZip::zipTree($dir_temp, $download_file_path, ZipArchive::CREATE);

			array_map('unlink', glob($dir_temp . "/*"));

			if( $this->getValue('exclude_previous_data', false) ) {
				$lead_ids = array_keys($leads);

				if( !empty($this->last_export_lead_ids) ) {
					$this->last_export_lead_ids = array_merge($this->last_export_lead_ids, $lead_ids);
				} else {
					$this->last_export_lead_ids = $lead_ids;
				}

				$this->saveChanges();
			}

			$wpdb->query($wpdb->prepare("
					INSERT INTO {$wpdb->prefix}opanda_export_logs (task_id, download_url, filepath, created_at)
					VALUES (%d, %s, %s, %d);", $this->ID, $download_file_url, $download_file_path, time()));

			/*header("Content-Type: text/csv");
			header("Content-Disposition: attachment; filename=" . $filename);
			header("Cache-Control: no-cache, no-store, must-revalidate");
			header("Pragma: no-cache");
			header("Expires: 0");*/

			/*$output = fopen("php://output", "w");
			foreach($leads as $row) {
				fputcsv($output, $row, $this->getValue('delimiter'));
			}
			fclose($output);*/

			//exit;

			return true;
		}

		public function filterLockersList($value)
		{
			$value = intval($value);

			return !empty($value);
		}
	}