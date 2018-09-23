<?php
/**
 * Run the incremental updates one by one.
 *
 * For example, if the current DB version is 3, and the target DB version is 6,
 * this function will execute update routines if they exist:
 *  - au_stockquote_update_routine_4()
 *  - au_stockquote_update_routine_5()
 *  - au_stockquote_update_routine_6()
 */

function au_stockquote_update() {

	// no PHP timeout for running updates
	set_time_limit( 0 );

	// this is the current database schema version number
	$current_db_ver = (int) get_option( 'stockquote_db_ver', 0 );

	// this is the target version that we need to reach
	$target_db_ver = (int) Wpau_Stock_Quote::DB_VER;

	// run update routines one by one until the current version number
	// reaches the target version number
	while ( $current_db_ver < $target_db_ver ) {
		// increment the current db_ver by one
		++$current_db_ver;

		// each db version will require a separate update function
		// for example, for db_ver 3, the function name should be solis_update_routine_3
		$func = "au_stockquote_update_routine_{$current_db_ver}";
		if ( function_exists( $func ) ) {
			call_user_func( $func );
		}

		// update the option in the database, so that this process can always
		// pick up where it left off
		update_option( 'stockquote_db_ver', $current_db_ver );
	}

	// Update plugin version number
	update_option( 'stockquote_version', Wpau_Stock_Quote::VER );

} // END function au_stockquote_update()

/**
 * Migrate pre-0.2.0 to 0.2.0 version
 */
function au_stockquote_update_routine_1() {

	// Move settings from old option to new option and delete old option
	if ( $old_option_value = get_option( 'stock_quote_defaults' ) ) {
		add_option( 'stockquote_defaults', $old_option_value );
		delete_option( 'stock_quote_defaults' );
	}

	// Migrate legacy settings if still exists
	$defaults = get_option( 'stockquote_defaults' );

	// Add new options avapikey and all_symbols
	if ( ! isset( $defaults['avapikey'] ) ) {
		$defaults['avapikey'] = '';
	}
	if ( ! isset( $defaults['all_symbols'] ) ) {
		$defaults['all_symbols'] = $defaults['symbol'];
	}

	// If fetch timeout is shorted than 4 seconds, increase timeout to 4 seconds
	if ( (int) $defaults['timeout'] < 4 ) {
		$defaults['timeout'] = 4;
	}

	// Update options
	update_option( 'stockquote_defaults', $defaults );

	// clear temporary vars
	unset( $old_option_value, $defaults );

	// Create DB table
	global $wpdb;

	$table_name = $wpdb->prefix . 'stock_quote_data';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`symbol` varchar(20) NOT NULL,
		`raw` text NOT NULL,
		`last_refreshed` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`tz` varchar(20) NOT NULL,
		`last_open` decimal(13,4) NOT NULL,
		`last_high` decimal(13,4) NOT NULL,
		`last_low` decimal(13,4) NOT NULL,
		`last_close` decimal(13,4) NOT NULL,
		`last_volume` int NOT NULL,
		`change` decimal(13,4) NOT NULL,
		`changep` decimal(13,4) NOT NULL,
		`range` varchar(60) DEFAULT '' NOT NULL,
		PRIMARY KEY  (`id`),
		UNIQUE `symbol` (`symbol`)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

} // END function au_stockquote_update_routine_1()

// Add av_api_tier setting
function au_stockquote_update_routine_2() {
	$defaults = get_option( 'stockquote_defaults' );
	if ( ! isset( $defaults['av_api_tier'] ) ) {
		try {
			$defaults['av_api_tier'] = 'free';
			update_option( 'stockquote_defaults', $defaults );
		} catch (Exception $w) {}
	}
} // END function au_stockquote_update_routine_2()
