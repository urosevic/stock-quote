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
				'wpau_stock_quote',
				__( 'Settings', 'stock-quote' ),
				array( &$this, 'settings_section_description' ),
				$wpau_stockquote->plugin_slug
			);

			// Add setting's fields.
			// Add separator for General section
			add_settings_field(
				$this->option_name . 'general_section',
				__( 'General', 'stock-quote' ),
				array( &$this, 'settings_field_section_divider' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'description' => __( 'Predefine general settings for Stock Quote. Here you can set API key and symbols used on whole website (in all quotes).', 'stock-quote' ),
				)
			);

			// Add setting's fields.
			add_settings_field(
				$this->option_name . 'avapikey',
				__( 'AlphaVantage.co API Key', 'stock-quote' ),
				array( &$this, 'settings_field_input_password' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[avapikey]',
					'description' => sprintf(
						wp_kses(
							__( 'To get stock data we use AlphaVantage.co API. If you do not have it already, <a href="%1$s" target="_blank">%2$s</a> and enter it here.', 'stock-quote' ),
							array(
								'a' => array(
									'href' => array(),
									'target' => array( '_blank' ),
								),
							)
						),
						esc_url( 'https://www.alphavantage.co/support/#api-key' ),
						__( 'Claim your free API Key', 'stock-quote' )
					),
					'class'       => 'widefat',
					'value'       => $this->defaults['avapikey'],
				)
			);

			add_settings_field(
				$this->option_name . 'av_api_tier',
				__( 'AlphaVantage.co API Key Tier', 'stock-quote' ),
				array( &$this, 'settings_field_select' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[av_api_tier]',
					'description' => sprintf(
						wp_kses(
							__( 'Which Alpha Vantage API Key membership do you have (<a href="%1$s" target="_blank">%2$s</a> or <a href="%3$s" target="_blank">%4$s</a>)?', 'stock-quote' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array( '_blank' ),
								),
							)
						),
						esc_url( 'https://www.alphavantage.co/support/#api-key' ),
						__( 'Free', 'stock-quote' ),
						esc_url( 'https://www.alphavantage.co/premium/' ),
						__( 'Premium', 'stock-quote' )
					),
					'items'       => array(
						'5'   => __( 'Free (5 requests/min)', 'stock-quote' ),
						'15'  => __( 'Premium (15 requests/min)', 'stock-quote' ),
						'60'  => __( 'Premium (60 requests/min)', 'stock-quote' ),
						'120' => __( 'Premium (120 requests/min)', 'stock-quote' ),
						'360' => __( 'Premium (360 requests/min)', 'stock-quote' ),
						'600' => __( 'Premium (600 requests/min)', 'stock-quote' ),
					),
					'value' => $this->defaults['av_api_tier'],
				)
			);

			add_settings_field(
				$this->option_name . 'all_symbols',
				__( 'All Stock Symbols', 'stock-quote' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[all_symbols]',
					'description' => __( 'You can use any of those symbols in any quote shortcode on website. Please note, you have to define which symbol you will use per shortcode. Enter stock symbols separated with comma.', 'stock-quote' ),
					'class'       => 'widefat',
					'value'       => $this->defaults['all_symbols'],
				)
			);
			add_settings_field(
				$this->option_name . 'loading_message',
				__( 'Loading Message', 'stock-quote' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[loading_message]',
					'description' => __( 'Customize message displayed to visitor until plugin load stock data through AJAX.', 'stock-quote' ),
					'class'       => 'widefat',
					'value'       => isset( $this->defaults['loading_message'] ) ? $this->defaults['loading_message'] : '',
				)
			);
			// Default error message.
			add_settings_field(
				$this->option_name . 'error_message',
				__( 'Error Message', 'stock-quote' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[error_message]',
					'description' => __(
						'When we do not have pre-fetched stock data from AlphaVantage.co for symbol requested in stock quote block, display this message instead.',
						'stock-quote'
					),
					'class'       => 'widefat',
					'value'       => $this->defaults['error_message'],
				)
			);
			// Force fetch stock
			add_settings_field(
				$this->option_name . 'force_fetch',
				__( 'Force data fetch', 'stock-quote' ),
				array( &$this, 'settings_js_forcedatafetch' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote'
			);

			// Add setting's fields.
			// Add separator for Defaults section
			add_settings_field(
				$this->option_name . 'default_section',
				__( 'Defaults', 'stock-quote' ),
				array( &$this, 'settings_field_section_divider' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'description' => __( 'Predefine default settings for Stock Quote. Here you can set stock symbol and how you wish to present companies in page.', 'stock-quote' ),
				)
			);

			// Add setting's fields.
			add_settings_field(
				$this->option_name . 'symbol',
				__( 'Stock Symbol', 'stock-quote' ),
				array( &$this, 'settings_field_input_text' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[symbol]',
					'description' => __( 'Enter default stock symbol', 'stock-quote' ),
					'class'       => 'small-text',
					'value'       => $this->defaults['symbol'],
				)
			);
			add_settings_field(
				$this->option_name . 'show',
				__( 'Show Company as', 'stock-quote' ),
				array( &$this, 'settings_field_select' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[show]',
					'description' => __( 'What to show as Company identifier by default', 'stock-quote' ),
					'items'       => array(
						'name'   => __( 'Company Name', 'stock-quote' ),
						'symbol' => __( 'Stock Symbol', 'stock-quote' ),
					),
					'value' => $this->defaults['show'],
				)
			);
			add_settings_field(
				$this->option_name . 'number_format',
				__( 'Number format', 'stock-quote' ),
				array( &$this, 'settings_field_select' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[number_format]',
					'description' => __( 'Select default number format', 'stock-quote' ),
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
				__( 'Decimal places', 'stock-quote' ),
				array( &$this, 'settings_field_select' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[decimals]',
					'description' => __( 'Select amount of decimal places for numbers', 'stock-quote' ),
					'items'       => array(
						'1' => __( 'One', 'stock-quote' ),
						'2' => __( 'Two', 'stock-quote' ),
						'3' => __( 'Three', 'stock-quote' ),
						'4' => __( 'Four', 'stock-quote' ),
					),
					'value' => isset( $this->defaults['decimals'] ) ? intval( $this->defaults['decimals'] ) : 2,
				)
			);
			// Color pickers.
			// Unchanged.
			add_settings_field(
				$this->option_name . 'quote_zero',
				__( 'Unchanged Quote', 'stock-quote' ),
				array( &$this, 'settings_field_colour_picker' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[zero]',
					'description' => __( 'Set colour for unchanged quote', 'stock-quote' ),
					'value'       => $this->defaults['zero'],
				)
			);
			// Minus.
			add_settings_field(
				$this->option_name . 'quote_minus',
				__( 'Negative Change', 'stock-quote' ),
				array( &$this, 'settings_field_colour_picker' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[minus]',
					'description' => __( 'Set colour for negative change', 'stock-quote' ),
					'value'       => $this->defaults['minus'],
				)
			);
			// Plus.
			add_settings_field(
				$this->option_name . 'quote_plus',
				__( 'Positive Change', 'stock-quote' ),
				array( &$this, 'settings_field_colour_picker' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[plus]',
					'description' => __( 'Set colour for positive change', 'stock-quote' ),
					'value'       => $this->defaults['plus'],
				)
			);

			// Add setting's fields.
			// Add separator for Advanced section
			add_settings_field(
				$this->option_name . 'advanced_section',
				__( 'Advanced', 'stock-quote' ),
				array( &$this, 'settings_field_section_divider' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'description' => __( 'Set advanced options important for caching quote feeds.', 'stock-quote' ),
				)
			);

			// Add setting's fields.
			// Custom name.
			add_settings_field(
				$this->option_name . 'legend',
				__( 'Custom Names', 'stock-quote' ),
				array( &$this, 'settings_field_textarea' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[legend]',
					'class'       => 'widefat',
					'value'       => $this->defaults['legend'],
					'rows'        => 7,
					'description' => __( 'Define custom names for symbols. Single symbol per row in format EXCHANGE:SYMBOL;CUSTOM_NAME', 'stock-quote' ),
				)
			);
			// Caching timeout field.
			add_settings_field(
				$this->option_name . 'cache_timeout',
				__( 'Cache Timeout', 'stock-quote' ),
				array( &$this, 'settings_field_input_number' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[cache_timeout]',
					'description' => __( 'Define cache timeout for single quote set, in seconds', 'stock-quote' ),
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
				__( 'Fetch Timeout', 'stock-quote' ),
				array( &$this, 'settings_field_input_number' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[timeout]',
					'description' => __( 'Define timeout to fetch quote feed before give up and display error message, in seconds (default is 4)', 'stock-quote' ),
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
				__( 'Custom Style', 'stock-quote' ),
				array( &$this, 'settings_field_textarea' ),
				$wpau_stockquote->plugin_slug,
				'wpau_stock_quote',
				array(
					'field'       => $this->option_name . '[style]',
					'class'       => 'widefat',
					'rows'        => 2,
					'value'       => $this->defaults['style'],
					'description' => __( 'Define custom CSS style for quote item (font family, size, weight)', 'stock-quote' ),
				)
			);

			// --- Register setting Advanced so $_POST handling is done ---
			register_setting(
				'wpau_stock_quote',
				$this->option_name,
				array( &$this, 'sanitize_options' )
			);

		} // END public static function register_settings()


		public function settings_js_forcedatafetch() {
			?>
			<p class="description"><?php _e( 'After you update settings, you can force stock data fetch by click on button below.', 'stock-quote' ); ?><br />
			<?php printf( __( "Status %s is normal. It's triggeerd to prevend exceeded AlphaVantage.co API Tier timeout.", 'stock-quote' ), '<code>[WAIT]</code>' ); ?><br />
			<?php
				printf(
					__( 'Status %1$s is shown in case when AlphaVantage.co provide empty response for symbol. You should check proper format for that symbol (for example currency <strong>since Q2 2020</strong> should not end with %2$s so use %3$s instead of old format %4$s).', 'stock-quote' ),
					'<code>[SKIP]</code>',
					'<code>=X</code>',
					'<code>EURGBP</code>',
					'<code>EURGBP=X</code>'
				);
			?>
			<br />
			<?php printf( __( 'If you get too much %1$s statuses during fetch, try to increase <strong>%2$s</strong> option, save settings and fetch data again.', 'stock-quote' ), '<code>[TIMEOUT]</code>', __( 'Fetch Timeout', 'stock-quote' ) ); ?><br />
			<?php printf( __( 'If you get any %1$s status for same symbol multiple times, then AlphaVantage.co does not have that symbol in %2$s and you should remove that faulty symbol from <strong>%3$s</strong>.', 'stock-quote' ), '<code>[INVALID]</code>', '<code>GLOBAL_QUOTE</code>', __( 'All Stock Symbols', 'stock-quote' ) ); ?></p><br />
			<button name="sq_force_data_fetch" class="button button-primary"><?php _e( 'Fetch Stock Data Now!', 'stock-quote' ); ?></button> <button name="sq_force_data_fetch_stop" class="button button-secondary"><?php _e( 'Stop Fetch', 'stock-quote' ); ?></button>
			<div class="sq_force_data_fetch"></div>
			<?php
		}

		/**
		 * Print description for General section
		 */
		public function settings_section_description() {
			// Think of this as help text for the section.
			return '';
		}

		/**
		 * Print divider for section
		 * @return [type] [description]
		 */
		public function settings_field_section_divider( $args ) {
			echo '<hr />';
			if ( ! empty( $args['description'] ) ) {
				printf(
					'<p class="description">%s</p><hr />',
					$args['description']
				);
			}
		}

		/**
		 * This function provides text inputs for settings fields
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_input_text( $args ) {
			printf(
				'<input type="text" name="%s" id="%s" value="%s" class="%s" data-lpignore="true" /><p class="description">%s</p>',
				esc_attr( $args['field'] ),
				esc_attr( $args['field'] ),
				esc_attr( $args['value'] ),
				sanitize_html_class( $args['class'] ),
				esc_html( $args['description'] )
			);
		} // END public function settings_field_input_text($args)

		/**
		 * This function provides password inputs for settings fields
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_input_password( $args ) {
			printf(
				'<input type="password" name="%s" id="%s" value="%s" class="%s" data-lpignore="true" /><p class="description">%s</p>',
				esc_attr( $args['field'] ),
				esc_attr( $args['field'] ),
				esc_attr( $args['value'] ),
				sanitize_html_class( $args['class'] ),
				$args['description']
			);
		} // END public function settings_field_input_text($args)

		/**
		 * This function provides number inputs for settings fields
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_input_number( $args ) {
			$args['description'] = self::format_description( esc_html( $args['description'] ) );
			printf(
				'<input type="number" name="%1$s" id="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" class="%7$s" /><p class="description">%8$s</p>',
				esc_attr( $args['field'] ),            // 1
				esc_attr( $args['field'] ),            // 2
				(int) $args['value'],                  // 3
				(int) $args['min'],                    // 4
				(int) $args['max'],                    // 5
				(int) $args['step'],                   // 6
				sanitize_html_class( $args['class'] ), // 7
				$args['description']                   // 8
			);
		} // END public function settings_field_input_number($args)

		/**
		 * This function provides textarea for settings fields
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_textarea( $args ) {
			if ( empty( $args['rows'] ) ) {
				$args['rows'] = 7;
			}
			printf(
				'<textarea name="%s" id="%s" rows="%s" class="%s">%s</textarea><p class="description">%s</p>',
				esc_attr( $args['field'] ),
				esc_attr( $args['field'] ),
				(int) $args['rows'],
				sanitize_html_class( $args['class'] ),
				esc_textarea( $args['value'] ),
				esc_html( $args['description'] )
			);
		} // END public function settings_field_textarea($args)

		/**
		 * This function provides select for settings fields
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_select( $args ) {
			if ( empty( $args['class'] ) ) {
				$args['class'] = 'regular-text';
			}
			printf(
				'<select id="%1$s" name="%1$s" class="%2$s">',
				esc_attr( $args['field'] ),
				sanitize_html_class( $args['class'] )
			);
			foreach ( $args['items'] as $key => $val ) {
				$selected = ( $args['value'] == $key ) ? 'selected=selected' : '';
				printf(
					'<option %1$s value="%2$s">%3$s</option>',
					esc_attr( $selected ),      // 1
					sanitize_key( $key ),       // 2
					sanitize_text_field( $val ) // 3
				);
			}
			printf(
				'</select><p class="description">%s</p>',
				wp_kses(
					$args['description'],
					array(
						'a' => array(
							'href'   => array(),
							'target' => array( '_blank' ),
						),
						'strong',
						'em',
						'pre',
						'code',
					)
				)
			);
		} // END public function settings_field_select($args)

		/**
		 * This function provides checkbox for settings fields
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_checkbox( $args ) {
			$checked = ( ! empty( $args['value'] ) ) ? 'checked="checked"' : '';
			printf(
				'<label for="%1$s"><input type="checkbox" name="%1$s" id="%1$s" value="1" class="%2$s" %3$s />%4$s</label>',
				esc_attr( $args['field'] ),
				$args['class'],
				$checked,
				self::format_description( $args['description'] )
			);
		} // END public function settings_field_checkbox($args) {

		/**
		 * Generate colour picker field
		 * @param  array $args Array of field arguments.
		 */
		public function settings_field_colour_picker( $args ) {
			printf(
				'<input type="text" name="%1$s" id="%2$s" value="%3$s" class="wpau-color-field" /> <p class="description">%4$s</p>',
				esc_attr( $args['field'] ),
				esc_attr( $args['field'] ),
				esc_attr( $args['value'] ),
				esc_html( $args['description'] )
			);
		} // END public function settings_field_colour_picker($args)

		/**
		 * Basic markdown formatter for descriptions
		 * @param  string $text Raw ASCII text
		 * @return string       HTML formatted text
		 */
		function format_description( $text ) {
			$pattern = '/(\*\*)([^\*]+)(\*\*)/';
			$replacement = '<strong>${2}</strong>';
			return preg_replace( $pattern, $replacement, $text );
		} // END function format_description( $text )

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
					case 'av_api_tier':
						if ( ! in_array( $value, array( 5, 15, 60, 120, 360, 600 ) ) ) {
							$value = 5;
						}
						break;
					case 'symbol':
						// Always uppercase
						$value = Wpau_Stock_Quote::sanitize_symbols( $value );
						$value = self::alpha_symbols( $value, 'symbol' );
						break;
					case 'all_symbols':
						// Always uppercase
						$value = Wpau_Stock_Quote::sanitize_symbols( $value );
						$value = self::alpha_symbols( $value, 'all_symbols' );
						// Add error if there is not supported exchanges
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
					case 'cache_timeout':
						$value = (int) $value;
						$value = ! empty( $value ) ? $value : 180;
						break;
					case 'fetch_timeout':
					case 'timeout':
						$value = (int) $value;
						$value = ! empty( $value ) ? $value : 4;
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
						if ( ! in_array( $value, array( 'dc', 'sd', 'sc', 'cd' ) ) ) {
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
				__( 'Stock Quote Settings', 'stock-quote' ),
				__( 'Stock Quote', 'stock-quote' ),
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
				$symbol_to_check = trim( $symbol_to_check );
				// If there is semicolon, it's symbol with exchange
				if ( strpos( $symbol_to_check, ':' ) ) {
					// Explode symbol so we can get exchange code
					$symbol_exchange = explode( ':', $symbol_to_check );
					// If exchange code is supported, add symbol to query array
					if ( ! empty( Wpau_Stock_Quote::$exchanges['supported'][ strtoupper( trim( $symbol_exchange[0] ) ) ] ) ) {
						$symbols_supported[] = $symbol_to_check;
					} else {
						$symbols_removed[] = $symbol_to_check;
					}
				} else if ( ! empty( $symbol_to_check ) ) {
					// Add symbol w/o exchange to query array
					$symbols_supported[] = $symbol_to_check;
				}
			}
			// Remove duplicate symbols
			$symbols_supported = array_unique( $symbols_supported );
			// Set back supported symbols
			$symbols = join( ',', $symbols_supported );
			// If we have removed symbols, add settings error message
			if ( ! empty( $symbols_removed ) ) {
				$symbols_removed_str = join( ', ', $symbols_removed );
				$opt_name = 'all_symbols' == $control ? 'All Stock Symbols' : 'Stock Symbol';
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
