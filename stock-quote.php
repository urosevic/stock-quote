<?php
/**
Plugin Name: Stock Quote
Plugin URI: https://urosevic.net/wordpress/plugins/stock-quote/
Description: Insert static inline stock ticker for known exchange symbols by customizable shortcode.
Version: 0.2.0
Author: Aleksandar Urosevic
Author URI: https://urosevic.net
License: GNU GPL3
Textdomain: stock-quote
 * @package  Stock Quote
 */

/**
 * Copyright 2015-2018 Aleksandar Urosevic (urke.kg@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @TODO:
 * * Add loading of stock data by AJAX on front-end
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wpau_Stock_Quote' ) ) {

	/**
	 * Wpau_Stock_Quote Class provide main plugin functionality
	 *
	 * @category Class
	 * @package Stock Quote
	 * @author Aleksandar Urosevic
	 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
	 * @link https://urosevic.net
	 */
	class Wpau_Stock_Quote {

		const DB_VER = 1;
		const VER = '0.2.0';

		public $plugin_name   = 'Stock Quote';
		public $plugin_slug   = 'stock-quote';
		public $plugin_option = 'stockquote_defaults';
		public $plugin_url;

		public static $exchanges = array(
			'ASX'    => 'Australian Securities Exchange',
			'BOM'    => 'Bombay Stock Exchange',
			'BIT'    => 'Borsa Italiana Milan Stock Exchange',
			'TSE'    => 'Canadian/Toronto Securities Exchange',
			'FRA'    => 'Deutsche Boerse Frankfurt Stock Exchange',
			'ETR'    => 'Deutsche Boerse Frankfurt Stock Exchange',
			'AMS'    => 'Euronext Amsterdam',
			'EBR'    => 'Euronext Brussels',
			'ELI'    => 'Euronext Lisbon',
			'EPA'    => 'Euronext Paris',
			'LON'    => 'London Stock Exchange',
			'MCX'    => 'Moscow Exchange',
			'NASDAQ' => 'NASDAQ Exchange',
			'CPH'    => 'NASDAQ OMX Copenhagen',
			'HEL'    => 'NASDAQ OMX Helsinki',
			'ICE'    => 'NASDAQ OMX Iceland',
			'STO'    => 'NASDAQ OMX Stockholm',
			'NSE'    => 'National Stock Exchange of India',
			'NYSE'   => 'New York Stock Exchange',
			'SGX'    => 'Singapore Exchange',
			'SHA'    => 'Shanghai Stock Exchange',
			'SHE'    => 'Shenzhen Stock Exchange',
			'TPE'    => 'Taiwan Stock Exchange',
			'TYO'    => 'Tokyo Stock Exchange',
		);

		/**
		 * Construct the plugin object
		 */
		public function __construct() {

			$this->plugin_url = plugin_dir_url( __FILE__ );
			$this->plugin_file = plugin_basename( __FILE__ );
			load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			// Installation and uninstallation hooks.
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			// Throw message on multisite
			if ( is_multisite() ) {
				add_action( 'admin_notices', array( $this, 'multisite_notice' ) );
				return;
			}

			// Maybe update trigger.
			add_action( 'plugins_loaded', array( $this, 'maybe_update' ) );

			// Cleanup transients
			if ( ! empty( $_GET['stockquote_purge_cache'] ) ) {
				self::restart_av_fetching();
			}

			// Initialize default settings
			$this->defaults = self::defaults();

			// Register AJAX stock updater
			add_action( 'wp_ajax_stockquote_update_quotes', array( $this, 'ajax_stockquote_update_quotes' ) );
			add_action( 'wp_ajax_nopriv_stockquote_update_quotes', array( $this, 'ajax_stockquote_update_quotes' ) );
			// Restart fetching loop by AJAX request
			add_action( 'wp_ajax_stockquote_purge_cache', array( $this, 'ajax_restart_av_fetching' ) );
			add_action( 'wp_ajax_nopriv_stockquote_purge_cache', array( $this, 'ajax_restart_av_fetching' ) );

			if ( is_admin() ) {
				// Initialize Plugin Settings Magic
				add_action( 'init', array( $this, 'admin_init' ) );
				// Maybe display admin notices?
				add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			} else {
				// Enqueue frontend scripts.
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}

			// Add dynamic scripts and styles to footer.
			add_action( 'wp_head', array( $this, 'wp_head' ), 999 );

			// Register stock_quote shortcode.
			add_shortcode( 'stock_quote', array( $this, 'shortcode' ) );

		} // END public function __construct()

		/**
		 * Throw notice that plugin does not work on Multisite
		 */
		function multisite_notice() {
			$class = 'notice notice-error';
			$message = sprintf(
				__( 'We are sorry, %1$s v%2$s does not support Multisite WordPress.', 'wpausq' ),
				$this->plugin_name,
				self::VER
			);
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}

		function admin_notice() {

			$missing_option = array();

			// If no AlphaVantage API Key, display admin notice
			if ( empty( $this->defaults['avapikey'] ) ) {
				$missing_option[] = __( 'AlphaVantage.co API Key', 'wpaust' );
			}

			// If no all symbls, display admin notice
			if ( empty( $this->defaults['all_symbols'] ) ) {
				$missing_option[] = __( 'All Stock Symbols', 'wpaust' );
			}

			if ( ! empty( $missing_option ) ) {
				$class = 'notice notice-error';
				$missing_options = '<ul><li>' . join( '</li><li>', $missing_option ) . '</li></ul>';
				$settings_title = __( 'Settings' );
				$settings_link = "<a href=\"options-general.php?page={$this->plugin_slug}\">{$settings_title}</a>";
				$message = sprintf(
					__( 'Plugin %1$s v%2$s require that you have defined options listed below to work properly. Please visit plugin %3$s page and read description for those options. %4$s', 'wpaust' ),
					"<strong>{$this->plugin_name}</strong>",
					self::VER,
					$settings_link,
					$missing_options
				);
				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
			}

		} // END function admin_notice()

		/**
		 * Activate the plugin
		 */
		function activate() {
			// Auto disable on WPMU
			if ( is_multisite() ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				wp_die( sprintf(
					__( 'We are sorry, %1$s v%2$s does not support Multisite WordPress.', 'wpausq' ),
					$this->plugin_name,
					self::VER
				) );
			}
			// Single WP activation process
			global $wpau_stockquote;
			$wpau_stockquote->init_options();
			$wpau_stockquote->maybe_update();
		} // END function activate()

		/**
		 * Deactivate the plugin
		 */
		function deactivate() {
			// Do nothing
		} // END function deactivate()

		/**
		 * Return initial options
		 * @return array Global defaults for current plugin version
		 */
		function init_options() {
			$init = array(
				'all_symbols'   => 'AAPL',
				'symbol'        => 'AAPL',
				'show'          => 'name',
				'zero'          => '#454545',
				'minus'         => '#D8442F',
				'plus'          => '#009D59',
				'cache_timeout' => '180', // 3 minutes
				'error_message' => 'Unfortunately, we could not get stock quote %symbol% this time.',
				'legend'        => "AAPL;Apple Inc.\nFB;Facebook, Inc.\nCSCO;Cisco Systems, Inc.\nGOOG;Google Inc.\nINTC;Intel Corporation\nLNKD;LinkedIn Corporation\nMSFT;Microsoft Corporation\nTWTR;Twitter, Inc.\nBABA;Alibaba Group Holding Limited\nIBM;International Business Machines Corporation\n.DJI;Dow Jones Industrial Average\nEURGBP;Euro (€) ⇨ British Pound Sterling (£)",
				'style'         => '',
				'timeout'       => 4,
				'loading_message' => 'Loading stock data...',
				'number_format' => 'dc',
				'decimals'      => 2,
			);

			add_option( $this->plugin_option, $init, '', 'no' );

			return $init;

		} // END public static function defaults()

		/**
		 * Check do we need to migrate options
		 */
		function maybe_update() {
			// Bail if this plugin data doesn't need updating
			if ( get_option( 'stockquote_db_ver', 0 ) >= self::DB_VER ) {
				return;
			}
			require_once( dirname( __FILE__ ) . '/update.php' );
			au_stockquote_update();
		} // END function maybe_update()

		/**
		 * Initialize Settings link for Plugins page and create Settings page
		 *
		 */
		function admin_init() {

			// Add plugin Settings link.
			add_filter( 'plugin_action_links_' . $this->plugin_file, array( $this, 'plugin_settings_link' ) );

			// Update links in plugin row on Plugins page.
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );

			// Load colour picker scripts on plugin settings page and on widgets/customizer.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			require_once( 'inc/settings.php' );

			global $wpau_stockquote_settings;
			if ( empty( $wpau_stockquote_settings ) ) {
				$wpau_stockquote_settings = new Wpau_Stock_Quote_Settings();
			}

		} // END function admin_init_settings()

		/**
		 * Add link to official plugin pages
		 * @param array $links  Array of existing plugin row links.
		 * @param string $file  Path of current plugin file.
		 * @return array        Array of updated plugin row links
		 */
		function add_plugin_meta_links( $links, $file ) {
			if ( 'stock-quote/stock-quote.php' === $file ) {
				return array_merge(
					$links,
					array(
						sprintf(
							'<a href="https://wordpress.org/support/plugin/stock-quote" target="_blank">%s</a>',
							__( 'Support' )
						),
						sprintf(
							'<a href="https://urosevic.net/wordpress/donate/?donate_for=stock-quote" target="_blank">%s</a>',
							__( 'Donate' )
						),
					)
				);
			}
			return $links;
		} // END function add_plugin_meta_links()

		/**
		 * Generate Settings link on Plugins page listing
		 * @param  array $links Array of existing plugin row links.
		 * @return array        Updated array of plugin row links with link to Settings page
		 */
		function plugin_settings_link( $links ) {
			$settings_title = __( 'Settings' );
			$settings_link = "<a href=\"options-general.php?page={$this->plugin_slug}\">{$settings_title}</a>";
			array_unshift( $links, $settings_link );
			return $links;
		} // END function plugin_settings_link()

		/**
		 * Enqueue the admin style.
		 */
		function admin_scripts( $hook ) {
			if ( 'settings_page_' . $this->plugin_slug == $hook ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style(
					$this->plugin_slug . '-admin',
					plugins_url( 'assets/css/admin.css', __FILE__ ),
					array(),
					self::VER
				);
				wp_register_script(
					$this->plugin_slug . '-admin',
					$this->plugin_url . ( WP_DEBUG ? 'assets/js/jquery.admin.js' : 'assets/js/jquery.admin.min.js' ),
					array( 'jquery' ),
					self::VER,
					true
				);
				wp_localize_script(
					$this->plugin_slug . '-admin',
					'stockQuoteJs',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'avurl'    => 'https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&outputsize=compact&apikey=' . $this->defaults['avapikey'] . '&symbol=',
					)
				);
				wp_enqueue_script( $this->plugin_slug . '-admin' );

			}
		} // END function admin_scripts()

		/**
		 * Enqueue frontend assets
		 */
		function enqueue_scripts() {
			$defaults = $this->defaults;
			$upload_dir = wp_upload_dir();

			wp_register_script(
				$this->plugin_slug,
				$this->plugin_url . ( WP_DEBUG ? 'assets/js/jquery.stockquote.js' : 'assets/js/jquery.stockquote.min.js' ),
				array( 'jquery' ),
				self::VER,
				true
			);
			wp_localize_script(
				$this->plugin_slug,
				'stockQuoteJs',
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
			);
		} // END function enqueue_scripts()

		/**
		 * Output custom styling
		 */
		public static function wp_head() {

			// Start with default CSS style.
			$css = '.widget .stock_quote{margin:0;padding:0}.stock_quote.sqitem.error{font-weight:bold}.stock_quote.sqitem.minus::before,.stock_quote.sqitem.plus::before{display:inline-block;margin-right:2px;content:"";width:10px;height:14px;background:url(data:image/gif;base64,R0lGODlhFAAKAKIHAMwzAACZAABmAJkAAMyIcsz/zDOZM////yH5BAEAAAcALAAAAAAUAAoAQAM3eHol+2TIp0oIjhIAxrJXGBjZsXUUOJbK6aUXe3LcW62UWT/qRUI7GGYxowUZMY3RVhE4cyZmAgA7) no-repeat}.stock_quote.sqitem.minus::before{background-position:-10px 4px}.stock_quote.sqitem.plus::before{background-position:0 4px}';

			// Append dynamic style and colours from plugin settings.
			if ( ! empty( $this->defaults['style'] ) ) {
				$css .= '.stock_quote.sqitem{' . $this->defaults['style'] . '}';
			}
			if ( ! empty( $this->defaults['zero'] ) ) {
				$css .= '.stock_quote.sqitem.zero,.stock_quote.sqitem.zero:hover{color:' . $this->defaults['zero'] . '}';
			}
			if ( ! empty( $this->defaults['minus'] ) ) {
				$css .= '.stock_quote.sqitem.minus,.stock_quote.sqitem.minus:hover{color:' . $this->defaults['minus'] . '}';
			}
			if ( ! empty( $this->defaults['plus'] ) ) {
				$css .= '.stock_quote.sqitem.plus,.stock_quote.sqitem.plus:hover{color:' . $this->defaults['plus'] . '}';
			}

			// Output generated CSS block.
			if ( ! empty( $css ) ) {
				echo '<style type="text/css">' . $css . '</style>';
			}

		} // END public static function wp_head()

		/**
		 * Get default options from DB
		 * @return array Latest global defaults
		 */
		public function defaults() {
			$defaults = get_option( $this->plugin_option );
			if ( empty( $defaults ) ) {
				$defaults = $this->init_options();
			}
			return $defaults;
		} // END public function defaults()

		/**
		 * Delete control options to force re-fetching from first symbol
		 */
		public static function restart_av_fetching() {
			update_option( 'stockquote_av_last', '' );
			$expired_timestamp = time() - ( 10 * YEAR_IN_SECONDS );
			update_option( 'stockquote_av_last_timestamp', $expired_timestamp );
			update_option( 'stockquote_av_progress', false );
			self::log( 'Stock Quote: data fetching from first symbol has been restarted' );
		} // END public static function restart_av_fetching() {

		function ajax_restart_av_fetching() {
			self::restart_av_fetching();
			$result['status']  = 'success';
			$result['message'] = 'OK';
			$result = json_encode( $result );
			echo $result;
			wp_die();
		} // END function ajax_restart_av_fetching() {

		/**
		 * AJAX to update AlphaVantage.co quotes
		 */
		function ajax_stockquote_update_quotes() {
			$response = $this->get_alphavantage_quotes();
			$result['status']  = 'success';
			$result['message'] = $response['message'];
			$result['symbol']  = $response['symbol'];

			if ( strpos( $response['message'], 'no need to fetch' ) !== false ) {
				$result['done'] = true;
				$result['message'] = 'DONE';
			} else {
				$result['done'] = false;
				// If we have some plugin functionality fatal error
				// (missing API key, no symbols, can't write to DB, etc)
				// then throw error and signal stop fetching:
				// * There is no defined All Stock Symbols
				// * Failed to save stock data for {$symbol_to_fetch} to database!
				// * AlphaVantage.co API key has not set
				if ( strpos( $response['message'], 'Stock Quote Fatal Error:' ) !== false ) {
					$result['done'] = true;
				}
			}
			$result = json_encode( $result );

			echo $result;
			wp_die();
		} // END function ajax_stockquote_update_quotes()

		/**
		 * Generate content for quote item
		 * @param  string   $symbol        Stock symbol
		 * @param  string   $show          How to represent company (symbol or name).
		 * @param  boolean  $nolink        Should item be linked to Google Finance page? @deprecated
		 * @param  string   $class         Custom class name for block.
		 * @param  integer  $decimals      Number of decimal places.
		 * @param  string   $number_format Which number format to use (dc, sc, cd, sd).
		 * @param  string   $template      Format of text for quote.
		 * @return string                  Formatted HTMLoutput.
		 */
		public function stock_quote( $symbol = 'AAPL', $show = 'symbol', $nolink = false, $class = '', $decimals = null, $number_format = null, $template = null ) {

			if ( empty( $symbol ) ) {
				return;
			}

			// Get defaults.
			$defaults = $this->defaults;

			// Append this symbol to all_symbils if missing
			self::add_to_all_symbols( $symbol );

			// Prepare quote.
			$class = "stock_quote sqitem $class";

			// Get stock data from database
			$stock_data = self::get_stock_from_db( $symbol );
			if ( empty( $stock_data ) || empty( $stock_data[ $symbol ] ) ) {
				// return "{$out_start}{$out_error_msg}{$out_end}";
				// No results were returned.
				return sprintf(
					'<span class="%1$s error">%2$s</span>',
					$class, // 1
					str_replace( '%symbol%', $symbol, $defaults['error_message'] ) // 2
				);
			}

			// Prepare number format
			if ( ! empty( $number_format ) && in_array( $number_format, array( 'dc', 'sd', 'sc', 'cd' ) ) ) {
				$defaults['number_format'] = $number_format;
			} else if ( ! isset( $defaults['number_format'] ) ) {
				$defaults['number_format'] = 'cd';
			}
			switch ( $defaults['number_format'] ) {
				case 'dc': // 0.000,00
					$thousands_sep = '.';
					$dec_point     = ',';
					break;
				case 'sd': // 0 000.00
					$thousands_sep = ' ';
					$dec_point     = '.';
					break;
				case 'sc': // 0 000,00
					$thousands_sep = ' ';
					$dec_point     = ',';
					break;
				default: // 0,000.00
					$thousands_sep = ',';
					$dec_point     = '.';
			}

			// Prepare number of decimals
			if ( null !== $decimals ) {
				// From shortcode or widget
				$decimals = (int) $decimals;
			} else {
				// From settings
				if ( ! isset( $defaults['decimals'] ) ) {
					$defaults['decimals'] = 2;
				}
				$decimals = (int) $defaults['decimals'];
			}

			// Get legend for company names.
			$matrix = explode( "\n", $defaults['legend'] );
			$msize = count( $matrix );
			for ( $m = 0; $m < $msize; ++$m ) {
				$line = explode( ';', $matrix[ $m ] );
				$legend[ strtoupper( trim( $line[0] ) ) ] = trim( $line[1] );
			}
			unset( $m, $msize, $matrix, $line );

			// Start quote string.
			$q = '';

			// Assign object elements to vars.
			$q_symbol  = $symbol;
			$q_name    = $stock_data[ $symbol ]['symbol']; // ['t']; // No nicename on AlphaVantage.co so use ticker instead.
			$q_change  = $stock_data[ $symbol ]['change']; // ['c'];
			$q_price   = $stock_data[ $symbol ]['last_open']; // ['l'];
			$q_changep = $stock_data[ $symbol ]['changep']; // ['cp'];
			$q_volume  = $stock_data[ $symbol ]['last_volume'];
			$q_tz      = $stock_data[ $symbol ]['tz'];
			$q_ltrade  = $stock_data[ $symbol ]['last_refreshed']; // ['lt'];
			$q_ltrade  = str_replace( ' 00:00:00', '', $q_ltrade ); // Strip zero time from last trade date string
			$q_ltrade  = "{$q_ltrade} {$q_tz}";
			// Extract Exchange from Symbol
			$q_exch = '';
			if ( strpos( $symbol, ':' ) !== false ) {
				list( $q_exch, $q_symbol ) = explode( ':', $symbol );
			}

			// Define class based on change.
			$prefix = '';
			if ( $q_change < 0 ) {
				$chclass = 'minus';
			} elseif ( $q_change > 0 ) {
				$chclass = 'plus';
				$prefix = '+';
			} else {
				$chclass = 'zero';
				$q_change = '0.00';
			}
			$class = "$class $chclass";

			// Get custom company name if exists.
			if ( ! empty( $legend[ $q_exch . ':' . $q_symbol ] ) ) {
				// First in format EXCHANGE:SYMBOL.
				$q_name = $legend[ $q_exch . ':' . $q_symbol ];
			} else if ( ! empty( $legend[ $q_symbol ] ) ) {
				// Then in format SYMBOL.
				$q_name = $legend[ $q_symbol ];
			}

			// What to show: Symbol or Company Name?
			if ( 'name' == $show ) {
				$company_show = $q_name;
			} else {
				$company_show = $q_symbol;
			}

			// Format numbers.
			$q_price   = number_format( $q_price, $decimals, $dec_point, $thousands_sep );
			$q_change  = $prefix . number_format( $q_change, $decimals, $dec_point, $thousands_sep );
			$q_changep = $prefix . number_format( $q_changep, $decimals, $dec_point, $thousands_sep );

			$url_query = $q_symbol;
			if ( ! empty( $q_exch ) ) {
				$quote_title = $q_name . ' (' . self::$exchanges[ $q_exch ] . ', Volume ' . $q_volume . ', Last trade ' . $q_ltrade . ')';
			} else {
				$quote_title = $q_name . ' (Last trade ' . $q_ltrade . ')';
			}

			$template = ! empty( $template ) ? $template : '%company% %price% %change% %changep%';

			// Value template.
			$quote_text = $template;
			$quote_text = str_replace( '%company%', $company_show, $quote_text );
			$quote_text = str_replace( '%symbol%', $q_symbol, $quote_text );
			$quote_text = str_replace( '%exch_symbol%', $url_query, $quote_text );
			$quote_text = str_replace( '%price%', $q_price, $quote_text );
			$quote_text = str_replace( '%change%', $q_change, $quote_text );
			$quote_text = str_replace( '%changep%', "{$q_changep}%", $quote_text );
			$quote_text = str_replace( '%volume%', $q_volume, $quote_text );

			$out = sprintf(
				'<span class="%1$s" title="%2$s">%3$s</span>',
				$class,       // 1
				$quote_title, // 2
				$quote_text   // 3
			);

			unset( $q, $defaults, $legend );

			// Print quote content.
			return $out;

		} // END public static function stock_quote()

		/**
		 * Shortcode for stock quote
		 * @param  array $atts    Array of shortcode parameters.
		 * @return string         Composer HTML output
		 */
		public static function shortcode( $atts ) {

			$defaults = $this->defaults;
			$atts = shortcode_atts( array(
				'symbol'        => $defaults['symbol'],
				'show'          => $defaults['show'],
				'nolink'        => false,
				'class'         => '',
				'decimals'      => null,
				'number_format' => $defaults['number_format'],
				'template'      => '%company% %price% %change% %changep%',
			), $atts );

			if ( ! empty( $atts['symbol'] ) ) {
				wp_enqueue_script( $this->plugin_slug );
				$symbol = strip_tags( $atts['symbol'] );
				return self::stock_quote( $symbol, $atts['show'], $atts['nolink'], $atts['class'], $atts['decimals'], $atts['number_format'], $atts['template'] );
			}

		} // END public static function shortcode()

		// Thanks to https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
		private function get_stock_from_db( $symbols = '' ) {
			// If no symbols we have to fetch from DB, then exit
			if ( empty( $symbols ) ) {
				return;
			}

			global $wpdb;
			// Explode symbols to array
			$symbols_arr = explode( ',', $symbols );
			// Count how many entries will we select?
			$how_many = count( $symbols_arr );
			// prepare the right amount of placeholders for each symbol
			$placeholders = array_fill( 0, $how_many, '%s' );
			// glue together all the placeholders...
			$format = implode( ',', $placeholders );
			// put all in the query and prepare

			// retrieve the results from database
			$stock_data_a = $wpdb->get_results( $wpdb->prepare(
				"
				SELECT `symbol`,`tz`,`last_refreshed`,`last_open`,`last_high`,`last_low`,`last_close`,`last_volume`,`change`,`changep`,`range`
				FROM {$wpdb->prefix}stock_quote_data
				WHERE symbol IN ($format)
				",
				$symbols_arr
			), ARRAY_A );

			// If we don't have anything retrieved, just exit
			if ( empty( $stock_data_a ) ) {
				return;
			}

			// Convert DB result to associated array
			$stock_data = array();
			foreach ( $stock_data_a as $stock_data_item ) {
				$stock_data[ $stock_data_item['symbol'] ] = $stock_data_item;
			}

			// Return re-composed assiciated array
			return $stock_data;
		} // END private function get_stock_from_db( $symbols ) {

		/**
		 * Download stock quotes from AlphaVantage.co and store them all to single transient
		 */
		function get_alphavantage_quotes() {

			// Check is currently fetch in progress
			$progress = get_option( 'stockquote_av_progress', false );

			if ( false != $progress ) {
				return array(
					'message' => 'Stock Quote Skip: Currently fetching another symbol in other thread',
					'symbol'  => '',
				);
			}

			// Set fetch progress as active
			self::lock_fetch();

			// Get defaults (for API key)
			$defaults = $this->defaults;
			// Get symbols we should to fetch from AlphaVantage
			$symbols = $defaults['all_symbols'];

			// If we don't have defined global symbols, exit
			if ( empty( $symbols ) ) {
				return array(
					'message' => 'Stock Quote Fatal Error: There is no defined All Stock Symbols',
					'symbol'  => '',
				);
			}

			// Make array of global symbols
			$symbols_arr = explode( ',', $symbols );

			// Remove unsupported stock exchanges from global array to prevent API errors
			$symbols_supported = array();
			foreach ( $symbols_arr as $symbol_pos => $symbol_to_check ) {
				// If there is semicolon, it's symbol with exchange
				if ( strpos( $symbol_to_check, ':' ) ) {
					// Explode symbol so we can get exchange code
					$symbol_exchange = explode( ':', $symbol_to_check );
					// If exchange code is supported, add symbol to query array
					if ( ! empty( self::$exchanges[ strtoupper( trim( $symbol_exchange[0] ) ) ] ) ) {
						$symbols_supported[] = $symbol_to_check;
					}
				} else {
					// Add symbol w/o exchange to query array
					$symbols_supported[] = $symbol_to_check;
				}
			}
			// Set back query array to $symbols_arr
			$symbols_arr = $symbols_supported;

			// Default symbol to fetch first (first form array)
			$current_symbol_index = 0;
			$symbol_to_fetch = $symbols_arr[ $current_symbol_index ];

			// Get last fetched symbol
			$last_fetched = strtoupper( get_option( 'stockquote_av_last' ) );

			// Find which symbol we should fetch
			if ( ! empty( $last_fetched ) ) {
				$last_symbol_index = array_search( $last_fetched, $symbols_arr );
				$current_symbol_index = $last_symbol_index + 1;
				// If we have less than next symbol, then rewind to beginning
				if ( count( $symbols_arr ) <= $current_symbol_index ) {
					$current_symbol_index = 0;
				} else {
					$symbol_to_fetch = strtoupper( $symbols_arr[ $current_symbol_index ] );
				}
			}

			// If current_symbol_index is 0 and cache timeout has not expired,
			// do not attempt to fetch again but wait to expire timeout for next loop (UTC)
			if ( 0 == $current_symbol_index ) {
				$current_timestamp = time();
				$last_fetched_timestamp = get_option( 'stockquote_av_last_timestamp', $current_timestamp );
				$target_timestamp = $last_fetched_timestamp + (int) $defaults['cache_timeout'];
				if ( $target_timestamp > $current_timestamp ) {
					// If timestamp not expired, do not fetch but exit
					self::unlock_fetch();
					return array(
						'message' => 'Cache timeout has not expired, no need to fetch new loop at the moment.',
						'symbol'  => $symbol_to_fetch,
					);
				} else {
					// If timestamp expired, set new value and proceed
					update_option( 'stockquote_av_last_timestamp', $current_timestamp );
					self::log( 'Set current timestamp when first symbol is fetched as a reference for next loop' );
				}
			}

			// Now call AlphaVantage fetcher for current symbol
			$stock_data = $this->fetch_alphavantage_feed( $symbol_to_fetch );

			// If we have not got array with stock data, exit w/o updating DB
			if ( ! is_array( $stock_data ) ) {
				self::log( $stock_data );

				// If it's Invalid API call, report and skip it
				if ( strpos( $stock_data, 'Invalid API call' ) >= 0 ) {
					self::log( 'Damn, we got Invalid API call for symbol ' . $symbol_to_fetch );
					update_option( 'stockquote_av_last', $symbol_to_fetch );
				}

				// If we got some error for first symbol, (and resnponse has not invalid API) revert last timestamp
				if ( 0 == $current_symbol_index && false === strpos( $stock_data, 'Invalid API call' ) ) {
					self::log( 'Failed fetching and crunching for first symbol, set back previous timestamp' );
					update_option( 'stockquote_av_last_timestamp', $last_fetched_timestamp );
				}
				// Release processing for next run
				self::unlock_fetch();
				// Return response status
				return array(
					'message' => $stock_data,
					'symbol'  => $symbol_to_fetch,
				);
			}

			// With success stock data in array, save data to database
			global $wpdb;
			// Define plugin table name
			$table_name = $wpdb->prefix . 'stock_quote_data';
			// Check does symbol already exists in DB (to update or to insert new one)
			// I'm not using here $wpdb->replace() as I wish to avoid reinserting row to table which change primary key (delete row, insert new row)
			$symbol_exists = $wpdb->get_var( $wpdb->prepare(
				"
					SELECT symbol
					FROM {$wpdb->prefix}stock_quote_data
					WHERE symbol = %s
				",
				$symbol_to_fetch
			) );
			if ( ! empty( $symbol_exists ) ) {
				// UPDATE
				$ret = $wpdb->update(
					// table
					$table_name,
					// data
					array(
						'symbol'         => $stock_data['t'],
						'raw'            => $stock_data['raw'],
						'last_refreshed' => $stock_data['lt'],
						'tz'             => $stock_data['ltz'],
						'last_open'      => $stock_data['o'],
						'last_high'      => $stock_data['h'],
						'last_low'       => $stock_data['low'],
						'last_close'     => $stock_data['l'],
						'last_volume'    => $stock_data['v'],
						'change'         => $stock_data['c'],
						'changep'        => $stock_data['cp'],
						'range'          => $stock_data['r'],
					),
					// WHERE
					array(
						'symbol' => $stock_data['t'],
					),
					// format
					array(
						'%s', // symbol
						'%s', // raw
						'%s', // last_refreshed
						'%s', // tz
						'%f', // last_open
						'%f', // last_high
						'%f', // last_low
						'%f', // last_close
						'%d', // last_volume
						'%f', // last_change
						'%f', // last_changep
						'%s', // range
					),
					// WHERE format
					array(
						'%s',
					)
				);
			} else {
				// INSERT
				$ret = $wpdb->insert(
					// table
					$table_name,
					// data
					array(
						'symbol'         => $stock_data['t'],
						'raw'            => $stock_data['raw'],
						'last_refreshed' => $stock_data['lt'],
						'tz'             => $stock_data['ltz'],
						'last_open'      => $stock_data['o'],
						'last_high'      => $stock_data['h'],
						'last_low'       => $stock_data['low'],
						'last_close'     => $stock_data['l'],
						'last_volume'    => $stock_data['v'],
						'change'         => $stock_data['c'],
						'changep'        => $stock_data['cp'],
						'range'          => $stock_data['r'],
					),
					// format
					array(
						'%s', // symbol
						'%s', // raw
						'%s', // last_refreshed
						'%s', // tz
						'%f', // last_open
						'%f', // last_high
						'%f', // last_low
						'%f', // last_close
						'%d', // last_volume
						'%f', // last_change
						'%f', // last_changep
						'%s', // range
					)
				);
			}

			// Is failed updated data in DB
			if ( false === $ret ) {
				$msg = "Stock Ticker Fatal Error: Failed to save stock data for {$symbol_to_fetch} to database!";
				self::log( $msg );
				// Release processing for next run
				self::unlock_fetch();
				// Return failed status
				return array(
					'message' => $msg,
					'symbol'  => $symbol_to_fetch,
				);
			}

			// After success update in database, report in log
			$msg = "Stock data for symbol {$symbol_to_fetch} has been updated in database.";
			self::log( $msg );
			// Set last fetched symbol
			update_option( 'stockquote_av_last', $symbol_to_fetch );
			// Release processing for next run
			self::unlock_fetch();
			// Return succes status
			return array(
				'message' => $msg,
				'symbol'  => $symbol_to_fetch,
			);

		} // END function get_alphavantage_quotes( $symbols )

		function fetch_alphavantage_feed( $symbol ) {

			self::log( "Fetching data for symbol {$symbol}..." );

			// Get defaults (for API key)
			$defaults = $this->defaults;

			// Exit if we don't have API Key
			if ( empty( $defaults['avapikey'] ) ) {
				return 'Stock Quote Fatal Error: AlphaVantage.co API key has not set';
			}

			// Define AplhaVantage API URL
			// $feed_url = 'https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&interval=5min&apikey=' . $defaults['avapikey'] . '&symbol=';
			$feed_url = 'https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&outputsize=compact&apikey=' . $defaults['avapikey'] . '&symbol=';
			$feed_url .= $symbol;

			$wparg = array(
				'timeout' => intval( $defaults['timeout'] ),
			);

			// self::log( 'Fetching data from AV: ' . $feed_url );
			$response = wp_remote_get( $feed_url, $wparg );

			// Initialize empty $json variable
			$data_arr = '';

			// If we have WP error log it and return none
			if ( is_wp_error( $response ) ) {
				return 'Stock Quote got error fetching feed from AlphaVantage.co: ' . $response->get_error_message();
			} else {
				// Get response from AV and parse it - look for error
				$json = wp_remote_retrieve_body( $response );
				$response_arr = json_decode( $json, true );
				// If we got some error from AV, log to self::log and return none
				if ( ! empty( $response_arr['Error Message'] ) ) {
					return 'Stock Quote connected to AlphaVantage.co but got error: ' . $response_arr['Error Message'];
				} else {
					// Crunch data from AlphaVantage for symbol and prepare compact array
					self::log( "We got data from AlphaVantage for $symbol, so now let we crunch them and save to database..." );

					// Get basics
					// $ticker_symbol      = $response_arr['Meta Data']['2. Symbol']; // We don't use this at the moment, but requested symbol
					$last_trade_refresh = $response_arr['Meta Data']['3. Last Refreshed'];
					$last_trade_tz      = $response_arr['Meta Data']['5. Time Zone']; // TIME_SERIES_DAILY
					// $last_trade_tz      = $response_arr['Meta Data']['6. Time Zone']; // TIME_SERIES_INTRADAY

					// Get prices
					$i = 0;

					// foreach ( $response_arr['Time Series (5min)'] as $key => $val ) { // TIME_SERIES_INTRADAY
					foreach ( $response_arr['Time Series (Daily)'] as $key => $val ) { // TIME_SERIES_DAILY
						switch ( $i ) {
							case 0:
								$last_trade_date = $key;
								$last_trade = $val;
								break;
							case 1:
								$prev_trade_date = $key;
								$prev_trade = $val;
								break;
							case 2: // Workaround for inconsistent data
								$prev_trade_2_date = $key;
								$prev_trade_2 = $val;
								break;
							case 3: // Workaround for weekend data (currencies)
								$prev_trade_3_date = $key;
								$prev_trade_3 = $val;
								break;
							default:
								continue;
						}
						++$i;
					}

					$last_open   = $last_trade['1. open'];
					$last_high   = $last_trade['2. high'];
					$last_low    = $last_trade['3. low'];
					$last_close  = $last_trade['4. close'];
					$last_volume = (int) $last_trade['5. volume'];

					$prev_open   = $prev_trade['1. open'];
					$prev_high   = $prev_trade['2. high'];
					$prev_low    = $prev_trade['3. low'];
					$prev_close  = $prev_trade['4. close'];
					$prev_volume = (int) $prev_trade['5. volume'];

					// Try fallback for previous data if AV return zero for second day
					if ( '0.0000' == $prev_open ) {
						$prev_open   = $prev_trade_2['1. open'];
						// 3rd day (weekend)
						if ( '0.0000' == $prev_open ) {
							$prev_open   = $prev_trade_3['1. open'];
						}
					}
					if ( '0.0000' == $prev_high ) {
						$prev_high   = $prev_trade_2['2. high'];
						// 3rd day (weekend)
						if ( '0.0000' == $prev_high ) {
							$prev_high   = $prev_trade_3['2. high'];
						}
					}
					if ( '0.0000' == $prev_low ) {
						$prev_low    = $prev_trade_2['3. low'];
						// 3rd day (weekend)
						if ( '0.0000' == $prev_low ) {
							$prev_low    = $prev_trade_3['3. low'];
						}
					}
					if ( '0.0000' == $prev_close ) {
						$prev_close  = $prev_trade_2['4. close'];
						// 3rd day (weekend)
						if ( '0.0000' == $prev_close ) {
							$prev_close  = $prev_trade_3['4. close'];
						}
					}

					// Volume (1st day)
					if ( 0 == $last_volume ) {
						// 2nd day
						$last_volume = (int) $prev_trade['5. volume'];
						// 3rd day
						if ( 0 == $last_volume ) {
							$last_volume = (int) $prev_trade_2['5. volume'];
							// 4th day
							if ( 0 == $last_volume ) {
								$last_volume = (int) $prev_trade_3['5. volume'];
							}
						}
					}

					// The difference between 2017-09-01's close price and 2017-08-31's close price gives you the "Change" value.
					$change = $last_close - $prev_close;
					// So the gain on Friday was 25.92 (5025.92 - 5000) or 0.52% (25.92/5000 x 100%). No mystery!
					$change_p = ( $change / $prev_close ) * 100;
					// if we got INF, fake changep to 0
					if ( 'INF' == $change_p ) {
						$change_p = 0;
					}

					// The high and low prices combined give you the "Range" information
					$range = "$last_low - $last_high";

					// unset( $json );
					$data_arr = array(
						't'   => $symbol, // $ticker_symbol,
						'c'   => $change,
						'cp'  => $change_p,
						'l'   => $last_close,
						'lt'  => $last_trade_refresh,
						'ltz' => $last_trade_tz,
						'r'   => $range,
						'o'   => $last_open,
						'h'   => $last_high,
						'low' => $last_low,
						'v'   => $last_volume,
					);
					$data_arr['raw'] = $json;

				}
				unset( $response_arr );
			}

			return $data_arr;

		} // END function fetch_alphavantage_feed( $symbol )

		private function lock_fetch() {
			update_option( 'stockquote_av_progress', true );
			return;
		}
		private function unlock_fetch() {
			update_option( 'stockquote_av_progress', false );
			return;
		}

		/**
		 * Allow only numbers, alphabet, comma, dot, semicolon, equal and carret
		 * @param  string $symbols Unfiltered value of stock symbols
		 * @return string          Sanitized value of stock symbols
		 */
		public function sanitize_symbols( $symbols ) {
			$symbols = preg_replace( '/[^0-9A-Z\=\.\,\:\^]+/', '', strtoupper( $symbols ) );
			return $symbols;
		} // END public function sanitize_symbols( $symbols )

		/**
		 * Append to All Symbols array currently displayed symbol
		 * @param string $symbol Single symbol
		 */
		public function add_to_all_symbols( $symbol ) {
			global $wpau_stockquote_settings;
			$symbol = $this->sanitize_symbols( $symbol );
			if ( ! empty( $symbol ) ) {
				$all_symbols = $this->defaults['all_symbols'];
				$all_symbols_arr = explode( ',', $all_symbols );
				if ( ! in_array( $symbol, $all_symbols_arr ) ) {

					$all_symbols_arr[] = $symbol;
					$this->defaults['all_symbols'] = join( ',', $all_symbols_arr );
					update_option( $this->plugin_option, $this->defaults );
				}
			}
		} // END public function add_to_all_symbols( $symbol )

		public static function log( $str ) {
			// Only if WP_DEBUG is enabled
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
				$log_file = trailingslashit( WP_CONTENT_DIR ) . 'stockquote.log';
				$date = date( 'c' );
				error_log( "{$date}: {$str}\n", 3, $log_file );
			}
		}

	} // END class Wpau_Stock_Quote

} // END if(!class_exists('Wpau_Stock_Quote'))

if ( class_exists( 'Wpau_Stock_Quote' ) ) {
	// Instantiate the plugin class.
	global $wpau_stockquote;
	if ( empty( $wpau_stockquote ) ) {
		$wpau_stockquote = new Wpau_Stock_Quote();
	}
} // END class_exists( 'Wpau_Stock_Quote' )
