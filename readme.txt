=== Stock Quote ===
Contributors: urkekg
Donate link: https://urosevic.net/wordpress/donate/?donate_for=stock-quote
Tags: widget, stock, securities, quote, financial, finance, exchange, bank, market, trading, investment, stock symbols, stock quotes, forex, nasdaq, nyse, wall street
Requires at least: 4.4.0
Tested up to: 4.9.8
Stable tag: 0.2.1
Requires PHP: 5.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Insert static inline stock ticker for known exchange symbols by customizable shortcode.

== Description ==

A simple and easy configurable plugin for WordPress which allows you to insert inline stock quotes with stock price information to posts, pages, widgets or even to template files. Insertion is mainly available by shortcode.

Please note, stock data has been provided by [Alpha Vantage](https://www.alphavantage.co/)

Stock Quote is simplified, static inline variation of [Stock Ticker](https://wordpress.org/plugins/stock-ticker/) plugin.

**Multisite WordPress is not supported jet**

== Disclaimer ==

All stock data used in **Stock Quote** is provided by **Alpha Vantage**, displayed for informational and educational purposes only and should not be considered as investment advise.

Author of the **Stock Quote** plugin does not accept liability or responsibility for your use of plugin, including but not limited to trading and investment results. Along to that, author of **Stock Quote** plugin can not guarantee that stock prices are always accurate as they are provided by 3rd party service for free.

== Features ==

* Configure default stock symbol that will be displayed by shortcode if no symbol provided
* Configure default presence of company as Company Name or as Stock Symbol
* Configure global colours for unchanged quote, negative and positive changes
* Tooltip for quote item display company name, exchange market and last trade date/time (if available)
* Define custom names for companies to be used instead symbols
* Plugin uses native WordPress function to get and cache data from AlphaVantage.co API for predefined duration of time

For feature requests or help [send feedback](https://urosevic.net/wordpress/plugins/stock-quote/ "Official plugin page") or use support forum on WordPress.

== How It Works? ==

1. When front-end is loaded in browser, plugin will render quote placeholder in place where you have inserted shortcode.
1. Right after page is loaded, AJAX call will request quote content and inject it inside quote placeholder. That quote is retrieved as a cached value in database.
1. Couple seconds latter, second AJAX request will initiate background quote update from WordPress.
1. If `Cache Timeout` period has expired, plugin will pick first symbol from `All Stock Symbols` field. Each time it will pick next symbol.
1. Without disturbing page rendering and speed to get quote from database, plugin in background retrieve data from AlphaVantage.co for picked symbol.
1. If quote is successfully retrieved, plugin will save value for that symbol in database.
1. New fetched quote for symbol will be displayed on front-end only after page reload but not right after updated quote get fetched from AlphaVantage.co!

== How To Use ==

You can add Stock Quote to posts, pages or widgets by shortcode.

= Shortcode =

Use simple shortcode `[stock_quote]` without any parameter in post or page, to display quote with default (global) settings.

**IMPORTANT** All shortcode parameters and values should be lowercase, except symbols which must be uppercase!

You can tune single shortcode with parameters:

* `symbol` - represent single stock symbol (if not provided then use default from settings page)
* `show` - can be `name` to represent company with Company Name (default), or `symbol` to represent company with Stock Symbol. You must add preferred symbol to `Custom Names` textarea on Settings page because Google Finance does not provide nice company names in feeds
* `decimals` - override default number of decimal places for values (default from settings page used if no custom set by shortcode). Valud values are: `1`, `2`, `3` and `4`
* `number_format` - override default number format for values (default from this settings page used if no custom set by shortcode). Valid options are: `cd` for *0.000,00*; `dc` for *0,000.00*; `sd` for *0 000.00* and `sc` for *0 000,00*
* `template` - override default template string (default is: `%company% %price% %change% %changep%`). You can use following template keywords: `%company%`, `%exch_symbol%`, `%symbol%`, `%price%`, `%change%`, `%changep%`, `%volume%`, `%raw_price%`, `%raw_change%`, `%raw_changep%`, `%raw_volume%`
* `raw` - enable printing quote content without wrapping to SPAN with classes. Default is disabled. Can be `1` or `true` for enabled, OR `0` or `false` for disabled.
* `class` - (optional) custom class name for quote item, if you wish some special styling

= Example =

`[stock_quote symbol="^DJI" show="symbol"]`

or

`[stock_quote symbol="MSFT" decimals=3 number_format=cd template="%symbol% %price% %change% %changep%"]`

== Supported Stock Exchange Markets ==

Alpha Vantage provide stock data for following stock exchange markets:

* **ASX** - Australian Securities Exchange
* **BOM** - Bombay Stock Exchange
* **BIT** - Borsa Italiana Milan Stock Exchange
* **TSE** - Canadian/Toronto Securities Exchange
* **FRA** - Deutsche Boerse Frankfurt Stock Exchange
* **ETR** - Deutsche Boerse Frankfurt Stock Exchange
* **AMS** - Euronext Amsterdam
* **EBR** - Euronext Brussels
* **ELI** - Euronext Lisbon
* **EPA** - Euronext Paris
* **LON** - London Stock Exchange
* **MCX** - Moscow Exchange
* **NASDAQ** - NASDAQ Exchange
* **CPH** - NASDAQ OMX Copenhagen
* **HEL** - NASDAQ OMX Helsinki
* **ICE** - NASDAQ OMX Iceland
* **STO** - NASDAQ OMX Stockholm
* **NSE** - National Stock Exchange of India
* **NYSE** - New York Stock Exchange
* **SGX** - Singapore Exchange
* **SHA** - Shanghai Stock Exchange
* **SHE** - Shenzhen Stock Exchange
* **TPE** - Taiwan Stock Exchange
* **TYO** - Tokyo Stock Exchange

== Installation ==

Easy install Stock Quote as any other ordinary WordPress plugin

https://youtu.be/bi9S8mG3Hz0

1. Go to `Plugins` -> `Add New`
1. Search for `Stock Quote` plugin
1. Install and activate `Stock Quote`
1. Get a free API Key from [AlphaVantage.co](https://www.alphavantage.co/support/#api-key)
1. In WordPress Dashboard go to `Settings` -> `Stock Quote`
1. Enter to field `AlphaVantage.co API Key` Alpha Vantage API Key you received in previous step
1. Enter to field `All Stock Symbols` all stock symbols you’ll use on whole website in various shortcodes, separated by comma. This field is used to fetch stock data from AlphaVantage.co API by AJAX in background. Because AV have only API to get data for single symbol, that can take a while to get. Please note, for default shortcode symbol there is still field in Default Settings section of plugin.
1. Save settings and click button `Fetch Stock Data Now!` to initially fetch stock data to database and wait for a while until we get all symbols from AlphaVantage.co for the very first time.
1. Insert shortcode `[stock_quote]` to page or post as usual.

== Screenshots ==

1. Global plugin settings page
2. Stock Quote in action

== Frequently Asked Questions ==

= How to know which stock symbols to use? =

For start you can try with AAPL (Apple). If you need some specific symbol, check you'll need to figure out by your self.

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

You'll need to set company name to Custom Names field on plugin settings page.

= My quote do not update or have large delay =

There is couple possible causes for this issue:
1. Your website do not have enough visits to fetch each symbol from `All Stock Symbols` field. Solution: We are working on `cron` driven updates, but we do not have ETA for that release.
1. You have set too short `Cache Timeout` value so plugin do not have enough time to fetch updated quote for each symbol in `All Stock Symbols`. Solution: Increase `Cache Timeout` value.
1. You have too long `Cache Timeout` value so plugin skip fetching of updated quotes. Solution: decrease `Cache Timeout` value below 600 (that means below 10 minutes).
1. You have set too short `Fetch Timeout` so plugin do not have enough time to successfully retrieve symbol from AlphaVantage.co. Solution: Increase `Fetch Timeout` by 2-3 second than you have set already. If that still does not work, try with another 2-3 seconds.
1. When you hover over quote on front-end, you should see last trade date. If date is older than expected (including last trade day), it's possible that something stuck in plugin. Solution: Enable debugging in WordPress as explained in official article [https://codex.wordpress.org/Debugging_in_WordPress#Example_wp-config.php_for_Debugging](https://codex.wordpress.org/Debugging_in_WordPress#Example_wp-config.php_for_Debugging) and after couple of hours disable debugging, upload wp-content/stock-quote.log to DropBox/Google Drive/etc and provide link to log in email sent throug our contact form at [https://urosevic.net/c/](https://urosevic.net/c/)

== Upgrade Notice ==

= 0.2.1 =
Alphavantage.co API endpoint change to GLOBAL_QUOTE for more precize data

= 0.2.0 =
Broken functionality fix

= 0.1.1 =
Bugfix release

= 0.1.0 =
This is initial version of plugin.

== Changelog ==

= 0.2.1 (20180923) =
* Improve: statuses and messages in fetch log
* Improve: Make Force Fetch to wait between each symbol fetch regarding to the API Tier
* Improve: Remove duplicate symbols on settings update
* Simplify: Merge 3 settings sections to single register_settings
* Improve: Move routine to extract symbol to fetch to self method `get_symbol_to_fetch()`
* Improve: Move stock data to DB to self method `data_to_db()`
* Change: Make method `get_stock_from_db()` public so user can access Stock data in DB from custom functions
* Change: Move method `sanitize_symbols()` to main class and make it public static so user can access it from custom functions
* Add Alpha Vantage Tier option for better fetch timeout control
* Add to settings page list of stock exchanges supported by AlphaVantage
* Switch to GLOBAL_QUOTE API mode and eliminate requirement to calculate change amount from TIME_SERIES_DAILY and TIME_SERIES_INTRADAY
* Fix: Allow dash character in symbols (eg. `STO:ERIC-B`) (thanks to @iarwain)
* (20180609) Readme: add How It Works
* Readme: Update FAQ with `stuck quote` question

= 0.2.0.5 (20180901) =
* Fix: `Netagive` spelling error (thanks to @eigood)

= 0.2.0.4 (20180604) =
* Fix: workaround for stuck skipping
* Add: URL request to unlock fetch

= 0.2.0.3 (20180219) =
* Add: shortcode parameter `raw`
* Add: template keywords `%raw_price%`, `%raw_change%`, `%raw_changep%` and `%raw_volume%`

= 0.2.0.2 (20180204) =
* Fix: price amount was taken from `last_open` instead of proper `last_close`

= 0.2.0.1 (20171229) =
* Fix: on PHP 5.x - PHP Fatal error:  Using $this when not in object context
* Test: PHP 5.6.31 and 7.1.12

= 0.2.0 (20171222) =
* (20171224)
* * Add: front-end updater AJAX call
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
