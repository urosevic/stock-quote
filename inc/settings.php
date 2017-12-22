<?php
/**
 * Stock Quote General Settings
 *
 * @category Wpau_Stock_Quote_Settings
 * @package Stock Quote
 * @author Aleksandar Urosevic
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link https://urosevic.net
 */

if ( ! class_exists( 'Wpau_Stock_Quote_Settings' ) ) {

	/**
	 * Wpau_Stock_Quote_Settings Class provide general plugins settings page
	 *
	 * @category Class
	 * @package Stock Quote
	 * @author Aleksandar Urosevic
	 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
	 * @link https://urosevic.net
	 */
	class Wpau_Stock_Quote_Settings {
		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			global $wpau_stockquote;

			// Get default values.
			$this->slug = $wpau_stockquote->plugin_slug;
			$this->option_name = $wpau_stockquote->plugin_option;
			$this->defaults = $wpau_stockquote->defaults; // get_option( $this->option_name );

			// Register actions.
			add_action( 'admin_init', array( &$this, 'register_settings' ) );
			add_action( 'admin_menu', array( &$this, 'add_menu' ) );
		} // END public function __construct

		/**
		 * hook into WP's register_settings action hook
		 */
		public function register_settings() {
			global $wpau_stockquote;

			// Add general settings section.
			add_settings_section(
				'wpausq_general',
				__( 'General', 'wpausq' ),
				array( &$this, 'settings_general_section_description' ),
				$wpau_stockquote->plugin_slug
			);

			// Add setting's fields.
			add_settings_field(
				$this->option_name . 'avapikey',
				__( 'AlphaVantage.co API Key', 'wpausq' ),
				array( &$this, 'settings_field_input_password' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_general',
				array(
					'field'       => $this->option_name . '[avapikey]',
					'description' => sprintf(
						wp_kses(
							__( 'To get stock data we use AlphaVantage.co API. If you do not have it already, <a href="%1$s" target="_blank">%2$s</a> and enter it here.', 'wpausq' ),
							array(
								'a' => array(
									'href' => array(),
									'target' => array( '_blank' ),
								),
							)
						),
						esc_url( 'https://www.alphavantage.co/support/#api-key' ),
						__( 'Claim your free API Key', 'wpausq' )
					),
					'class'       => 'widefat',
					'value'       => $this->defaults['avapikey'],
				)
			);
			add_settings_field(
				$this->option_name . 'all_symbols',
				__( 'All Stock Symbols', 'wpausq' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_general',
				array(
					'field'       => $this->option_name . '[all_symbols]',
					'description' => __( 'You can use some or all of those symbils in any ticker on website. Please note, you have to define which symbols you will use per widget/shortcode. Enter stock symbols separated with comma.', 'wpausq' ),
					'class'       => 'widefat',
					'value'       => $this->defaults['all_symbols'],
				)
			);
			add_settings_field(
				$this->option_name . 'loading_message',
				__( 'Loading Message', 'wpausq' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_general',
				array(
					'field'       => $this->option_name . '[loading_message]',
					'description' => __( 'Customize message displayed to visitor until plugin load stock data through AJAX.', 'wpausq' ),
					'class'       => 'widefat',
					'value'       => isset( $this->defaults['loading_message'] ) ? $this->defaults['loading_message'] : '',
				)
			);
			// Default error message.
			add_settings_field(
				$this->option_name . 'error_message',
				__( 'Error Message', 'wpausq' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_general',
				array(
					'field'       => $this->option_name . '[error_message]',
					'description' => __(
						'When we do not have pre-fetched stock data for symbols requested in block from AlphaVantage.co, display this message instead.',
						'wpausq'
					),
					'class'       => 'widefat',
					'value'       => $this->defaults['error_message'],
				)
			);
			// Force fetch stock
			add_settings_field(
				$this->option_name . 'force_fetch',
				__( 'Force data fetch', 'wpausq' ),
				array( &$this, 'settings_js_forcedatafetch' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_general'
			);

			// --- Register setting General so $_POST handling is done ---
			register_setting(
				'wpausq_general',
				$this->option_name,
				array( &$this, 'sanitize_options' )
			);

			// Add default settings section.
			add_settings_section(
				'wpausq_default',
				__( 'Default', 'wpausq' ),
				array( &$this, 'settings_default_section_description' ),
				$wpau_stockquote->plugin_slug
			);
			// Add setting's fields.
			add_settings_field(
				$this->option_name . 'symbol',
				__( 'Stock Symbols', 'wpausq' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_default',
				array(
					'field'       => $this->option_name . '[symbol]',
					'description' => __( 'Enter default stock symbol', 'wpausq' ),
					'class'       => 'small-text',
					'value'       => $this->defaults['symbol'],
				)
			);
			add_settings_field(
				$this->option_name . 'show',
				__( 'Show Company as', 'wpausq' ),
				array( &$this, 'settings_field_select' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_default',
				array(
					'field'       => $this->option_name . '[show]',
					'description' => __( 'What to show as Company identifier by default', 'wpausq' ),
					'items'       => array(
						'name'   => __( 'Company Name', 'wpausq' ),
						'symbol' => __( 'Stock Symbol', 'wpausq' ),
					),
					'value' => $this->defaults['show'],
				)
			);
			add_settings_field(
				$this->option_name . 'number_format',
				__( 'Number format', 'wpausq' ),
				array( &$this, 'settings_field_select' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_default',
				array(
					'field'       => $this->option_name . '[number_format]',
					'description' => __( 'Select default number format', 'wpausq' ),
					'items'       => array(
						'cd' => '0,000.00',
						'dc' => '0.000,00',
						'sd' => '0 000.00',
						'sc' => '0 000,00',
					),
					'value' => isset( $this->defaults['number_format'] ) ? $this->defaults['number_format'] : 'dc',
				)
			);
			add_settings_field(
				$this->option_name . 'decimals',
				__( 'Decimal places', 'wpausq' ),
				array( &$this, 'settings_field_select' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_default',
				array(
					'field'       => $this->option_name . '[decimals]',
					'description' => __( 'Select amount of decimal places for numbers', 'wpausq' ),
					'items'       => array(
						'1' => __( 'One', 'wpausq' ),
						'2' => __( 'Two', 'wpausq' ),
						'3' => __( 'Three', 'wpausq' ),
						'4' => __( 'Four', 'wpausq' ),
					),
					'value' => isset( $this->defaults['decimals'] ) ? intval( $this->defaults['decimals'] ) : 2,
				)
			);
			// Color pickers.
			// Unchanged.
			add_settings_field(
				$this->option_name . 'quote_zero',
				__( 'Unchanged Quote', 'wpausq' ),
				array( &$this, 'settings_field_colour_picker' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_default',
				array(
					'field'       => $this->option_name . '[zero]',
					'description' => __( 'Set colour for unchanged quote', 'wpausq' ),
					'value'       => $this->defaults['zero'],
				)
			);
			// Minus.
			add_settings_field(
				$this->option_name . 'quote_minus',
				__( 'Netagive Change', 'wpausq' ),
				array( &$this, 'settings_field_colour_picker' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_default',
				array(
					'field'       => $this->option_name . '[minus]',
					'description' => __( 'Set colour for negative change', 'wpausq' ),
					'value'       => $this->defaults['minus'],
				)
			);
			// Plus.
			add_settings_field(
				$this->option_name . 'quote_plus',
				__( 'Positive Change', 'wpausq' ),
				array( &$this, 'settings_field_colour_picker' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_default',
				array(
					'field'       => $this->option_name . '[plus]',
					'description' => __( 'Set colour for positive change', 'wpausq' ),
					'value'       => $this->defaults['plus'],
				)
			);

			// --- Register setting Default so $_POST handling is done ---
			register_setting(
				'wpausq_default',
				$this->option_name,
				array( &$this, 'sanitize_options' )
			);

			// Add advanced settings section.
			add_settings_section(
				'wpausq_advanced',
				__( 'Advanced', 'wpausq' ),
				array( &$this, 'settings_advanced_section_description' ),
				$wpau_stockquote->plugin_slug
			);
			// Add setting's fields.
			// Custom name.
			add_settings_field(
				$this->option_name . 'legend',
				__( 'Custom Names', 'wpausq' ),
				array( &$this, 'settings_field_textarea' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_advanced',
				array(
					'field'       => $this->option_name . '[legend]',
					'class'       => 'widefat',
					'value'       => $this->defaults['legend'],
					'rows'        => 7,
					'description' => __( 'Define custom names for symbols. Single symbol per row in format EXCHANGE:SYMBOL;CUSTOM_NAME', 'wpausq' ),
				)
			);
			// Caching timeout field.
			add_settings_field(
				$this->option_name . 'cache_timeout',
				__( 'Cache Timeout', 'wpausq' ),
				array( &$this, 'settings_field_input_number' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_advanced',
				array(
					'field'       => $this->option_name . '[cache_timeout]',
					'description' => __( 'Define cache timeout for single quote set, in seconds', 'wpausq' ),
					'class'       => 'num small-text',
					'value'       => isset( $this->defaults['cache_timeout'] ) ? $this->defaults['cache_timeout'] : 180,
					'min'         => 0,
					'max'         => DAY_IN_SECONDS,
					'step'        => 5,
				)
			);
			// Fetching timeout field.
			add_settings_field(
				$this->option_name . 'timeout',
				__( 'Fetch Timeout', 'wpausq' ),
				array( &$this, 'settings_field_input_number' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_advanced',
				array(
					'field'       => $this->option_name . '[timeout]',
					'description' => __( 'Define timeout to fetch quote feed before give up and display error message, in seconds (default is 2)', 'wpausq' ),
					'class'       => 'num small-text',
					'value'       => isset( $this->defaults['timeout'] ) ? $this->defaults['timeout'] : 2,
					'min'         => 1,
					'max'         => 60,
					'step'        => 1,
				)
			);

			// Default styling.
			add_settings_field(
				$this->option_name . 'style',
				__( 'Custom Style', 'wpausq' ),
				array( &$this, 'settings_field_textarea' ),
				$wpau_stockquote->plugin_slug,
				'wpausq_advanced',
				array(
					'field'       => $this->option_name . '[style]',
					'class'       => 'widefat',
					'rows'        => 2,
					'value'       => $this->defaults['style'],
					'description' => __( 'Define custom CSS style for quote item (font family, size, weight)', 'wpausq' ),
				)
			);

			// --- Register setting Advanced so $_POST handling is done ---
			register_setting(
				'wpausq_advanced',
				$this->option_name,
				array( &$this, 'sanitize_options' )
			);

		} // END public static function register_settings()


		public function settings_js_forcedatafetch() {
			?>
			<p class="description">After you update settings, you can force initial stock data fetching by click on button below.<br />
			If you get too much <code>[Timeout]</code> statuses during fetch, try to increase Fetch Timeout option, save settings and fetch data again.<br />
			If you get any <code>[Invalid API call]</code> for same symbol multiple times, then AlphaVantage.co does not have that symbol in TIME_SERIES_DAILY (you should remove faulty symbol from <strong>All Stock Symbols</strong>).</p>
			<button name="sq_force_data_fetch" class="button button-primary">Fetch Stock Data Now!</button> <button name="sq_force_data_fetch_stop" class="button button-secondary">Stop Fetch</button>
			<div class="sq_force_data_fetch"></div>
			<?php
		}

		public function settings_general_section_description() {
			// Think of this as help text for the section.
			esc_attr_e(
				'Predefine general settings for Stock Quote. Here you can set API key and symbols used on whole website (in all ticker).',
				'wpausq'
			);
		}
		public function settings_default_section_description() {
			// Think of this as help text for the section.
			esc_attr_e(
				'Predefine default settings for Stock Quote. Here you can set stock symbols and how you wish to present companies in page.',
				'wpausq'
			);
		}
		public function settings_advanced_section_description() {
			// Think of this as help text for the section.
			esc_attr_e(
				'Set advanced options important for caching quote feeds.',
				'wpausq'
			);
		}

		/**
		 * This function provides text inputs for settings fields
		 * @param  array $args Array of field options
		 */
		public function settings_field_input_text( $args ) {
			extract( $args );
			printf(
				'<input type="text" name="%s" id="%s" value="%s" class="%s" /><p class="description">%s</p>',
				$field,
				$field,
				$value,
				$class,
				$description
			);
		} // END public function settings_field_input_text($args)

		/**
		 * This function provides password inputs for settings fields
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_input_password( $args ) {
			printf(
				'<input type="password" name="%s" id="%s" value="%s" class="%s" /><p class="description">%s</p>',
				esc_attr( $args['field'] ),
				esc_attr( $args['field'] ),
				esc_attr( $args['value'] ),
				sanitize_html_class( $args['class'] ),
				$args['description']
			);
		} // END public function settings_field_input_text($args)

		/**
		 * This function provides number inputs for settings fields
		 */
		public function settings_field_input_number( $args ) {
			extract( $args );
			printf(
				'<input type="number" name="%1$s" id="%2$s" value="%3$s" class="%4$s" min="%5$s" max="%6$s" step="%7$s" /><p class="description">%8$s</p>',
				$field, // name
				$field, // id
				$value, // value
				$class, // class
				$min, // min
				$max, // max
				$step, // step
				$description // description
			);
		} // END public function settings_field_input_number($args)

		/**
		 * This function provides checkbox for settings fields
		 */
		public function settings_field_checkbox( $args ) {
			extract( $args );
			$checked = ( ! empty( $args['value'] ) ) ? 'checked="checked"' : '';
			printf(
				'<label for="%s"><input type="checkbox" name="%s" id="%s" value="1" class="%s" %s />%s</label>',
				$field,
				$field,
				$field,
				$class,
				$checked,
				$description
			);
		} // END public function settings_field_checkbox($args)

		/**
		 * This function provides textarea for settings fields
		 */
		public function settings_field_textarea( $args ) {
			extract( $args );
			if ( empty( $rows ) ) {
				$rows = 2;
			}
			printf(
				'<textarea name="%s" id="%s" rows="%s" class="%s">%s</textarea><p class="description">%s</p>',
				$field,
				$field,
				$rows,
				$class,
				$value,
				$description
			);
		} // END public function settings_field_textarea($args)

		/**
		 * This function provides select for settings fields
		 */
		public function settings_field_select( $args ) {
			extract( $args );
			$html = sprintf( '<select id="%s" name="%s">', $field, $field );
			foreach ( $items as $key => $val ) {
				$selected = ( $value == $key ) ? 'selected="selected"' : '';
				$html .= sprintf( '<option %s value="%s">%s</option>', $selected, $key, $val );
			}
			$html .= sprintf( '</select><p class="description">%s</p>', $description );
			echo $html;
		} // END public function settings_field_select($args)

		public function settings_field_colour_picker( $args ) {
			extract( $args );
			$html = sprintf( '<input type="text" name="%s" id="%s" value="%s" class="wpau-color-field" />', $field, $field, $value );
			$html .= ( ! empty( $description ) ) ? ' <p class="description">' . $description . '</p>' : '';
			echo $html;
		} // END public function settings_field_colour_picker($args)


		/**
		 * Sanitize settings options
		 * @param  array $input Array of option values entered on settings page.
		 * @return array        Sanitized settings values
		 */
		public function sanitize_options( $options ) {

			$sanitized = get_option( $this->option_name );
			$previous_options = $sanitized;

			// If there is no POST option_page keyword, return initial plugin options
			if ( empty( $_POST['option_page'] ) ) {
				return $sanitized;
			}

			global $wpau_stockquote;
			foreach ( $options as $key => $value ) {
				switch ( $key ) {
					case 'avapikey':
						// Allow only numbers (0-9) and English uppercase letters (A-Z)
						$value = preg_replace( '/[^0-9A-Z]+/', '', $value );
						break;
					case 'symbols':
						// Always uppercase
						// $value = self::sanitize_symbols( $value );
						$value = $wpau_stockquote->sanitize_symbols( $value );
						$value = self::alpha_symbols( $value, 'symbols' );
						break;
					case 'all_symbols':
						// Always uppercase
						// $value = self::sanitize_symbols( $value );
						$value = $wpau_stockquote->sanitize_symbols( $value );
						$value = self::alpha_symbols( $value, 'all_symbols' );
						// Add error if there is not supported exchanges
						// add_settings_error( 'all_symbols', 'all_symbols', 'You have unsupported exchange markets in All Symbols. Please remove them!', 'error' );
						break;
					case 'legend':
					case 'loading_message':
					case 'error_message':
					case 'style':
						$value = strip_tags( stripslashes( $value ) );
						break;
					case 'zero':
					case 'minus':
					case 'plus':
						$value = preg_replace( '/\#[^0-9a-f]/i', '', $value );
						break;
					case 'show':
						$value = strip_tags( stripslashes( $value ) );
						if ( ! in_array( $value, array( 'name', 'symbol' ) ) ) {
							$value = 'name';
						}
						break;
					case 'template':
						$value = strip_tags( $value, '<span><em><strong>' );
						break;
					// case 'cache_timeout':
					// 	$value = (int) $value;
					// 	$value = ! empty( $value ) ? $value : 180;
					// 	break;
					case 'fetch_timeout':
					case 'timeout':
						$value = (int) $value;
						$value = ! empty( $value ) ? $value : 2;
						break;
					case 'refresh_timeout':
						$value = (int) $value;
						$value = ! empty( $value ) ? $value : 5 * MINUTE_IN_SECONDS;
						break;
					case 'speed':
						$value = (int) $value;
						$value = ! empty( $value ) ? $value : 50;
						break;
					case 'refresh':
						$value = ! empty( $value ) ? true : false;
						break;
					case 'decimals':
						$value = (int) $value;
						$value = ! empty( $value ) ? $value : 2;
						break;
					case 'number_format':
						$value = strip_tags( stripslashes( $value ) );
						if ( ! in_array( $value, array( 'dc','sd','sc','cd' ) ) ) {
							$value = 'dc';
						}
						break;
				}
				$sanitized[ $key ] = $value;
			}

			// Clear transient but only if changed one of:
			// API key, All Stock Symbols, Cache Timeout or Fetch Timeout
			// @TODO remove cache_timeout
			if (
				$previous_options['avapikey'] !== $sanitized['avapikey'] ||
				$previous_options['all_symbols'] !== $sanitized['all_symbols'] ||
				$previous_options['cache_timeout'] !== $sanitized['cache_timeout'] ||
				$previous_options['timeout'] !== $sanitized['timeout']
			) {
				Wpau_Stock_Quote::log( 'Stock Quote: Restarting data fetching from first symbol' );
				Wpau_Stock_Quote::restart_av_fetching();
			}

			Wpau_Stock_Quote::log( 'Stock Quote: Settings have been updated' );
			return $sanitized;
		} // END public function sanitize_options($sanitized) {

		/**
		 * Add a menu
		 */
		public function add_menu() {
			global $wpau_stockquote;
			// Add a page to manage this plugin's settings
			add_options_page(
				__( 'Stock Quote Settings', 'wpausq' ),
				__( 'Stock Quote', 'wpausq' ),
				'manage_options',
				$wpau_stockquote->plugin_slug,
				array( &$this, 'plugin_settings_page' )
			);
		} // END public function add_menu()

		/**
		 * Menu Callback
		 */
		public function plugin_settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			// Render the settings template.
			include( sprintf( '%s/../templates/settings.php', dirname( __FILE__ ) ) );
		} // END public function plugin_settings_page()

		/**
		 * Strip unsupported stock symbols and throw message with list of removed symbols
		 * @param  string $symbols All stock symbols
		 * @param  string $control Name of field where symbols goes
		 * @return string          Only symbols supported by AlphaVantage.co
		 */
		private function alpha_symbols( $symbols, $control ) {
			$symbols_supported = array();
			$symbols_removed = array();
			$symbols_arr = explode( ',', $symbols );
			// Remove unsupported stock exchanges from global array to prevent API errors
			foreach ( $symbols_arr as $symbol_pos => $symbol_to_check ) {
				// If there is semicolon, it's symbol with exchange
				if ( strpos( $symbol_to_check, ':' ) ) {
					// Explode symbol so we can get exchange code
					$symbol_exchange = explode( ':', $symbol_to_check );
					// If exchange code is supported, add symbol to query array
					if ( ! empty( Wpau_Stock_Quote::$exchanges[ strtoupper( trim( $symbol_exchange[0] ) ) ] ) ) {
						$symbols_supported[] = $symbol_to_check;
					} else {
						$symbols_removed[] = $symbol_to_check;
					}
				} else {
					// Add symbol w/o exchange to query array
					$symbols_supported[] = $symbol_to_check;
				}
			}
			// Set back supported symbols
			$symbols = join( ',', $symbols_supported );
			// If we have removed symbols, add settings error message
			if ( ! empty( $symbols_removed ) ) {
				$symbols_removed_str = join( ', ', $symbols_removed );
				$opt_name = 'all_symbols' == $control ? 'All Stock Symbols' : 'Stock Symbols';
				add_settings_error(
					$control,
					$control,
					sprintf(
						'Field %1$s had symbols from unsupported exchange markets, so we removed them: %2$s',
						$opt_name,
						$symbols_removed_str
					),
					'updated'
				);
			}
			return $symbols;
		} // END private function alpha_symbols( $symbols, $control )

	} // END class Wpau_Stock_Quote_Settings
} // END if(!class_exists('Wpau_Stock_Quote_Settings'))
