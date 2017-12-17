<div class="wrap" id="stock_quote_settings">
	<h1><?php _e( 'Stock Quote Settings', 'stock-quote' ); ?></h1>
	<em>Plugin version: <?php echo WPAU_STOCK_QUOTE_VER; ?></em>
	<div class="stock_quote_wrapper">
		<div class="content_cell">

			<form method="post" action="options.php">
				<?php @settings_fields( 'wpausq_default_settings' ); ?>
				<?php @settings_fields( 'wpausq_advanced_settings' ); ?>

				<?php @do_settings_sections( 'wpau_stock_quote' ); ?>

				<?php @submit_button(); ?>
			</form>
			<h2><?php _e( 'Help', 'stock-quote' ); ?></h2>
			<p><?php printf( __( 'To insert Stock Quote to content, use shortcode <code>%s</code> where:', 'stock-quote' ), '[stock_quote symbol="" show="" decimals="" number_format="" template="" nolink="" class=""]' ); ?>
				<ul>
					<li><code><strong>symbol</strong></code> <?php _e( 'represent single stock symbol (default from this settings page used if no custom set by shortcode)', 'stock-quote' ); ?></li>
					<li><code><strong>show</strong></code> <?php printf( __( 'can be <code>%s</code> to represent company with Company Name (default), or <code>%s</code> to represent company with Stock Symbol', 'stock-quote' ), 'name', 'symbol' ); ?></li>
					<li><code><strong>decimals</strong></code> <?php _e( 'override default number of decimal places for values (default from this settings page used if no custom set by shortcode). Valud values are: 1, 2, 3 and 4', 'stock-quote' ); ?></li>
					<li><code><strong>number_format</strong></code> <?php printf( __( 'override default number format for values (default from this settings page used if no custom set by shortcode). Valid options are: %s and %s', 'stock-quote' ), '<code>cd</code> for <em>0.000,00</em>; <code>dc</code> for <em>0,000.00</em>; <code>sd</code> for <em>0 000.00</em>', '<code>sc</code> for <em>0 000,00</em>' ); ?></li>
					<li><code><strong>template</strong></code> <?php printf( __( 'override default template string (default is: %1$s). You can use following template keywords: %2$s and %3$s', 'stock-quote'), '<code>%company% %price% %change% %changep%</code>', '<code>%company_show%</code>, <code>%company%</code>, <code>%exchange%</code>, <code>%exch_symbol%</code>, <code>%symbol%</code>, <code>%price%</code>, <code>%change%</code>, <code>%changep%</code>', '<code>%ltrade%</code>' ); ?></li>
					<li><code><strong>nolink</strong></code> <?php _e( 'to disable link of quotes to Google Finance page set to <code>1</code> or <code>true</code>', 'stock-quote' ); ?></li>
					<li><code><strong>class</strong></code> <?php _e( 'custom class name for quote item', 'stock-quote' ); ?></li>
				</ul>
			</p>

			<p><?php printf(
				__( 'If you experience error message after update (WordPress or plugin), try to increase %s parameter in settings (from default 2 to 3 seconds), and then append to page URL parameter %s to re-fetch quote feed.', 'stock-quote'),
				sprintf( '<strong>%s</strong>', __( 'Fetch Timeout', 'stock-quote' ) ),
				'<em>?stockquote_purge_cache=1</em>'
				); ?></p>
		</div><!-- .content_cell -->

		<div class="sidebar_container">
			<a href="https://urosevic.net/wordpress/donate/?donate_for=stock-quote" class="ausq-button paypal_donate" target="_blank">Donate</a>
			<br />
			<a href="https://wordpress.org/plugins/stock-quote/faq/" class="ausq-button" target="_blank">FAQ</a>
			<br />
			<a href="https://wordpress.org/support/plugin/stock-quote" class="ausq-button" target="_blank">Community Support</a>
			<br />
			<a href="https://wordpress.org/support/view/plugin-reviews/stock-quote#postform" class="ausq-button" target="_blank">Review this plugin</a>
		</div><!-- .sidebar_container -->
	</div><!-- .stock_quote_wrapper -->
</div>

<h2><?php _e( 'Disclaimer', 'stock-quote' ); ?></h2>
<p class="description">Data for Stock Quote has provided by Google Finance and per their disclaimer,
it can only be used at a noncommercial level. Please also note that Google has stated
Finance API as deprecated and has no exact shutdown date.<br />
<br />
<a href="http://www.google.com/intl/en-US/googlefinance/disclaimer/#disclaimers">Google Finance Disclaimer</a><br />
<br />
Data is provided by financial exchanges and may be delayed as specified
by financial exchanges or our data providers. Google does not verify any
data and disclaims any obligation to do so.
<br />
Google, its data or content providers, the financial exchanges and
each of their affiliates and business partners (A) expressly disclaim
the accuracy, adequacy, or completeness of any data and (B) shall not be
liable for any errors, omissions or other defects in, delays or
interruptions in such data, or for any actions taken in reliance thereon.
Neither Google nor any of our information providers will be liable for
any damages relating to your use of the information provided herein.
As used here, “business partners” does not refer to an agency, partnership,
or joint venture relationship between Google and any such parties.
<br />
You agree not to copy, modify, reformat, download, store, reproduce,
reprocess, transmit or redistribute any data or information found herein
or use any such data or information in a commercial enterprise without
obtaining prior written consent. All data and information is provided “as is”
for personal informational purposes only, and is not intended for trading
purposes or advice. Please consult your broker or financial representative
to verify pricing before executing any trade.
<br />
Either Google or its third party data or content providers have exclusive
proprietary rights in the data and information provided.
<br />
Please find all listed exchanges and indices covered by Google along with
their respective time delays from the table on the left.
<br />
Advertisements presented on Google Finance are solely the responsibility
of the party from whom the ad originates. Neither Google nor any of its
data licensors endorses or is responsible for the content of any advertisement
or any goods or services offered therein.</p>
<script type="text/javascript">
jQuery(document).ready(function($){
	$('.wpau-color-field').wpColorPicker();
});
</script>
