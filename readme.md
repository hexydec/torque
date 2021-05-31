# Torque - Transport Optimisation Plugin for Wordpress

A Wordpress plugin to optimise the transport of your website to the client. Reduce the load on your server and make your Wordpress website fly!

**This project is currently alpha, check your deployment thoroughly before deploying into production**

## Description

Take advantage of best in class minification to squeeze every byte out of your HTML, combine this with the ability to optimally configure shared and client caches, and your website will not only be noticeably faster, your server will be under less load, and so you will be able to server more clients with your existing metal.

## Features

- Minify your HTML (Uses [HTMLdoc](https://github.com/hexydec/htmldoc))
- Minify and cache your inline CSS (Uses [CSSdoc](https://github.com/hexydec/cssdoc))
- Minify and cache your inline Javascript (Uses [JSlite](https://github.com/hexydec/jslite))
- Lazy load images
- Set shared cache timeout
- Set client cache timeout
- Enable client to check whether their cached page is still valid, and send an HTTP 304 response if it is
- Administration panel to control all features, including all minification optimisations
- Print minification stats in the console

## FAQ

### What kind of compression can I expect from minification?

Depending on how compressible you content is you can expect ~10 - 15% compression of your page before gzip compression, after gzip you can expect ~5 - 10%.

### What are the tradeoffs for minifying my HTML?

You are swapping the time it takes to send the extra bytes down the wire to your clients for extra CPU time on the server.

Torque uses my other project [HTMLdoc](https://github.com/hexydec/htmldoc) to minify your code, it has been designed to use on the fly and has been optimised for speed. Even so I recommend you use some sort of cache in front of your PHP code to make sure your time-to-first-byte is optimised, then the extra CPU time doesn't matter.

### Why is HTMLdoc best in class?

Other minification plugins blindly find and replace patterns within your code to make it smaller, often using outdated 3rd-party libraries.

**HTMLdoc is a compiler**, it parses your code to an internal representation, optimises it, and then compiles it back to code. The result is better reliability, compression, and performance. It also bundles CSS and Javascript compilers from the same author for minifying your inline CSS and Javascript which use the same technology to make less mistakes and offer better compression.

### Why is the plugin called Torque?

Transport Optimisation - Really QUick & Easy.
