# HTMLdoc Minification Plugin for Wordpress

A Wordpress plugin to minify HTML, inline CSS, and inline Javascript. Take your website optimisation to the next level! Other minification plugins blindly find and replace patterns within your code to make it smaller, often using outdated 3rd-party libraries.

**HTMLdoc is a compiler**, it parses your code to an internal representation, optimises it, and then compiles it back to code. The result is better reliability, compression, and performance. It also bundles CSS and Javascript compilers from the same author for minifying your inline CSS and Javascript.

Combines 3 of my projects:

- [HTMLdoc](https://github.com/hexydec/htmldoc) - A tokeniser based HTML document parser and minifier, written in PHP
- [CSSdoc](https://github.com/hexydec/cssdoc) - A tokeniser based CSS document parser and minifier, written in PHP
- [JSlite](https://github.com/hexydec/jslite) - A tokeniser based Javascript minifier designed for compressing inline scripts on the fly, written in PHP

## Description

This plugin provides an administration panel to configure the minification options, once activated, pages will be minified according to the configuration options selected.

You can also control whether the administration system is minified, and output minification stats.

Expect ~10% compression after gzip.
