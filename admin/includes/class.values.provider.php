<?php
	/**
	 * Export options value provider
	 *
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 29.10.2017, Webcraftic
	 *
	 * @package factory-forms
	 * @since 1.0.0
	 */

	/**
	 * Factory Options Value Provider
	 *
	 * This provide stores form values in the wordpress options.
	 *
	 * @since 1.0.0
	 */
	class BZDA_EXPORT_ADN_OptionsValueProvider implements IFactoryForms000_ValueProvider {

		protected $ID;

		protected $task_title;

		protected $export_options;

		protected $last_export_lead_ids = array();

		protected $autoexport = false;

		/**
		 * Creates a new instance of an options value provider.
		 * @param int $task_id
		 */
		public function __construct($task_id = 0)
		{
			global $wpdb;
			$this->ID = (int)trim($task_id);

			if( !empty($this->ID) ) {
				$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}opanda_export WHERE ID='%d' LIMIT 1", $this->ID));

				if( !empty($results) ) {
					$this->task_title = $results[0]->task_title;
					$this->export_options = unserialize($results[0]->export_options);
					$this->last_export_lead_ids = unserialize($results[0]->last_export_lead_ids);
					$this->autoexport = $results[0]->autoexport;

					return;
				}
				$this->ID = 0;
			}
		}

		/**
		 * @since 1.0.0
		 */
		public function init()
		{
			// nothing to do
		}

		/**
		 * @since 1.0.0
		 */
		public function saveChanges()
		{
			global $wpdb;

			if( empty($this->task_title) ) {
				$this->task_title = __('(not title)', 'bizpanda');
			}

			$export_options = serialize($this->export_options);
			$last_export_lead_ids = serialize($this->last_export_lead_ids);

			if( !empty($this->ID) ) {
				$result_update = $wpdb->update($wpdb->prefix . 'opanda_export', array(
					'task_title' => $this->task_title,
					'export_options' => $export_options,
					'last_export_lead_ids' => $last_export_lead_ids,
					'updated_at' => time(),
					'autoexport' => $this->autoexport
				), array('ID' => $this->ID), array('%s', '%s', '%s', '%d', '%d'), array('%d'));

				if( !$result_update ) {
					return null;
				}

				return $this->ID;
			}

			$wpdb->query($wpdb->prepare("
				INSERT INTO {$wpdb->prefix}opanda_export (task_title, export_options, last_export_lead_ids, updated_at, autoexport)
				VALUES (%s, %s, %s, %d, %d);", $this->task_title, $export_options, $last_export_lead_ids, time(), $this->autoexport));

			return $wpdb->insert_id;
		}

		public function getValue($name, $default = null, $multiple = false)
		{
			$value = isset($this->export_options[$name])
				? $this->export_options[$name]
				: $default;

			if( $value === 'true' ) {
				$value = 1;
			}
			if( $value === 'false' ) {
				$value = 0;
			}

			return $value;
		}

		/**
		 * @param string $name
		 * @param mixed $value
		 */
		public function setValue($name, $value)
		{
			switch( $name ) {
				case 'task_title':
					$this->task_title = $value;
					break;
				case 'autoexport':
					$this->autoexport = intval($value);
					break;
				case 'local_folder_path':
					$value = stripslashes($value);
					break;
			}

			$this->export_options[$name] = $value;
		}
	}
