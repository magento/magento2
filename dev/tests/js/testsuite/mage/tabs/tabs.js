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
    var tabs = $("<div></div>");
    tabs.tabs();
    ok( tabs.is(':mage-tabs'), "widget instantiated" );
    tabs.tabs('destroy');
    ok( !tabs.is(':mage-tabs'), "widget destroyed" );
});

test( "Collapsible instantiation", function() {
    expect(2);
    var tabs = $("<div></div>");
    var title = $("<div></div>").attr("data-role","collapsible");
    title.appendTo(tabs);
    tabs.tabs();
    ok( title.is(':mage-collapsible'), "widget instantiated" );
    tabs.tabs('destroy');
    ok( !title.is(':mage-collapsible'), "widget destroyed" );
});

test( "Tabs behavior - closing others tabs when one gets activated", function() {
    expect(4);
    var tabs = $('<div></div>');
    var title1 = $('<div data-role="collapsible"></div>').appendTo(tabs);
    var content1 = $('<div data-role="content"></div>').appendTo(tabs);
    var title2 = $('<div data-role="collapsible"></div>').appendTo(tabs);
    var content2 = $('<div data-role="content"></div>').appendTo(tabs);
    tabs.appendTo("body");
    tabs.tabs();
    ok( content1.is(':visible'), "content visible" );
    ok( content2.is(':hidden'), "content hidden" );
    title2.trigger('click');
    ok( content1.is(':hidden'), "content hidden" );
    ok( content2.is(':visible'), "content visible" );
    tabs.tabs('destroy');
});

test( "Testing enable,disable,activate,deactivate options", function() {
    expect(6);
    var tabs = $('<div></div>');
    var title = $('<div data-role="collapsible"></div>').appendTo(tabs);
    var content = $('<div data-role="content"></div>').appendTo(tabs);
    tabs.appendTo("body");
    tabs.tabs();
    ok( content.is(':visible'), "content visible" );
    tabs.tabs("deactivate",0);
    ok( content.is(':hidden'), "content hidden" );
    tabs.tabs("activate",0);
    ok( content.is(':visible'), "content visible" );
    tabs.tabs("disable",0);
    ok( content.is(':hidden'), "content hidden" );
    title.trigger("click");
    ok( content.is(':hidden'), "content hidden" );
    tabs.tabs("enable",0);
    title.trigger("click");
    ok( content.is(':visible'), "content visible" );
    tabs.tabs('destroy');
});

asyncTest( "Keyboard support for tabs view", function() {

    expect( 5 );
    var tabs = $('<div></div>');
    var title1 = $('<div data-role="collapsible"></div>').appendTo(tabs);
    var content1 = $('<div data-role="content"></div>').appendTo(tabs);
    var title2 = $('<div data-role="collapsible"></div>').appendTo(tabs);
    var content2 = $('<div data-role="content"></div>').appendTo(tabs);
    tabs.appendTo("body");
    tabs.tabs();

    title1.on("focus",function(ev){
        ok(content1.is(':visible'), "Content is expanded");
        title1.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.RIGHT } ));
        ok(content2.is(':visible'), "Content is expanded");
        ok(content1.is(':hidden'), "Content is collapsed");
        title2.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.LEFT } ));
        ok(content1.is(':visible'), "Content is expanded");
        ok(content2.is(':hidden'), "Content is collapsed");
        tabs.tabs('destroy');
        start();
    } );

    setTimeout(function(){
        title1.focus();
    },10);
});

asyncTest( "Keyboard support for accordion view", function() {

    expect( 5 );
    var tabs = $('<div></div>');
    var title1 = $('<div data-role="collapsible"></div>').appendTo(tabs);
    var content1 = $('<div data-role="content"></div>').appendTo(tabs);
    var title2 = $('<div data-role="collapsible"></div>').appendTo(tabs);
    var content2 = $('<div data-role="content"></div>').appendTo(tabs);
    tabs.appendTo("body");
    tabs.tabs({openOnFocus:false});

    title1.on("focus",function(ev){
        ok(content1.is(':visible'), "Content is expanded");
        title1.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.RIGHT } ));
        ok(content1.is(':visible'), "Content is expanded");
        ok(content2.is(':hidden'), "Content is collapsed");
        title2.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.ENTER } ));
        ok(content2.is(':visible'), "Content is expanded");
        ok(content1.is(':hidden'), "Content is collapsed");
        tabs.tabs('destroy');
        start();
    } );

    setTimeout(function(){
        title1.focus();
    },10);
});
