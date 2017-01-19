/**
 * @category    mage.js
 * @package     test
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
