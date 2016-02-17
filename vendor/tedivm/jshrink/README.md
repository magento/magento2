# JShrink [![Build Status](https://travis-ci.org/tedious/JShrink.svg?branch=master)](https://travis-ci.org/tedivm/JShrink)

[![License](http://img.shields.io/packagist/l/tedivm/JShrink.svg)](https://github.com/tedivm/JShrink/blob/master/LICENSE)
[![Latest Stable Version](http://img.shields.io/github/release/tedious/JShrink.svg)](https://packagist.org/packages/tedivm/JShrink)
[![Coverage Status](https://coveralls.io/repos/tedious/JShrink/badge.png?branch=master)](https://coveralls.io/r/tedivm/JShrink?branch=master)
[![Total Downloads](http://img.shields.io/packagist/dt/tedivm/jshrink.svg)](https://packagist.org/packages/tedivm/JShrink)


JShrink is a php class that minifies javascript so that it can be delivered to the client quicker. This code can be used
by any product looking to minify their javascript on the fly (although caching the results is suggested for performance
reasons). Unlike many other products this is not a port into php but a native application, resulting in better
performance.


## Usage

Minifying your code is simple call to a static function-

```php
<?php
include('vendor/autoload.php');

// Basic (default) usage.
$minifiedCode = \JShrink\Minifier::minify($js);

// Disable YUI style comment preservation.
$minifiedCode = \JShrink\Minifier::minify($js, array('flaggedComments' => false));
```


## Results

* Raw - 586,990
* Gzip - 151,301
* JShrink - 371,982
* JShrink and Gzip - 93,507


## Installing

### Composer

Installing JShrink can be done through a variety of methods, although Composer is
recommended.

```yaml
"require": {
  "tedivm/jshrink": "~1.0"
}
```

### Github

Releases of JShrink are available on [Github](https://github.com/tedious/JShrink/releases).


## License

JShrink is licensed under the BSD License. See the LICENSE file for details.

In the spirit of open source, use of this library for evil is discouraged but not prohibited.
