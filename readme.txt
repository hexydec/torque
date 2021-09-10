=== Torque - Optimise the transport of your Website ===
Contributors: hexydec
Tags: minify,minification,performance,security,optimization
Requires at least: 5.7
Tested up to: 5.8
Requires PHP: 7.3
Stable tag: 0.5.3
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
7. The Security screen enables you to set some security headers and specify a Content-Security-Policy
8. The Preload screen lets you select which assets will be preloaded with HTTP/2.0 preload

== Frequently Asked Questions ==

= What kind of compression can I expect from minification? =

Depending on how compressible you content is you can expect ~10 - 15% compression of your page before gzip compression, after gzip you can expect ~5 - 10%.

= How long does it take to minify my page?

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

= How does HTTP/2.0 preload work? =

To enable preload, you must have an HTTP/2.0 enabled server, and your website must be served over HTTPS. You may also have to specifically configure your server to enable preload.

Preload works by "pushing" the selected assets onto the client when they first request a page, so they receive assets they haven't requested in the initial payload. When the users browser then parses the page, and knows what assets to request, the browser already has them ready to load.

To prevent continually pushing assets onto the client on each page load, a cookie (called "torque-preload") is used to indicate that assets have already been pushed to the client.

= My server doesn't support HTTP/2.0 or my website is not served over HTTPS, can I still use preload? =

Preload is best when your site is delivered over HTTPS using the HTTP/2.0 protocol, but you can still take advantage of preload without this setup, but it won't be quite as fast as with it setup correctly.

Preload is implemented through a "Link" header, which lists all the assets to preload. When setup correctly, your server will read this header and bundle the listed assets and push them onto the client. When not enabled at server level, the header is passed to the client who can request the assets immediately upon receipt of the page. If any of these assets are chained within other assets, the preload header will enable the browser to fetch them earlier.

== Changelog ==

= Version 0.5.3 =

* Fixed issue in HTMLdoc where domain URLs were not minified correctly
* Updated readme
