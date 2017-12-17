<?php
/**
 * Stock Quote General Settings
 *
 * @category WPAU_STOCK_QUOTE_SETTINGS
 * @package Stock Quote
 * @author Aleksandar Urosevic
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link https://urosevic.net
 */
if ( ! class_exists( 'WPAU_STOCK_QUOTE_SETTINGS' ) ) {

	/**
	 * WPAU_STOCK_QUOTE_SETTINGS Class provide general plugins settings page
	 *
	 * @category Class
	 * @package Stock Quote
	 * @author Aleksandar Urosevic
	 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
	 * @link https://urosevic.net
	 */
	class WPAU_STOCK_QUOTE_SETTINGS
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			// Register actions.
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'add_menu' ) );
		} // END public function __construct

		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init() {

			// Get default values.
			$defaults = WPAU_STOCK_QUOTE::defaults();

			// Register plugin's settings.
			register_setting( 'wpausq_default_settings', 'stock_quote_defaults' );
			register_setting( 'wpausq_advanced_settings', 'stock_quote_defaults' );

			// Add general settings section.
			add_settings_section(
				'wpausq_default_settings',
				__( 'Default Settings', 'stock-quote' ),
				array( &$this, 'settings_default_section_description' ),
				'wpau_stock_quote'
			);

			// add setting's fields
			add_settings_field(
				'wpau_stock_quote-symbol',
				__( 'Stock Symbols', 'stock-quote' ),
				array( &$this, 'settings_field_input_text' ),
				'wpau_stock_quote',
				'wpausq_default_settings',
				array(
					'field'       => 'stock_quote_defaults[symbol]',
					'description' => __( 'Enter default stock symbol', 'stock-quote' ),
					'class'       => 'small-text',
					'value'       => $defaults['symbol'],
				)
			);
			add_settings_field(
				'wpau_stock_quote-show',
				__( 'Show Company as', 'stock-quote' ),
				array( &$this, 'settings_field_select' ),
				'wpau_stock_quote',
				'wpausq_default_settings',
				array(
					'field'       => 'stock_quote_defaults[show]',
					'description' => __( 'What to show as Company identifier by default', 'stock-quote' ),
					'items'       => array(
						'name'   => __( 'Company Name', 'stock-quote' ),
						'symbol' => __( 'Stock Symbol', 'stock-quote' ),
					),
					'value' => $defaults['show'],
				)
			);
			add_settings_field(
				'wpau_stock_quote-number_format',
				__( 'Number format', 'stock-quote' ),
				array( &$this, 'settings_field_select' ),
				'wpau_stock_quote',
				'wpausq_default_settings',
				array(
					'field'       => 'stock_quote_defaults[number_format]',
					'description' => __( 'Select default number format', 'stock-quote' ),
					'items'       => array(
						'cd' => '0,000.00',
						'dc' => '0.000,00',
						'sd' => '0 000.00',
						'sc' => '0 000,00',
					),
					'value' => isset( $defaults['number_format'] ) ? $defaults['number_format'] : 'dc',
				)
			);
			add_settings_field(
				'wpau_stock_quote-decimals',
				__( 'Decimal places', 'stock-quote' ),
				array( &$this, 'settings_field_select' ),
				'wpau_stock_quote',
				'wpausq_default_settings',
				array(
					'field'       => 'stock_quote_defaults[decimals]',
					'description' => __( 'Select amount of decimal places for numbers', 'stock-quote' ),
					'items'       => array(
						'1' => __( 'One', 'stock-quote' ),
						'2' => __( 'Two', 'stock-quote' ),
						'3' => __( 'Three', 'stock-quote' ),
						'4' => __( 'Four', 'stock-quote' ),
					),
					'value' => isset( $defaults['decimals'] ) ? intval( $defaults['decimals'] ) : 2,
				)
			);
			// Color pickers
			add_settings_field( // unchanged
				'wpau_stock_quote-quote_zero',
				__( 'Unchanged Quote', 'stock-quote' ),
				array( &$this, 'settings_field_colour_picker' ),
				'wpau_stock_quote',
				'wpausq_default_settings',
				array(
					'field'       => 'stock_quote_defaults[zero]',
					'description' => __( 'Set colour for unchanged quote', 'stock-quote' ),
					'value'       => $defaults['zero'],
				)
			);
			add_settings_field( // minus
				'wpau_stock_quote-quote_minus',
				__( 'Netagive Change', 'stock-quote' ),
				array( &$this, 'settings_field_colour_picker' ),
				'wpau_stock_quote',
				'wpausq_default_settings',
				array(
					'field'       => 'stock_quote_defaults[minus]',
					'description' => __( 'Set colour for negative change', 'stock-quote' ),
					'value'       => $defaults['minus'],
				)
			);
			add_settings_field( // plus
				'wpau_stock_quote-quote_plus',
				__( 'Positive Change', 'stock-quote' ),
				array( &$this, 'settings_field_colour_picker' ),
				'wpau_stock_quote',
				'wpausq_default_settings',
				array(
					'field'       => 'stock_quote_defaults[plus]',
					'description' => __( 'Set colour for positive change', 'stock-quote' ),
					'value'       => $defaults['plus'],
				)
			);

			// add advanced settings section
			add_settings_section(
				'wpausq_advanced_settings',
				__( 'Advanced Settings', 'stock-quote' ),
				array( &$this, 'settings_advanced_section_description' ),
				'wpau_stock_quote'
			);
			// add setting's fields
			// custom name
			add_settings_field(
				'wpau_stock_quote-legend',
				__( 'Custom Names', 'stock-quote' ),
				array( &$this, 'settings_field_textarea' ),
				'wpau_stock_quote',
				'wpausq_advanced_settings',
				array(
					'field'       => 'stock_quote_defaults[legend]',
					'class'       => 'widefat',
					'value'       => $defaults['legend'],
					'rows'        => 7,
					'description' => __( 'Define custom names for symbols. Single symbol per row in format EXCHANGE:SYMBOL;CUSTOM_NAME', 'stock-quote' ),
				)
			);
			// caching timeout field
			add_settings_field(
				'wpau_stock_quote-cache_timeout',
				__( 'Cache Timeout', 'stock-quote' ),
				array( &$this, 'settings_field_input_number' ),
				'wpau_stock_quote',
				'wpausq_advanced_settings',
				array(
					'field'       => 'stock_quote_defaults[cache_timeout]',
					'description' => __( 'Define cache timeout for single quote set, in seconds', 'stock-quote' ),
					'class'       => 'num',
					'value'       => $defaults['cache_timeout'],
					'min'         => 0,
					'max'         => DAY_IN_SECONDS,
					'step'        => 5,
				)
			);
			// fetching timeout field
			add_settings_field(
				'wpau_stock_quote-timeout',
				__( 'Fetch Timeout', 'stock-quote' ),
				array( &$this, 'settings_field_input_number' ),
				'wpau_stock_quote',
				'wpausq_advanced_settings',
				array(
					'field'       => 'stock_quote_defaults[timeout]',
					'description' => __( 'Define timeout to fetch quote feed before give up and display error message, in seconds (default is 2)', 'stock-quote' ),
					'class'       => 'num',
					'value'       => $defaults['timeout'],
					'min'         => 1,
					'max'         => 60,
					'step'        => 1,
				)
			);
			// default error message
			add_settings_field(
				'wpau_stock_quote-error_message',
				__( 'Error Message', 'stock-quote' ),
				array( &$this, 'settings_field_input_text' ),
				'wpau_stock_quote',
				'wpausq_advanced_settings',
				array(
					'field'       => 'stock_quote_defaults[error_message]',
					'description' => __( 'When Stock Quote fail to grab quote set from Google Finance, display this mesage instead. Use macro <em>%symbol%</em> to insert requested symbol.', 'stock-quote' ),
					'class'       => 'widefat',
					'value'       => $defaults['error_message'],
				)
			);

			// default styling
			add_settings_field(
				'wpau_stock_quote-style',
				__( 'Custom Style', 'stock-quote' ),
				array( &$this, 'settings_field_textarea' ),
				'wpau_stock_quote',
				'wpausq_advanced_settings',
				array(
					'field'       => 'stock_quote_defaults[style]',
					'class'       => 'widefat',
					'rows'        => 2,
					'value'       => $defaults['style'],
					'description' => __( 'Define custom CSS style for quote item (font family, size, weight)', 'stock-quote' ),
				)
			);
			// Possibly do additional admin_init tasks.
		} // END public static function admin_init()

		public function settings_default_section_description() {
			// Think of this as help text for the section.
			echo __( 'Predefine default settings for Stock Quote. Here you can set stock symbols and how you wish to present companies in page.', 'stock-quote' );
		}
		public function settings_advanced_section_description() {
			// Think of this as help text for the section.
			echo __( 'Set advanced options important for caching quote feeds.', 'stock-quote' );
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
		 * add a menu
		 */
		public function add_menu() {
			// Add a page to manage this plugin's settings
			add_options_page(
				__( 'Stock Quote Settings', 'stock-quote' ),
				__( 'Stock Quote', 'stock-quote' ),
				'manage_options',
				'wpau_stock_quote',
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
	} // END class WPAU_STOCK_QUOTE_SETTINGS
} // END if(!class_exists('WPAU_STOCK_QUOTE_SETTINGS'))
