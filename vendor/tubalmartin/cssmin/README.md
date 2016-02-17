# A PHP port of the YUI CSS compressor

This port is based on version 2.4.8 (Jun 12, 2013) of the [YUI compressor](https://github.com/yui/yuicompressor).

**Table of Contents**

1.  [How to use](#howtouse)
2.  [YUI compressor on asteroids!](#onasteroids)
    1.  [Bugs fixed](#bugsfixed)
    2.  [Enhancements](#enhancements)
3.  [Unit Tests](#unittests)
4.  [API Reference](#api)
5.  [Who uses this?](#whousesit)
6.  [Changelog](#changelog)

<a name="howtouse"></a>

## 1. How to use

**Need a GUI?**

We've made an awesome web based GUI to use the compressor, it's in the `gui` folder.
We built the GUI because many times we need to compress some CSS code quick and easily.

GUI features:

* Optional on-the-fly LESS compilation before compression with error reporting included. We use LESS to write CSS so we spent some time to add LESS compilation before compression.
* Absolute control of the library.

How to use the GUI:

* You need a server with PHP 5.0.0+ installed.
* Download the repository and upload it to your server.
* Open your favourite browser and enter the URL to the `/gui` folder.

**I don't need a GUI**

OK, here's an example that covers a tipical use scenario:

```php
<?php

// Require the compressor
require 'cssmin.php';

// Extract the CSS code you want to compress from your CSS files
$input_css1 = file_get_contents('test1.css');
$input_css2 = file_get_contents('test2.css');

// Create a new CSSmin object.
// By default CSSmin will try to raise PHP settings.
// If you don't want CSSmin to raise the PHP settings pass FALSE to
// the constructor i.e. $compressor = new CSSmin(false);
$compressor = new CSSmin();

// Override any PHP configuration options before calling run() (optional)
$compressor->set_memory_limit('256M');
$compressor->set_max_execution_time(120);

// Compress the CSS code in 1 long line and store the result in a variable
$output_css1 = $compressor->run($input_css1);

// You can change any PHP configuration option between run() calls
// and those will be applied for that run
$compressor->set_pcre_backtrack_limit(3000000);
$compressor->set_pcre_recursion_limit(150000);

// Compress the CSS code splitting lines after a specific column (2000) and
// store the result in a variable
$output_css2 = $compressor->run($input_css2, 2000);

// Do whatever you need with the compressed CSS code
echo $output_css1 . $output_css2;
```

You can also use [Composer](http://getcomposer.org/) to install and autoload the compressor library:

    $ composer.phar require tubalmartin/cssmin

After which the library would be loaded with all the other Composer packages when you include Composer's autoloader file:

```php
require './vendor/autoload.php';
```

<a name="onasteroids"></a>

## 2. YUI compressor on asteroids!

<a name="bugsfixed"></a>

### 2.1. FIXED BUGS still present in the original YUI compressor

* Only one `@charset` at-rule per file and pushed at the beginning of the file. YUI compressor does not remove @charset at-rules if single quotes are used `@charset 'utf-8'`.
* Safer/improved comment removal. YUI compressor would ruin part of the output if the `*` selector is used right after a comment: `a{/* comment 1 */*width:auto;}/* comment 2 */* html .b{height:100px}`. See issues [#2528130](http://yuilibrary.com/projects/yuicompressor/ticket/2528130), [#2528118](http://yuilibrary.com/projects/yuicompressor/ticket/2528118) & [this topic](http://yuilibrary.com/forum/viewtopic.php?f=94&t=9606)
* `background: none;` is not compressed to `background:0;` anymore. See issue [#2528127](http://yuilibrary.com/projects/yuicompressor/ticket/2528127).
* `text-shadow: 0 0 0;` is not compressed to `text-shadow:0;` anymore. See issue [#2528142](http://yuilibrary.com/projects/yuicompressor/ticket/2528142)
* Trailing `;` is not removed anymore if the last property is prefixed with a `*` (lte IE7 hack). See issue [#2528146](http://yuilibrary.com/projects/yuicompressor/ticket/2528146)
* Newlines before and/or after a preserved comment `/*!` are not removed (we leave just 1 newline). YUI removes all newlines making it really hard to spot an important comment.
* Spaces surrounding the `+` operator in `calc()` calculations are not removed. YUI removes them and that is wrong.
* Fix for issue [#2528093](http://yuilibrary.com/projects/yuicompressor/ticket/2528093).
* Fixes for `!important` related issues.
* Fixes @keyframes 0% step bug.
* Some units should not be removed when the value is 0, such as `0s` or `0ms`.
* Fixes replacing 0 length values in selectors such as `.span0px { ... }`.

<a name="enhancements"></a>

### 2.2. ENHANCEMENTS over the original YUI compressor

* Numbers & units compression:
    * Sign is removed from positive numbers: `+2em` gets minified to `2em`.
    * Leading and trailing zeros are removed: `0.2em` gets minified to `.2em`, `-01.010%` to `-1.01%`, `-9.0` to `-9`.
    * Zero length numbers & units are replaced with `0`: `-0.00%`, `.0em`, `0.0000`, `-0px` get minified to `0`.
    * Added newer unit lengths `ch, rem, vw, vh, vm, vmin` so we can replace `0rem` or `0vw` with `0`.
* Colors compression:
    * Percentage and negative RGB values are supported i.e. `rgb(100%, 0%, 0%)` gets minified to `red`.
    * HSL colors are compressed too, i.e. `hsl(0, 100%, 50%)` gets minified to `red`. HSL angles are wrapped and values are clipped if needed.
* All CSS properties are lowercased.


<a name="unittests"></a>

## 3. Unit Tests

Unit tests from YUI are not modified to fit this port so that you if a YUI test fails you can see the improvements/fixes this port provides over the original.

**60+** unit tests written!!

How to run the test suite:

* You need a server with PHP 4.3+ installed (preferrably PHP 5).
* Download the repository and upload it to a folder in your server.
* Open your favourite browser and enter the URL to the file `tests/run.php`.

<a name="api"></a>

## 4. API Reference

### __construct([ bool *$raise_php_limits* ])

**Description**

Class constructor, creates a new CSSmin object.

**Parameters**

*raise_php_limits*

If TRUE, CSSmin will try to raise the values of some php configuration options.
Set to FALSE to keep the values of your php configuration options.
Defaults to TRUE.

### run(string *$css* [, int *$linebreak_pos* ])

**Description**

Minifies a string of uncompressed CSS code.
`run()` may be called multiple times on a single CSSmin instance.

**Parameters**

*css*

A string of uncompressed CSS code.
Defaults to an empty string `''`.

*linebreak_pos*

Some source control tools don't like it when files containing lines longer than, say 8000 characters, are checked in.
The linebreak option is used in that case to split long lines after a specific column.
Defaults to FALSE (1 long line).

**Return Values**

A string of compressed CSS code or an empty string if no string is passed.

### set_memory_limit(mixed *$limit*)

**Description**

Sets the `memory_limit` configuration option for this script

CSSmin default value: `128M`

**Parameters**

*limit*

Values & notes: [memory_limit documentation](http://php.net/manual/en/ini.core.php#ini.memory-limit)

### set_max_execution_time(int *$seconds*)

**Description**

Sets the `max_execution_time` configuration option for this script

CSSmin default value: `60`

**Parameters**

*seconds*

Values & notes: [max_execution_time documentation](http://php.net/manual/en/info.configuration.php#ini.max-execution-time)

### set_pcre_backtrack_limit(int *$limit*)

**Description**

Sets the `pcre.backtrack_limit` configuration option for this script

CSSmin default value: `1000000`

**Parameters**

*limit*

Values & notes: [pcre.backtrack_limit documentation](http://php.net/manual/en/pcre.configuration.php#ini.pcre.backtrack-limit)

### set_pcre_recursion_limit(int *$limit*)

**Description**

Sets the `pcre.recursion_limit` configuration option for this script.

CSSmin default value: `500000`

**Parameters**

*limit*

Values & notes: [pcre.recursion_limit documentation](http://php.net/manual/en/pcre.configuration.php#ini.pcre.recursion-limit)


<a name="whousesit"></a>

## 5. Who uses this port


* [Minify](https://github.com/mrclay/minify) Minify is an HTTP content server. It compresses sources of content (usually files), combines the result and serves it with appropriate HTTP headers.
* [Autoptimize](http://wordpress.org/plugins/autoptimize/) is a Wordpress plugin. Autoptimize speeds up your website and helps you save bandwidth by aggregating and minimizing JS, CSS and HTML.
* [IMPRESSPAGES](http://www.impresspages.org/) PHP framework with content editor.

<a name="changelog"></a>

## 6. Changelog

### v2.4.8-4 22 Sep 2014

* Composer support. The package is [tubalmartin/cssmin](https://packagist.org/packages/tubalmartin/cssmin)
* Fixed issue #17

### 2.4.8-3 26 Apr 2014

* Fixed all reported bugs: See issues #11, #13 (first case only) and #14.
* LESS compiler upgraded to version 1.7.0

### 2.4.8-2 13 Nov 2013

* Chunk length reduced to 5000 chars (previously 25.000 chars) in an effort to avoid PCRE backtrack limits (needs feedback).
* Improvements for the @keyframes 0% step bug. Tests improved.
* Fix IE7 issue on matrix filters which browser accept whitespaces between Matrix parameters
* LESS compiler upgraded to version 1.4.2

### 2.4.8-1 8 Aug 2013

* Fix for the @keyframes 0% step bug. Tests added.
* LESS compiler upgraded to version 1.4.1
