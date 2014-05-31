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
 * @category    mage.js
 * @package     test
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*

 */
test( "Initialization", function() {
    expect(2);
    var accordion = $("<div></div>");
    accordion.accordion();
    ok( accordion.is(':mage-accordion'), "widget instantiated" );
    accordion.accordion('destroy');
    ok( !accordion.is(':mage-accordion'), "widget destroyed" );
});



test( "One-collapsible element", function() {
    expect(4);
    var accordion = $('<div></div>');
    var title1 = $('<div data-role="collapsible"></div>').appendTo(accordion);
    var content1 = $('<div data-role="content"></div>').appendTo(accordion);
    var title2 = $('<div data-role="collapsible"></div>').appendTo(accordion);
    var content2 = $('<div data-role="content"></div>').appendTo(accordion);
    accordion.appendTo("body");

    accordion.accordion();
    ok( content1.is(':visible'), "content visible" );
    ok( content2.is(':hidden'), "content hidden" );
    title2.trigger('click');
    ok( content1.is(':hidden'), "content hidden" );
    ok( content2.is(':visible'), "content visible" );
    accordion.accordion('destroy');

});

test( "Multi-collapsible elements", function() {
    expect(4);
    var accordion = $('<div></div>');
    var title1 = $('<div data-role="collapsible"></div>').appendTo(accordion);
    var content1 = $('<div data-role="content"></div>').appendTo(accordion);
    var title2 = $('<div data-role="collapsible"></div>').appendTo(accordion);
    var content2 = $('<div data-role="content"></div>').appendTo(accordion);
    accordion.appendTo("body");

    accordion.accordion({multipleCollapsible:true});
    ok( content1.is(':visible'), "content visible" );
    ok( content2.is(':hidden'), "content hidden" );
    title2.trigger('click');
    ok( content1.is(':visible'), "content visible" );
    ok( content2.is(':visible'), "content visible" );
    accordion.accordion('destroy');
});
