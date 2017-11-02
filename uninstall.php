<?php

	// if uninstall.php is not called by WordPress, die
	if( !defined('WP_UNINSTALL_PLUGIN') ) {
		die;
	}

	// remove plugin options
	global $wpdb;

	$wpdb->query("DROP TABLE {$wpdb->prefix}{$wpdb->prefix}opanda_export;");
	$wpdb->query("DROP TABLE {$wpdb->prefix}{$wpdb->prefix}opanda_export_logs;");
