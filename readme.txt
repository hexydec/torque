=== Torque - Optimise the transport of your Website ===
Contributors: hexydec
Tags: minify,minification,performance,security,optimization
Requires at least: 5.9
Tested up to: 6.1
Requires PHP: 7.4
Stable tag: 0.7.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A Wordpress plugin to optimise the transport of your website to the client. Reduce the load on your server and make your Wordpress website fly!

== Description ==
Take advantage of best in class minification to squeeze every byte out of your HTML, CSS, and Javascript, combine this with the control over cache headers, lazy loading, and more, and your website will not only be noticeably faster, your server will be under less load, enabling you to serve more clients with your existing metal.

The plugin also includes a suite of security features to help you secure your website, including full control over Content-Security-Policy, which enables you to control which domains can embed assets on your website, and what domains you can connect to. This prevents malicious scripts from being able to run and more.

= Features =

* Site Analysis
    * Environment information
    * Page information such as MIME type, output size and compression ratio
    * Asset counts and sizes with recommendations
    * Performance metrics with descriptions and recommendations
    * Security metrics with descriptions and recommendations
* Minification
    * Minify your HTML (Uses HTMLdoc)
    * Minify and cache your inline CSS (Uses CSSdoc)
    * Minify and cache your inline Javascript (Uses JSlite)
* Combine Files
    * Combine and minify CSS files
    * Combine and minify Javascript files
* Lazy load images
* Headers
    * Set shared cache timeout
    * Set client cache timeout
    * Enable client to check whether their cached page is still valid, and send an HTTP 304 response if it is
* Security
    * Disable MIME sniffing
    * XSS protection
    * Control how the site can be embedded
    * Enable HSTS to force browsers to only connect over HTTPS
    * Specify Content-Security-Policy to control what domains can connect and embed content in your site
* Preload
	* Select which assets to preload with first load
	* Preload combined stylesheets
* Administration panel to control all features, including all minification optimisations

See the [Torque Github homepage](https://github.com/hexydec/torque) for more information.

== Installation ==

Upon installation of the plugin, most of the settings will be disabled. Only the settings in the "Caching" section will be implemented.

To get the plugin up and running to a basic level, enable some settings in the "Settings" section.

It is recommended that you do not use this plugin with other minification plugins.

== Screenshots ==
1. The Overview screen analyses your website and gives recommendations
2. The Settings screen enables you to set some basic optimisation settings
3. The HTML tab enables you to specify your HTML minification settings
4. The CSS tab enables you to specify your CSS minification settings
5. The Javascript tab enables you to specify your Javascript minification settings
6. The Caching screen gives you some browser cache and shared cache settings
7. The Security screen enables you to set some security headers
7. The Policy screen enables you to specify a Content-Security-Policy
8. The Preload screen lets you select which assets will be preloaded

== Frequently Asked Questions ==

= What kind of compression can I expect from minification? =

Depending on how compressible you content is you can expect ~10 - 15% compression of your page before gzip compression, after gzip you can expect ~5 - 10%.

= How long does it take to minify my page? =

You can tick the "Show stats in the console" option to see how long it takes to minify your page and what compression was achieved, view the output in the developer console (Press F12).

Note that inline CSS and Javascript is cached in a Wordpress transient, so if you page has inline code, it should be faster after first run.

= What are the tradeoffs for minifying my HTML? =

You are swapping the time it takes to send the extra bytes down the wire to your clients for extra CPU time on the server.

Torque uses my other project HTMLdoc to minify your code, it has been designed to use on the fly and has been optimised for speed. Even so I recommend you use some sort of cache in front of your PHP code to make sure your time-to-first-byte is optimised, then the extra CPU time doesn't matter.

= How can I test if my page is faster after using your plugin? =

The best tool to use is Lighthouse, which is built into Blink based browsers such as Chrome, Edge and others:

* Press F12 to bring up the developer tools
* Select the "Lighthouse" tab
* Click "Generate Report"

Do this before you enable the plugin, and then again after you have enabled and configured the plugin. The performance metric should be higher with the plugin. You can also look at the Network tab in the developer console and see that the total download size and number of requests is lower (With combne and minify enabled).

= I enabled minification and it broke my site =

Some advanced minification optimisations can cause issues with your website's layout, or break your Javascript depending on how your CSS/Javascript selectors are setup.

For example, you can strip default attributes from your HTML such as `type="text"` on the `<input>` object. If you have a CSS or Javascript selector that relies on this attribute being there, such as `input[type=input]`, the selector will no longer match. See [HTMLdoc: Mitigating Side Effects of Minification](https://github.com/hexydec/htmldoc/blob/master/docs/mitigating-side-effects.md) for solutions.

= Why is HTMLdoc best in class? =

Other minification plugins blindly find and replace patterns within your code to make it smaller, often using outdated 3rd-party libraries.

HTMLdoc is a compiler, it parses your code to an internal representation, optimises it, and then compiles it back to code. The result is better reliability, compression, and performance. It also bundles CSS and Javascript compilers from the same author for minifying your inline CSS and Javascript which use the same technology to make less mistakes and offer better compression.

All three libraries have automated test suites to ensure reliability, and should outperform other PHP based minifiers in terms of compression.

= What is Content Security Policy? =

Content Security Policy (CSP) is a very powerful browser security feature that only enables assets to be downloaded from the specified domains. Any assets that are downloaded from domains that are not listed will be blocked.

= How do I setup my Content Security Policy? =

Using the developer tools in your browser (Press F12), look at the network tab on each page, and note down the domains that are used for different assets, along with their asset type. You can then enter those domains in to the relevant CSP boxes. Be sure to run any extra features of your website that use Fetch or XHR, as these connections are also bound by CSP.

Once the domains are entered, and with the CSP setting set to "Enabled only for me (testing)", go through the pages of your website again, checking for Content-Security-Policy errors in the console. If there are errors, the console should indicate which domain and category trigger the CSP error. Note that your website may not function correctly whilst you do this if the CSP is not correct, but this behaviour will only be exhibited for you with the testing setting.

When you are happy that all domains and settings are set correctly, you can enable the CSP setting.

= How does preload work? =

Preload works by notifies the browser as soon as possible of assets it will need to load the page, this enables it to start downloading them sooner than if it discovered them on page. For example font files are normally linked from the stylesheet, so the browser has to download and parse the stylesheet before it can request them. By preloading, when it discovers that it needs those assets, they will already be downloading. Thus your website will load faster.

== Changelog ==

= Version 0.7.1 =

* Updated JSlite to fix javascript parsing issue

= Version 0.7.0 =

* Improved Javascript combine function to offload inline javascript into the bundle file and fix ordering issues
* More Javascript minification options
* Improved overview metrics
* Console stats now only show for the admin who set the setting
* Removed support for HTTP/2.0 Push, as it is deprecated with HTTP/3.0, only preload is now suppoorted
* Reworked Content Security Policy manager to gather violations and recommend settings
* Lots of bug fixes
* Syntax improvements

= Version 0.6.5 =

* Tested with Wordpress v6.1
* Fixed bug in Content-Security-Policy generator where a directive was not spelt correctly
* Updated dependencies
* Minor syntax improvements

= Version 0.6.4 =

* Tested with Wordpress v6.0

= Version 0.6.3 =

* Updated dependencies for better PHP 8.1 compatibility
* Improved type hinting

= Version 0.6.2 =

* Fixed issue when the plugin is installed where the wrong value was written to a config option, this then prevented Javascript from being compiled
* Fixed issue where if a datasource returns false, it caused an error

= Version 0.6.1 =

* Fixed issue where the plugin said it was only compatible with PHP 8.0+, whereas it still supports 7.4
* Updated dependencies

= Version 0.6.0 =

* Updated dependencies to fix PHP 8.0/8.1 issues

= Version 0.5.8 =

* Added hook to rebuild the assets when a plugin is updated
* Added CLI command "torque rebuild"

= Version 0.5.7 =

* Updated dependencies to fix issues with minifying Javascript
* Updated readme to add unlisted features

= Version 0.5.6 =

* Reworked how the combined Javascript file is included to make sure the original order is kept, and inline code is loaded either before or after the combined file as defined by the script include
* Fixed issues when addressing stylesheet assets which caused some not to be listed

= Version 0.5.5 =

* Fixed issue with how some internal addresses were formatted for certain features
* Updated terminology of the HTTP/2.0 Push feature
* Changed defaults of some HTML attribute minification options to false as they may be unsafe and updated description in `config::$options`
* Updated FAQ in readme


= Version 0.5.4 =

* Fixed issues with how URL's were rewritten when combining CSS files, which caused image and font files not to appear in some cases

= Version 0.5.3 =

* Fixed issue in HTMLdoc where domain URLs were not minified correctly
* Updated readme
