<?php
/**
Plugin Name: Stock Quote
Plugin URI: https://urosevic.net/wordpress/plugins/stock-quote/
Description: Quick and easy insert static inline stock information for specific exchange symbol by customizable shortcode.
Version: 0.1.7.1
Author: Aleksandar Urosevic
Author URI: https://urosevic.net
License: GNU GPL3
Textdomain: stock-quote
 * @package  Stock Quote
 */

/**
 * Copyright 2015-2017 Aleksandar Urosevic (urke.kg@gmail.com)
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
 * Google Finance Disclaimer <https://www.google.com/intl/en-US/googlefinance/disclaimer/#disclaimers>
 *
 * Data for Stock Quote has provided by Google Finance and per their disclaimer,
 * it can only be used at a noncommercial level. Please also note that Google
 * has stated Finance API as deprecated and has no exact shutdown date.
 *
 * Data is provided by financial exchanges and may be delayed as specified
 * by financial exchanges or our data providers. Google does not verify any
 * data and disclaims any obligation to do so.
 *
 * Google, its data or content providers, the financial exchanges and
 * each of their affiliates and business partners (A) expressly disclaim
 * the accuracy, adequacy, or completeness of any data and (B) shall not be
 * liable for any errors, omissions or other defects in, delays or
 * interruptions in such data, or for any actions taken in reliance thereon.
 * Neither Google nor any of our information providers will be liable for
 * any damages relating to your use of the information provided herein.
 * As used here, “business partners” does not refer to an agency, partnership,
 * or joint venture relationship between Google and any such parties.
 *
 * You agree not to copy, modify, reformat, download, store, reproduce,
 * reprocess, transmit or redistribute any data or information found herein
 * or use any such data or information in a commercial enterprise without
 * obtaining prior written consent. All data and information is provided “as is”
 * for personal informational purposes only, and is not intended for trading
 * purposes or advice. Please consult your broker or financial representative
 * to verify pricing before executing any trade.
 *
 * Either Google or its third party data or content providers have exclusive
 * proprietary rights in the data and information provided.
 *
 * Please find all listed exchanges and indices covered by Google along with
 * their respective time delays from the table on the left.
 *
 * Advertisements presented on Google Finance are solely the responsibility
 * of the party from whom the ad originates. Neither Google nor any of its
 * data licensors endorses or is responsible for the content of any advertisement
 * or any goods or services offered therein.
 */

define( 'WPAU_STOCK_QUOTE_VER', '0.1.7.1' );

if ( ! class_exists( 'WPAU_STOCK_QUOTE' ) ) {

	/**
	 * WPAU_STOCK_QUOTE Class provide main plugin functionality
	 *
	 * @category Class
	 * @package Stock Quote
	 * @author Aleksandar Urosevic
	 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
	 * @link https://urosevic.net
	 */
	class WPAU_STOCK_QUOTE {

		/**
		 * Global default options
		 * @var null
		 */
		public static $defaults = null;

		/**
		 * Construct the plugin object
		 */
		public function __construct() {

			// Initialize default settings.
			self::$defaults = self::defaults();

			// Installation and uninstallation hooks.
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			// Initialize Textdomain for localization
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

			// Add Settings page link to plugin actions cell.
			$plugin_file = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin_file", array( $this, 'plugin_settings_link' ) );

			// Update links in plugin row on Plugins page.
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );

			// Load colour picker scripts on plugin settings page.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Add dynamic scripts and styles to footer.
			add_action( 'wp_head', array( $this, 'wp_head' ), 999 );

			// Register stock_ticker shortcode.
			add_shortcode( 'stock_quote', array( $this, 'shortcode' ) );

			// Initialize Settings.
			require_once( sprintf( '%s/inc/settings.php', dirname( __FILE__ ) ) );

			new WPAU_STOCK_QUOTE_SETTINGS();
		} // END public function __construct()

		/**
		 * Activate the plugin
		 */
		public static function activate() {
			// Do nothing
		} // END public static function activate()

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate() {
			// Do nothing
		} // END public static function deactivate()

		/**
		 * Load textdomain for localization
		 */
		public static function plugins_loaded() {
			load_plugin_textdomain( 'stock-quote', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END public static function plugins_loaded() {

		/**
		 * Defaults
		 */
		public static function defaults() {
			$defaults = array(
				'symbol'        => 'AAPL',
				'show'          => 'name',
				'zero'          => '#454545',
				'minus'         => '#D8442F',
				'plus'          => '#009D59',
				'cache_timeout' => '180', // 3 minutes
				'error_message' => 'Unfortunately, we could not get stock quote %symbol% this time.',
				'legend'        => "AAPL;Apple Inc.\nFB;Facebook, Inc.\nCSCO;Cisco Systems, Inc.\nGOOG;Google Inc.\nINTC;Intel Corporation\nLNKD;LinkedIn Corporation\nMSFT;Microsoft Corporation\nTWTR;Twitter, Inc.\nBABA;Alibaba Group Holding Limited\nIBM;International Business Machines Corporation\n.DJI;Dow Jones Industrial Average\nEURGBP;Euro (€) ⇨ British Pound Sterling (£)",
				'style'         => '',
				'timeout'       => 2,
				'number_format' => 'dc',
				'decimals'      => 2,
			);
			$options = wp_parse_args( get_option( 'stock_quote_defaults' ), $defaults );
			return $options;
		} // END public static function defaults()

		/**
		 * Add the settings link to the plugins page
		 * @param  array $links Array of existing action links for plugin row.
		 * @return array        Modified array with link to Settings page for plugin row
		 */
		public static function plugin_settings_link( $links ) {
			$settings_link = sprintf(
				'<a href="options-general.php?page=wpau_stock_quote">%s</a>',
				__( 'Settings' )
			);
			array_unshift( $links, $settings_link );
			return $links;
		} // END public static function plugin_settings_link()

		/**
		 * Add link to official plugin pages
		 * @param array  $links Array of existing plugin row links.
		 * @param string $file  Path of current plugin file.
		 * @return array        Array of updated plugin row links
		 */
		public static function add_plugin_meta_links( $links, $file ) {
			if ( 'stock-ticker/stock-ticker.php' === $file ) {
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
		} // END public static function add_plugin_meta_links()

		/**
		 * Enqueue the admin style.
		 */
		function enqueue_admin_scripts( $hook ) {
			if ( 'settings_page_wpau_stock_quote' == $hook ) {
				wp_enqueue_style(
					'stock-quote-admin',
					plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
					array(),
					WPAU_STOCK_QUOTE_VER
				);
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
			}
		} // END function enqueue_admin_scripts()

		/**
		 * Output custom styling
		 */
		public static function wp_head() {

			// Start with default CSS style.
			$css = '.widget .stock_quote{margin:0;padding:0}.stock_quote.sqitem.error{font-weight:bold}.stock_quote.sqitem.minus::before,.stock_quote.sqitem.plus::before{display:inline-block;margin-right:2px;content:"";width:10px;height:14px;background:url(data:image/gif;base64,R0lGODlhFAAKAKIHAMwzAACZAABmAJkAAMyIcsz/zDOZM////yH5BAEAAAcALAAAAAAUAAoAQAM3eHol+2TIp0oIjhIAxrJXGBjZsXUUOJbK6aUXe3LcW62UWT/qRUI7GGYxowUZMY3RVhE4cyZmAgA7) no-repeat}.stock_quote.sqitem.minus::before{background-position:-10px 4px}.stock_quote.sqitem.plus::before{background-position:0 4px}';

			// Append dynamic style and colours from plugin settings.
			if ( ! empty( self::$defaults['style'] ) ) {
				$css .= '.stock_quote.sqitem{' . self::$defaults['style'] . '}';
			}
			if ( ! empty( self::$defaults['zero'] ) ) {
				$css .= '.stock_quote.sqitem.zero,.stock_quote.sqitem.zero:hover{color:' . self::$defaults['zero'] . '}';
			}
			if ( ! empty( self::$defaults['minus'] ) ) {
				$css .= '.stock_quote.sqitem.minus,.stock_quote.sqitem.minus:hover{color:' . self::$defaults['minus'] . '}';
			}
			if ( ! empty( self::$defaults['plus'] ) ) {
				$css .= '.stock_quote.sqitem.plus,.stock_quote.sqitem.plus:hover{color:' . self::$defaults['plus'] . '}';
			}

			// Output generated CSS block.
			if ( ! empty( $css ) ) {
				echo '<style type="text/css">' . $css . '</style>';
			}

		} // END public static function wp_head()

		/**
		 * Generate content for quote item
		 * @param  string   $symbol        Stock symbol
		 * @param  string   $show          How to represent company (symbol or name).
		 * @param  boolean  $nolink        Should item be linked to Google Finance page?
		 * @param  string   $class         Custom class name for block.
		 * @param  integer  $decimals      Number of decimal places.
		 * @param  string   $number_format Which number format to use (dc, sc, cd, sd).
		 * @param  string   $template      Format of text for quote.
		 * @return string                  Formatted HTMLoutput.
		 */
		public static function stock_quote( $symbol = 'AAPL', $show = 'symbol', $nolink = false, $class = '', $decimals = null, $number_format = null, $template = null ) {

			if ( empty( $symbol ) ) {
				return;
			}

			// Get defaults.
			$defaults = self::$defaults;

			// Prepare number format
			if ( ! empty( $number_format ) && in_array( $number_format, array( 'dc','sd','sc','cd' ) ) ) {
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

			// Get fresh or from transient cache stock quote.
			$transient_id = 'stockquote_' . sanitize_key( $symbol ) . '_' . $defaults['cache_timeout'];

			// Get legend for company names.
			$matrix = explode( "\n", $defaults['legend'] );
			$msize = count( $matrix );
			for ( $m = 0; $m < $msize; ++$m ) {
				$line = explode( ';', $matrix[ $m ] );
				$legend[ strtoupper( trim( $line[0] ) ) ] = trim( $line[1] );
			}
			unset( $m, $msize, $matrix, $line );

			// Check if cache exists.
			if ( false === ( $json = get_transient( $transient_id ) ) || empty( $json ) || ! empty( $_GET['stockquote_purge_cache'] ) ) {

				// Log new fetch for cache if WP debugging is enabled
				if ( WP_DEBUG ) {
					error_log( "Download data for symbol {$symbol} and cache for {$defaults['cache_timeout']} seconds within transient {$transient_id}" );
				}

				// If does not exist, get new cache.
				// Clean and prepare symbol for query.
				$exc_symbol = preg_replace( '/\s+/', '', $symbol );
				// Adapt ^DIJ to .DJI symbol.
				$exc_symbol = preg_replace( '/\^/', '.', $exc_symbol );
				// Replace amp with code.
				$exc_symbol = str_replace( '&', '%26', $exc_symbol );
				// Adapt currency symbol EURGBP=X to CURRENCY:EURGBP format.
				$exc_symbol = preg_replace( '/([a-zA-Z]*)\=X/i', 'CURRENCY:$1', $exc_symbol );
				// Compose URL to call.
				$exc_url = "https://finance.google.com/finance/info?client=ig&q=$exc_symbol";

				// Set timeout.
				$wparg = array(
					'timeout' => $defaults['timeout'], // Two seconds only.
				);
				// Get stock from Google.
				$response = wp_remote_get( $exc_url, $wparg );
				if ( is_wp_error( $response ) ) {
					return $defaults['error_message'] . '<!-- Stock Quote fetch ERROR: ' . $response->get_error_message() . ' -->';
				} else {
					// Get content from response.
					$data = wp_remote_retrieve_body( $response );
					// Convert a string with ISO-8859-1 characters encoded with UTF-8 to single-byte ISO-8859-1.
					$data = utf8_decode( $data );
					// Remove newlines from content.
					$data = str_replace( "\n", '', $data );
					// Remove // from content.
					$data = trim( str_replace( '/', '', $data ) );

					// Decode data to JSON.
					$json = json_decode( $data );
					unset( $data );

					// Now cache array for N seconds.
					set_transient( $transient_id, $json, $defaults['cache_timeout'] );
				}

				// Free some memory: destroy all vars that we temporary used here.
				unset( $exc_symbol, $exc_url, $reponse );
			}

			// Prepare quote.
			$class = "stock_quote sqitem $class";

			// Process quote.
			if ( ! empty( $json ) && ! is_null( $json[0]->id ) ) {

				// Parse results and extract data to display.
				$quote = $json[0];

				// Assign object elements to vars.
				// Strip comma from numbers to make it compatible for formatting.
				$q_change  = str_replace( ',', '', $quote->c );
				$q_price   = str_replace( ',', '', $quote->l );
				$q_changep = str_replace( ',', '', $quote->cp );
				$q_name    = $quote->t; // No nicename in Google Finance so use Symbol instead.
				$q_symbol  = $quote->t;
				$q_ltrade  = $quote->lt;
				$q_exch    = $quote->e;

				// Fallback if numeric values are zero or empty
				if ( empty( $q_change ) ) {
					$q_change = (int) 0;
				}
				if ( empty( $q_price ) ) {
					$q_price = (int) 0;
				}
				if ( empty( $q_changep ) ) {
					$q_changep = (int) 0;
				}

				// Define class based on change.
				$prefix    = '';
				if ( $q_change < 0 ) {
					$class .= ' minus';
				} elseif ( $q_change > 0 ) {
					$prefix = '+';
					$class .= ' plus';
				} else {
					$class .= ' zero';
					$q_change = '0.00';
				}

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

				// Do not print change, volume and change% for currencies.
				if ( 'CURRENCY' == $q_exch ) {
					$company_show = $q_symbol == $q_name ? $q_name . '=X' : $q_name;
					$url_query    = $q_symbol;
					$quote_title  = $q_name;
				} else {
					$url_query   = $q_exch . ':' . $q_symbol;
					$quote_title = $q_name . ' (' . $q_exch . ' Last trade ' . $q_ltrade . ')';
				}

				// Format numbers.
				$q_price   = number_format( $q_price, $decimals, $dec_point, $thousands_sep );
				$q_change  = $prefix . number_format( $q_change, $decimals, $dec_point, $thousands_sep );
				$q_changep = $prefix . number_format( $q_changep, $decimals, $dec_point, $thousands_sep );

				// Text.
				// $quote_text = "$company_show $q_price $q_change ${q_changep}%";

				$template = ! empty( $template ) ? $template : '%company% %price% %change% %changep%';

				// Value template.
				$quote_text = $template;
				$quote_text = str_replace( '%company_show%', $company_show, $quote_text );
				$quote_text = str_replace( '%company%', $q_name, $quote_text );
				$quote_text = str_replace( '%exchange%', $q_exch, $quote_text );
				$quote_text = str_replace( '%exch_symbol%', $url_query, $quote_text );
				$quote_text = str_replace( '%symbol%', $symbol, $quote_text );
				$quote_text = str_replace( '%price%', $q_price, $quote_text );
				$quote_text = str_replace( '%change%', $q_change, $quote_text );
				$quote_text = str_replace( '%changep%', "{$q_changep}%", $quote_text );
				$quote_text = str_replace( '%ltrade%', $q_ltrade, $quote_text );

				// Quote w/ or w/o link.
				if ( empty( $nolink ) ) {
					$out = sprintf(
						'<a href="https://www.google.com/finance?q=%1$s" class="%2$s" target="_blank" title="%3$s">%4$s</a>',
						$url_query,   // 1
						$class,       // 2
						$quote_title, // 3
						$quote_text   // 4
					);
				} else {
					$out = sprintf(
						'<span class="%1$s" title="%2$s">%3$s</span>',
						$class,       // 1
						$quote_title, // 2
						$quote_text   // 3
					);
				}
			} else {

				// No results were returned.
				$out = sprintf(
					'<span class="stock_quote sqitem error %1$s">%2$s</span>',
					$class, // 1
					str_replace( '%symbol%', $symbol, $defaults['error_message'] ) // 2
				);

			}

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

			$defaults = self::$defaults;
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
				$symbol = strip_tags( $atts['symbol'] );
				return self::stock_quote( $symbol, $atts['show'], $atts['nolink'], $atts['class'], $atts['decimals'], $atts['number_format'], $atts['template'] );
			}

		} // END public static function shortcode()

	} // END class WPAU_STOCK_QUOTE

} // END if(!class_exists('WPAU_STOCK_QUOTE'))

// Instantiate the plugin class.
new WPAU_STOCK_QUOTE();
