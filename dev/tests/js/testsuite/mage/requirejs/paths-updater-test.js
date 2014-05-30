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
var PathsUpdaterTest = TestCase('PathsUpdaterTest');

PathsUpdaterTest.prototype.testUpdateConfigPaths = function() {
    var config = {paths: { simplePath: "simple/path.js", relativePath: "./relative/path.js" }};
    var expected = {paths: { simplePath: "simple/path.js", relativePath: "relative/path.js" }};
    assertEquals(expected, mageUpdateConfigPaths(config, ''));
};

PathsUpdaterTest.prototype.testUpdateConfigPathsWithContext = function() {
    var config = {paths: { simplePath: "simple/path.js", relativePath: "./relative/path.js" }};
    var context = 'context';
    var expectedWithContext = {paths: { simplePath: "simple/path.js", relativePath: "context/relative/path.js" }};
    assertEquals(expectedWithContext, mageUpdateConfigPaths(config, context));
};
