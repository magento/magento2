# Emogrifier

[![Build Status](https://travis-ci.org/jjriv/emogrifier.svg?branch=master)](https://travis-ci.org/jjriv/emogrifier)
[![Latest Stable Version](https://poser.pugx.org/pelago/emogrifier/v/stable.svg)](https://packagist.org/packages/pelago/emogrifier)
[![Total Downloads](https://poser.pugx.org/pelago/emogrifier/downloads.svg)](https://packagist.org/packages/pelago/emogrifier)
[![Latest Unstable Version](https://poser.pugx.org/pelago/emogrifier/v/unstable.svg)](https://packagist.org/packages/pelago/emogrifier)
[![License](https://poser.pugx.org/pelago/emogrifier/license.svg)](https://packagist.org/packages/pelago/emogrifier)

_n. e•mog•ri•fi•er [\ē-'mä-grƏ-,fī-Ər\] - a utility for changing completely the nature or appearance of HTML email,
esp. in a particularly fantastic or bizarre manner_

Emogrifier converts CSS styles into inline style attributes in your HTML code. This ensures proper display on email
and mobile device readers that lack stylesheet support.

This utility was developed as part of [Intervals](http://www.myintervals.com/) to deal with the problems posed by
certain email clients (namely Outlook 2007 and Google Gmail) when it comes to the way they handle styling contained
in HTML emails. As many web developers and designers already know, certain email clients are notorious for their
lack of CSS support. While attempts are being made to develop common
[email standards](http://www.email-standards.org/), implementation is still a ways off.

The primary problem with uncooperative email clients is that most tend to only regard inline CSS, discarding all
`<style>` elements and links to stylesheets in `<link>` elements. Emogrifier solves this problem by converting CSS
styles into inline style attributes in your HTML code.


- [How it works](#how-it-works)
- [Usage](#usage)
- [Installing with Composer](#installing-with-composer)
- [Usage](#usage)
- [Supported CSS selectors](#supported-css-selectors)
- [Caveats](#caveats)
- [Maintainer](#maintainer)
- [Contributing](#contributing)



## How it Works

Emogrifier automagically transmogrifies your HTML by parsing your CSS and inserting your CSS definitions into tags
within your HTML based on your CSS selectors.


## Usage

First, you provide Emogrifier with the HTML and CSS you would like to merge. This can happen directly during
instantiation:

    $html = '<html><h1>Hello world!</h1></html>';
    $css = 'h1 {font-size: 32px;}';
    $emogrifier = new \Pelago\Emogrifier($html, $css);

You could also use the setters for providing this data after instantiation:

    $emogrifier = new \Pelago\Emogrifier();

    $html = '<html><h1>Hello world!</h1></html>';
    $css = 'h1 {font-size: 32px;}';

    $emogrifier->setHtml($html);
    $emogrifier->setCss($css);

After you have set the HTML and CSS, you can call the `emogrify` method to merge both:

    $mergedHtml = $emogrifier->emogrify();

## Options

There are several options that you can set on the Emogrifier object before 
calling the `emogrify` method:

* `$emogrifier->disableStyleBlocksParsing()` - By default, Emogrifier will grab
  all `<style>` blocks in the HTML and will apply the CSS styles as inline
  "style" attributes to the HTML. The `<style>` blocks will then be removed 
  from the HTML. If you want to disable this functionality so that Emogrifier
  leaves these `<style>` blocks in the HTML and does not parse them, you should
  use this option.
* `$emogrifier->disableInlineStylesParsing()` - By default, Emogrifier
  preserves all of the "style" attributes on tags in the HTML you pass to it.
  However if you want to discard all existing inline styles in the HTML before
  the CSS is applied, you should use this option.


## Installing with Composer

Download the [`composer.phar`](https://getcomposer.org/composer.phar) locally or install [Composer](https://getcomposer.org/) globally:

    curl -s https://getcomposer.org/installer | php

Run the following command for a local installation:

    php composer.phar require pelago/emogrifier:@dev

Or for a global installation, run the following command:

    composer require pelago/emogrifier:@dev

You can also add follow lines to your `composer.json` and run the `composer update` command:

    "require": {
      "pelago/emogrifier": "@dev"
    }

See https://getcomposer.org/ for more information and documentation.


## Supported CSS selectors

Emogrifier currently support the following [CSS selectors](http://www.w3.org/TR/CSS2/selector.html):

 * ID
 * class
 * type
 * descendant
 * child
 * adjacent
 * attribute presence
 * attribute value
 * attribute only

The following selectors are implemented, but currently are broken:

 * first-child (currently broken)
 * last-child (currently broken)

The following selectors are not implemented yet:

 * universal


## Caveats

* Emogrifier requires the HTML and the CSS to be UTF-8. Encodings like ISO8859-1 or ISO8859-15 are not supported.
* **NEW:** Emogrifier now preserves all valuable @media queries. Media queries can be very useful in
  responsive email design. See [media query support](https://litmus.com/help/email-clients/media-query-support/).
* **NEW:** Emogrifier will grab existing inline style attributes _and_ will grab `<style>` blocks from your HTML, but it
  will not grab CSS files referenced in <link> elements (the problem email clients are going to ignore these tags
  anyway, so why leave them in your HTML?).
* Even with styles inline, certain CSS properties are ignored by certain email clients. For more information,
  refer to these resources:
    * [http://www.email-standards.org/](http://www.email-standards.org/)
    * [https://www.campaignmonitor.com/css/](https://www.campaignmonitor.com/css/)
    * [http://templates.mailchimp.com/resources/email-client-css-support/](http://templates.mailchimp.com/resources/email-client-css-support/)
* All CSS attributes that apply to a node will be applied, even if they are redundant. For example, if you define a
  font attribute _and_ a font-size attribute, both attributes will be applied to that node (in other words, the more
  specific attribute will not be combined into the more general attribute).
* There's a good chance you might encounter problems if your HTML is not well formed and valid (DOMDocument might
  complain). If you get problems like this, consider running your HTML through [Tidy](http://php.net/manual/en/book.tidy.php)
  before you pass it to Emogrifier.
* Finally, Emogrifier only supports CSS1 level selectors and a few CSS2 level selectors (but not all of them). It
  does not support pseudo selectors (Emogrifier works by converting CSS selectors to XPath selectors, and pseudo
  selectors cannot be converted accurately).
* `!important` currently is not supported yet.


## Maintainer

Emogrifier is maintained by the good people at [Pelago](http://www.pelagodesign.com/), info AT pelagodesign DOT com.


## Contributing

Those that wish to contribute bug fixes, new features, refactorings and clean-up to Emogrifier are more than welcome.
When you contribute, please take the following things into account:

* Please cover all changes with unit tests and make sure that your code does not break any existing tests.
* To run the existing PHPUnit tests, run the following 
  command:
  
      composer install
  
  and then run this command:
  
      vendor/bin/phpunit Tests/
  
  If you want to uninstall the development packages that were installed when 
  you ran `composer install`, you can run this command:
  
      composer install --no-dev
* Please use the same coding style (PSR-2) as the rest of the code. Indentation is four spaces.
* Please make your code clean, well-readable and easy to understand.
* If you add new methods or fields, please use proper PHPDoc for the new methods/fields.
* Git commits should have a <= 50 character summary, optionally followed by a blank line
  and a more in depth description of 79 characters per line.
* [Please squash related commits together](http://gitready.com/advanced/2009/02/10/squashing-commits-with-rebase.html).
* Please use grammatically correct, complete sentences in the code documentation and the commit messages.
