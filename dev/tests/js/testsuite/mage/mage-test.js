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
MageTest = TestCase('MageTest');

MageTest.prototype.setUp = function() {
    /*:DOC += <button id="save"></button>*/
};

MageTest.prototype.tearDown = function() {
    jQuery(jQuery.mage).off('buttoninit');
    jQuery('body').off('contentUpdated');
    jQuery.mage
        .component('tabs', null)
        .component('baseButton', null)
        .component('button', null);
};

MageTest.prototype.testInit = function() {
    /*:DOC += <button id="reset"></button>*/
    jQuery.mage.component('button', []);
    jQuery('button').mage('button');
    assertTrue(!!jQuery('#save').data('button'));
    assertTrue(!!jQuery('#reset').data('button'));
};

MageTest.prototype.testCustomizeOptions = function() {
    /*:DOC += <button id="reset"></button>*/
    jQuery.mage
        .component('button', [])
        .onInit('button', '#save', function(options) {
            options.disabled = false;
        });
    jQuery('button').mage('button', {disabled: true});
    assertTrue(!jQuery('#save').is(':disabled'));
    assertTrue(jQuery('#reset').is(':disabled'));
};

MageTest.prototype.testDataAttribute = function() {
    /*:DOC += <div id="main" data-mage-init='{"button":[], "tabs":[]}'></div>*/
    jQuery.mage
        .component('button', [])
        .component('tabs', [])
        .init();
    assertTrue(!!jQuery('#main').data('button'));
    assertTrue(!!jQuery('#main').data('tabs'));
};

MageTest.prototype.testRejectComponent = function() {
    jQuery.mage
        .component('button', [])
        .onInit('button', function() {
            this.name = null;
        });
    jQuery('button').mage('button');
    assertTrue(!jQuery('#save').data('button'));
};

MageTest.prototype.testSubstituteComponent = function() {
    jQuery.mage
        .component('button', [])
        .component('tabs', [])
        .onInit('button', function() {
            this.name = 'tabs';
        });
    jQuery('button').mage('button');
    assertTrue(!jQuery('#save').data('button'));
    assertTrue(!!jQuery('#save').data('tabs'));
};

MageTest.prototype.testAddComponent = function() {
    expectAsserts(4);
    var resources = ['test1.js', 'test2.js'];
    jQuery.mage
        .component('button', resources)
        .onInit('button', '#save', function() {
            assertNotSame(resources, this.resources);
            assertEquals(resources.length, this.resources.length);
            jQuery.each(resources, jQuery.proxy(function(i, resource) {
                assertEquals(resource, this.resources[i]);
            }, this));
            this.name = null;
        });
    jQuery('button').mage('button');
};

MageTest.prototype.testExtendComponent = function() {
    expectAsserts(6);
    var baseButtonResources = ['test1.js', 'test2.js'],
        buttonResources = ['test3.js', 'test4.js'],
        resources = [];
    jQuery.merge(resources, baseButtonResources);
    jQuery.merge(resources, buttonResources);
    jQuery.mage
        .component('baseButton', baseButtonResources)
        .extend('button', 'baseButton', buttonResources)
        .onInit('button', '#save', function() {
            assertNotSame(resources, this.resources);
            assertEquals(resources.length, this.resources.length);
            jQuery.each(resources, jQuery.proxy(function(i, resource) {
                assertEquals(resource, this.resources[i]);
            }, this));
            this.name = null;
        });
    jQuery('button').mage('button');
};

MageTest.prototype.testContentUpdated = function() {
    jQuery.mage
        .component('tabs', [])
        .component('button', [])
        .init();
    jQuery('body')
        .append(
            jQuery('<div>')
                .attr('id', 'test')
                .attr('data-mage-init', '{"tabs":[]}')
                .append(jQuery('<button>').attr('data-mage-init', '{"button":[]}'))
        );
    jQuery('#test').trigger('contentUpdated');
    assertTrue(!!jQuery('#test').data('tabs'));
    assertTrue(!!jQuery('#test button').data('button'));
};

MageTest.prototype.testArgumentsModification = function() {
    expectAsserts(3);
    var expected = ['some string', 5, true];
    jQuery.fn.testPlugin = function() {
        jQuery.each(arguments, function(i) {
            assertEquals(expected[i], this);
        });
        return this;
    };
    jQuery.mage
        .component('testPlugin', [])
        .onInit('testPlugin', function() {
            for (var i = this.args.length - 1; i >= 0; i--) {
                this.args[i] = expected[i];
            }
        });
    jQuery('#save').mage('testPlugin', 'test', 3, false);
    // teardown
    jQuery(jQuery.mage).off('testPlugininit');
    jQuery.mage.component('testPlugin', null);
};
