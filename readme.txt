=== Stock Quote ===
Contributors: urkekg
Donate link: https://urosevic.net/wordpress/donate/?donate_for=stock-quote
Tags: widget, stock, securities, quote, financial, finance, exchange, bank, market, trading, investment, stock symbols, stock quotes, forex, nasdaq, nyse, wall street
Requires at least: 3.9.0
Tested up to: 4.7.5
Stable tag: 0.1.7.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Quick and easy insert static inline stock information for specific exchange symbol by customizable shortcode.

== Description ==

*IMPORTANT NOTICE*
Google in September 2017 completely abandoned free stock API we used for Stock Quote plugin. That is reason why plugin does not work anymore. We'll try to find other stock resource allowed by wordpress.org rules and update plugin. If you have any advise or example of free stock API, feel free to contact us at https://urosevic.net/c/

A simple and easy configurable plugin that allows you to insert inline stock quotes with stock price information (data provided by Google Finance). Insertion is enabled by shortcode.

Stock Quote is simplified, static inline variation of [Stock Ticker](https://wordpress.org/plugins/stock-ticker/) plugin.

= Features =
* Configure default stock symbol that will be displayed by shortcode if no symbol provided
* Configure default presence of company as Company Name or as Stock Symbol
* Configure global colours for unchanged quote, negative and positive changes
* Tooltip for quote item display company name, exchange market and last trade date/time
* Define custom names for companies to be used instead symbols
* Plugin uses native WordPress function to get and cache data from Google Finance for predefined duration of time

For feature requests or help [send feedback](https://urosevic.net/wordpress/plugins/stock-quote/ "Official plugin page") or use support forum on WordPress.

= Shortcode =
Use simple shortcode `[stock_quote]` without any parameter in post or page, to display quote with default (global) settings.

You can tune single shortcode with parameters:

* `symbol` - represent single stock symbol (if not provided then use default from settings page)
* `show` - can be `name` to represent company with Company Name (default), or `symbol` to represent company with Stock Symbol. You must add preferred symbol to˛`Custom Names` textarea on Settings page because Google Finance does not provide nice company names in feeds
* **NEW** `decimals` - override default number of decimal places for values (default from settings page used if no custom set by shortcode). Valud values are: `1`, `2`, `3` and `4`
* **NEW** `number_format` - override default number format for values (default from this settings page used if no custom set by shortcode). Valid options are: `cd` for *0.000,00*; `dc` for *0,000.00*; `sd` for *0 000.00* and `sc` for *0 000,00*
* **NEW** `template` - override default template string (default is: `%company% %price% %change% %changep%`). You can use following template keywords: `%company_show%`, `%company%`, `%exchange%`, `%exch_symbol%`, `%symbol%`, `%price%`, `%change%`, `%changep%` and `%ltrade%`
* `nolink` - to disable link of quotes to Google Finance page set to `1` or `true`
* `class` - (optional) custom class name for quote item, if you wish some special styling

= Example =

`[stock_quote symbol="^DJI" show="symbol"]`

or

`[stock_quote symbol="MSFT" decimals=3 number_format=cd template="%symbol% %price% %change% %changep%"]`

== Installation ==

Easy install Stock Quote as any other ordinary WordPress plugin

1. Go to `Plugins` -> `Add New`
1. Search for `Stock Quote` plugin
1. Install and activate `Stock Quote`
1. Configure default plugin options and insert shortcode `[stock_quote]` to page or post

== Screenshots ==

1. Global plugin settings page
2. Stock Quote in action

== Frequently Asked Questions ==

= How to know which stock symbols to use? =

Visit [Google Finance Stock Screener](https://www.google.com/finance#stockscreener) and look for preferred symbols that you need/wish to display on your site.
For start you can try with AAPL (Apple)

= How to get Dow Jones Industrial Average? =

To get quote for this exchange, simply add symbol `.DJI` or `^DJI`.

= How to get currency exchange rate? =

Use Currency symbols like `EURGBP=X` to get rate of `1 Euro` = `? British Pounds`

= How to get descriptive title for currency exchange rates =

Add to `Custom Names` legend currency exchange symbol w/o `=X` part, like:

`EURGBP;Euro (€) ⇨ British Pound Sterling (£)`

= How to get proper stock price from proper stock exchange? =

Enter symbol in format `EXCHANGE:SYMBOL` like `LON:FFX`

= How to add Stock Ticker to header theme file? =

Add this to your template file (you also can add custom parameters for shortcode):

`<?php echo do_shortcode('[stock_ticker]'); ?>`

= I set to show company name but symbol is displayed instead =

Please note that Google Finance does not provide company name in retrieved feeds. You'll need to set company name to Custom Names field on plugin settings page.

== Disclaimer ==

Data for Stock Quote has provided by Google Finance and per their disclaimer, it can only be used at a noncommercial level. Please also note that Google has stated Finance API as deprecated and has no exact shutdown date.

[Google Finance Disclaimer](http://www.google.com/intl/en-US/googlefinance/disclaimer/#disclaimers)

Data is provided by financial exchanges and may be delayed as specified
by financial exchanges or our data providers. Google does not verify any
data and disclaims any obligation to do so.

Google, its data or content providers, the financial exchanges and
each of their affiliates and business partners (A) expressly disclaim
the accuracy, adequacy, or completeness of any data and (B) shall not be
liable for any errors, omissions or other defects in, delays or
interruptions in such data, or for any actions taken in reliance thereon.
Neither Google nor any of our information providers will be liable for
any damages relating to your use of the information provided herein.
As used here, “business partners” does not refer to an agency, partnership,
or joint venture relationship between Google and any such parties.

You agree not to copy, modify, reformat, download, store, reproduce,
reprocess, transmit or redistribute any data or information found herein
or use any such data or information in a commercial enterprise without
obtaining prior written consent. All data and information is provided “as is”
for personal informational purposes only, and is not intended for trading
purposes or advice. Please consult your broker or financial representative
to verify pricing before executing any trade.

Either Google or its third party data or content providers have exclusive
proprietary rights in the data and information provided.

Please find all listed exchanges and indices covered by Google along with
their respective time delays from the table on the left.

Advertisements presented on Google Finance are solely the responsibility
of the party from whom the ad originates. Neither Google nor any of its
data licensors endorses or is responsible for the content of any advertisement
or any goods or services offered therein.

== Upgrade Notice ==
= 0.1.1 =
Bugfix release

= 0.1.0 =
This is initial version of plugin.

== Changelog ==
= 0.2.0 (20171222) =
* (20171222)
* Move: method sanitize_symbols to main plugin class
* Add: on shortcode renderer part to check is current symbol already in All Symbols list and append if it's not (method `add_to_all_symbols`)
* Add: fetching system to settings page
* Fix: Fatal exceptions caused by classes, methods and variables renaming
* Fix: Settings page
* Fix: Admin settings symbol
* (20171217) Add: plugin update script with database creation and legacy settings migration
* Add: All Symbols settings field
* Add: AlphaVantage parser and DB updater
* Add: get_stock_from_db
* (20171212) Start AlphaVantage.co version based on v0.1.7.1

= 0.1.7.1 (20170524) =
* Fix: when changep is empty - PHP Warning:  number_format() expects parameter 1 to be float, string given in wp-content/plugins/stock-quote/stock-quote.php on line 436

= 0.1.7 (20170521) =
* Add: `decimals`, `number_format` and `template` as shortcode parameters
* Add: Error check on fetching data from Google
* Cleanup: Remove unused constant `WPAU_STOCK_QUOTE_CACHE_TIMEOUT`
* Cleanup: Improve variable names (like `sq_transient_id` to `transient_id`)
* Remove: unused Stock Quote element ID
* Update: help for shortcode and parameters
* Update: readme file

= 0.1.6 (20170118) =
* Add: options to choose number format and amount of decimal places
* Optimize: Remove enqueing stylesheet and move inline CSS from footer to HEAD. Converted images to single inline data:image to reduce HTTP requests.
* Simplify: remove shortcode parameters zero, minus and plus, because webmaster can tweak colours by custom class
* Simplify: convert main CSS to SASS and inject to HEAD instead link to small file
* Change: improve settings page layout.

= 0.1.5 (20160523) =
* Fix: Complete support for localization
* Add: Localization to Serbian Cyrillic

= 0.1.4 (20151016) =
* Fix: Quote stuck and never change
* Change: Made name of transient cache key name human readable

= 0.1.3 (20150809) =
* Change: Item ID length reduced fro 8 to 4 characters
* Change: Move all core methods inside class
* Make code fully compliant to WordPress Coding Standard
* Update FAQ

= 0.1.2 (20150723) =
* Add: Option to purge cache by providing parameter `stockquote_purge_cache` in page URL
* Add: Option on plugin settings page to set fetch timeout in seconds (2 is default). Usefull for websites hosted on shared hosting.
* Change: Timeout fields to HTML5 number

= 0.1.1 (20150607) =
* Fix: Make available to work with our Stock Ticker plugin

= 0.1.0 (20150408) =
* Initial release
