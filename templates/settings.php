<?php
/**
 * Stock Quote General Settings page template
 *
 * @category Template
 * @package Stock Quote
 * @author Aleksandar Urosevic
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link https://urosevic.net
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpau_stockquote;
?>
<div class="wrap" id="stock_quote_settings">
	<h2><?php printf( __( '%s Settings', 'stock-quote' ), $wpau_stockquote->plugin_name ); ?></h2>
	<em><?php printf( __( 'Plugin version: %s', 'stock-quote' ), $wpau_stockquote::VER ); ?></em>
	<div class="stock_quote_wrapper">
		<div class="content_cell">
			<form method="post" action="options.php">
				<?php settings_fields( 'wpau_stock_quote' ); ?>
				<?php do_settings_sections( $wpau_stockquote->plugin_slug ); ?>
				<?php submit_button(); ?>
			</form>
		</div><!-- .content_cell -->

		<div class="sidebar_container">
			<a href="https://urosevic.net/wordpress/donate/?donate_for=stock-quote" class="ausq-button paypal_donate" target="_blank"><?php _e( 'Donate', 'stock-quote' ); ?></a>
			<br />
			<a href="https://wordpress.org/plugins/stock-quote/faq/" class="ausq-button" target="_blank"><?php _e( 'FAQ', 'stock-quote' ); ?></a>
			<br />
			<a href="https://wordpress.org/support/plugin/stock-quote" class="ausq-button" target="_blank"><?php _e( 'Community Support', 'stock-quote' ); ?></a>
			<br />
			<a href="https://wordpress.org/support/view/plugin-reviews/stock-quote#postform" class="ausq-button" target="_blank"><?php _e( 'Review this plugin', 'stock-quote' ); ?></a>

			<h2><?php esc_attr_e( 'Disclaimer', 'stock-quote' ); ?></h2>
			<p class="description">
				<?php
				printf(
					__( 'Since %1$s version %2$s source for all stock data used in plugin is provided by %3$s, displayed for informational and educational purposes only and should not be considered as investment advise. <br />Author of the plugin does not accept liability or responsibility for your use of plugin, including but not limited to trading and investment results.', 'stock-quote' ),
					__( 'Stock Quote', 'stock-quote' ),
					'0.2.0',
					'<strong>Alpha Vantage</strong>'
				);
				?>
			</p>
		</div><!-- .sidebar_container -->
	</div><!-- .stock_quote_wrapper -->

	<div class="help">
		<div class="overview">
			<h2><?php _e( 'Help', 'stock-quote' ); ?></h2>
			<p><?php printf( __( 'To insert Stock Quote to content, use shortcode %s where:', 'stock-quote' ), '<code>[stock_quote symbol="" show="" decimals="" number_format="" template="" raw="" class=""]</code>' ); ?></p>
			<p class="description"><strong><?php _e( 'IMPORTANT', 'stock-quote' ); ?></strong> <?php _e( 'All shortcode parameters and values should be lowercase, except symbols which must be uppercase!', 'stock-quote' ); ?></p>
			<dl>
				<dt class="head"><?php _e( 'Parameter', 'stock-quote' ); ?></dt><dd class="head"><?php _e( 'Usage', 'stock-quote' ); ?></dd>
				<dt><code>symbol</code></dt><dd><?php _e( 'represent single stock symbol (default from this settings page used if no custom set by shortcode)', 'stock-quote' ); ?></dd>
				<dt><code>show</code></dt><dd>
					<?php
					printf(
						__( 'can be %1$s to represent company with %2$s, or %3$s to represent company with %4$s', 'stock-quote' ),
						'<code>name</code>',
						__( 'Company Name', 'stock-ticker' ) . ' ' . __( '(default)', 'stock-ticker' ),
						'<code>symbol</code>',
						__( 'Stock Symbol', 'stock-ticker' )
					);
					?>
				</dd>
				<dt><code>decimals</code></dt><dd><?php _e( 'override default number of decimal places for values (default from this settings page used if no custom set by shortcode). Valud values are: 1, 2, 3 and 4', 'stock-quote' ); ?></dd>
				<dt><code>number_format</code></dt><dd>
					<?php
					printf(
						__( 'override default number format for values (default from this settings page used if no custom set by shortcode). Valid options are: %1$s and %2$s', 'stock-quote' ),
						sprintf( '<code>cd</code> %1$s <em>0.000,00</em>; <code>dc</code> %1$s <em>0,000.00</em>; <code>sd</code> %1$s <em>0 000.00</em>', __( 'for', 'stock-quote' ) ),
						sprintf( '<code>sc</code> %s <em>0 000,00</em>', __( 'for', 'stock-quote' )
						)
					);
					?>
				</dd>
				<dt><code>template</code></dt><dd>
					<?php
						printf(
							__( 'override default template string (default is: %1$s). You can use following template keywords: %2$s and %3$s', 'stock-quote' ),
							'<code>%company% %price% %change% %changep%</code>',
							'<code>%symbol%</code>, <code>%exch_symbol%</code>, <code>%company%</code>, <code>%company_name%</code>, <code>%price%</code>, <code>%change%</code>, <code>%changep%</code>, <code>%volume%</code>, <code>%raw_price%</code>, <code>%raw_change%</code>, <code>%raw_changep%</code>',
							'<code>%raw_volume%</code>'
						);
						?>
				</dd>
				<dt><code>raw</code></dt><dd><?php _e( 'to print quote content without being wrapped to SPAN with classes. Disabled by default. Use <code>1</code> or <code>true</code> to enable', 'stock-quote' ); ?></dd>
				<dt><code>class</code></dt><dd><?php _e( 'custom class name for quote item', 'stock-quote' ); ?></dd>
			</dl>
		</div><!-- .overview -->

		<div class="exchanges">
			<div class="exchanges-supported">
				<h3><?php esc_attr_e( 'Supported Stock Exchanges', 'stock-quote' ); ?></h3>
				<ul>
					<?php
					foreach ( $wpau_stockquote::$exchanges['supported'] as $symbol => $name ) {
						printf(
							'<li><strong>%1$s</strong> - %2$s</li>',
							$symbol,
							$name
						);
					}
					?>
				</ul>
			</div><!-- .exchanges-supported -->
			<div class="exchanges-unsupported">
				<h3><?php esc_attr_e( 'Unsupported Stock Exchanges', 'stock-quote' ); ?></h3>
				<ul>
					<?php
					foreach ( $wpau_stockquote::$exchanges['unsupported'] as $symbol => $name ) {
						printf(
							'<li><strong>%1$s</strong> - %2$s</li>',
							$symbol,
							$name
						);
					}
					?>
				</ul>
			</div><!-- .exchanges-unsupported -->
		</div><!-- .exchanges -->
	</div><!-- .help_cell -->
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	$('.wpau-color-field').wpColorPicker();
});
</script>
