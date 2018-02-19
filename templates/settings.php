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
	<h2><?php echo $wpau_stockquote->plugin_name . ' ' . __( 'Settings', 'stock-quote' ); ?></h2>
	<em>Plugin version: <?php echo $wpau_stockquote::VER; ?></em>
	<div class="stock_quote_wrapper">
		<div class="content_cell">
			<form method="post" action="options.php">
				<?php settings_fields( 'wpausq_general' ); ?>
				<?php settings_fields( 'wpausq_default' ); ?>
				<?php settings_fields( 'wpausq_advanced' ); ?>
				<?php do_settings_sections( $wpau_stockquote->plugin_slug ); ?>
				<?php submit_button(); ?>
			</form>
		</div><!-- .content_cell -->

		<div class="sidebar_container">
			<a href="https://urosevic.net/wordpress/donate/?donate_for=stock-quote" class="ausq-button paypal_donate" target="_blank">Donate</a>
			<br />
			<a href="https://wordpress.org/plugins/stock-quote/faq/" class="ausq-button" target="_blank">FAQ</a>
			<br />
			<a href="https://wordpress.org/support/plugin/stock-quote" class="ausq-button" target="_blank">Community Support</a>
			<br />
			<a href="https://wordpress.org/support/view/plugin-reviews/stock-quote#postform" class="ausq-button" target="_blank">Review this plugin</a>

			<h2><?php esc_attr_e( 'Disclaimer', 'stock-quote' ); ?></h2>
			<p class="description">Data for Stock Quote has been provided by AlphaVantage.co.</p>

		</div><!-- .sidebar_container -->
	</div><!-- .stock_quote_wrapper -->

	<div class="help">
		<h2><?php _e( 'Help', 'stock-quote' ); ?></h2>
		<p><?php printf( __( 'To insert Stock Quote to content, use shortcode <code>%s</code> where:', 'stock-quote' ), '[stock_quote symbol="" show="" decimals="" number_format="" template="" raw="" class=""]' ); ?></p>
		<p class="description"><strong>IMPORTANT</strong> All shortcode parameters and values should be lowercase, except symbols which must be uppercase!</p>
		<p>
			<ul>
				<li><code><strong>symbol</strong></code> <?php _e( 'represent single stock symbol (default from this settings page used if no custom set by shortcode)', 'stock-quote' ); ?></li>
				<li><code><strong>show</strong></code> <?php printf( __( 'can be <code>%s</code> to represent company with Company Name (default), or <code>%s</code> to represent company with Stock Symbol', 'stock-quote' ), 'name', 'symbol' ); ?></li>
				<li><code><strong>decimals</strong></code> <?php _e( 'override default number of decimal places for values (default from this settings page used if no custom set by shortcode). Valud values are: 1, 2, 3 and 4', 'stock-quote' ); ?></li>
				<li><code><strong>number_format</strong></code> <?php printf( __( 'override default number format for values (default from this settings page used if no custom set by shortcode). Valid options are: %s and %s', 'stock-quote' ), '<code>cd</code> for <em>0.000,00</em>; <code>dc</code> for <em>0,000.00</em>; <code>sd</code> for <em>0 000.00</em>', '<code>sc</code> for <em>0 000,00</em>' ); ?></li>
				<li><code><strong>template</strong></code> <?php printf( __( 'override default template string (default is: %1$s). You can use following template keywords: %2$s and %3$s', 'stock-quote'), '<code>%company% %price% %change% %changep%</code>', '<code>%company%</code>, <code>%exch_symbol%</code>, <code>%symbol%</code>, <code>%price%</code>, <code>%change%</code>, <code>%changep%</code>', '<code>%volume%</code>, <code>%raw_price%</code>, <code>%raw_change%</code>, <code>%raw_changep%</code>, <code>%raw_volume%</code>' ); ?></li>
				<li><code><strong>raw</strong></code> <?php _e( 'to print quote content without being wrapped to SPAN with classes. Disabled by default. Use <code>1</code> or <code>true</code> to enable', 'stock-quote' ); ?></li>
				<li><code><strong>class</strong></code> <?php _e( 'custom class name for quote item', 'stock-quote' ); ?></li>
			</ul>
		</p>
	</div><!-- .help_cell -->
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	$('.wpau-color-field').wpColorPicker();
});
</script>
