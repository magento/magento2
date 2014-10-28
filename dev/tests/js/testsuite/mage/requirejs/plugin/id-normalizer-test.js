/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint globalstrict: true*/

"use strict";

/*jshint undef: false, newcap: false*/
var IdNormalizerTest = TestCase('IdNormalizerTest');

IdNormalizerTest.prototype.setUp = function() {
    var defineArgs = jsunit.requirejsUtil.getDefineArgsInScript('lib/web/mage/requirejs/plugin/id-normalizer.js');
    assertNotUndefined('There expected to be a define() call', defineArgs);
    assertEquals('Wrong number of arguments in the define() call', 1, defineArgs.length);

    this.normalizer = defineArgs[0];
    assertObject(this.normalizer);
    assertFunction(this.normalizer.normalize);
    assertFunction(this.normalizer.load);
};

IdNormalizerTest.prototype.testNormalize = function () {
    var actual = this.normalizer.normalize('Magento_Catalog::foo/bar.js');
    assertEquals('Magento_Catalog/foo/bar.js', actual);
};

IdNormalizerTest.prototype.testLoad = function () {
    // Check that load() is just a proxy
    var modulePassed, onloadPassed;
    var parentRequire = function (moduleIn, onloadIn) {
        modulePassed = moduleIn;
        onloadPassed = onloadIn;
    };
    var onload = function (){};

    this.normalizer.load('module', parentRequire, onload);

    assertEquals('module', modulePassed);
    assertSame(onload, onloadPassed);
};

