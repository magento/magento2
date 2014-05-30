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

/*
    testing if dialog opens when the triggerEvent is triggered
 */
test( "triggerEvent", function() {
    expect(2);
    var opener = $('<div></div>');
    var dialog = $('<div></div>');
    dialog.dropdownDialog({"triggerEvent":"click", "triggerTarget":opener});
    opener.trigger("click");
    equal(dialog.dropdownDialog("isOpen"), true, "Dropdown opens when click opener");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"triggerEvent":null, "triggerTarget":opener});
    opener.trigger("click");
    equal(dialog.dropdownDialog("isOpen"), false, "Dropdown doesn't open when click opener");
    dialog.dropdownDialog( "destroy" );

});

/*
    testing if a specified class is added to the trigger
 */
test( "triggerClass", function() {
    expect(2);
    var opener = $('<div></div>');
    var dialog = $('<div></div>');
    dialog.dropdownDialog({"triggerTarget":opener,"triggerClass":"active"});
    dialog.dropdownDialog("open");
    ok( opener.hasClass("active"), "Class added to opener when dialog opens" );
    dialog.dropdownDialog("close");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"triggerEvent":opener, "triggerClass":null});
    dialog.dropdownDialog("open");
    ok( !opener.hasClass("active"), "Class added to opener when dialog opens" );
    dialog.dropdownDialog("close");
    dialog.dropdownDialog( "destroy" );

});

/*
    testing if a specified class is added to the element which the dialog appends to
 */
test( "parentClass", function() {
    expect(2);
    var parent = $('<div></div>');
    var dialog = $('<div></div>');

    dialog.dropdownDialog({"parentClass":"active","appendTo":parent});
    dialog.dropdownDialog("open");
    ok( parent.hasClass("active"), "Class is added to parent when dialog opens" );
    dialog.dropdownDialog("close");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"parentClass":null,"appendTo":parent});
    dialog.dropdownDialog("open");
    ok( !parent.hasClass("active"), "No class is added to parent when dialog opens" );
    dialog.dropdownDialog("close");
    dialog.dropdownDialog( "destroy" );

});

/*
    testing if a specified class is added to the element that becomes dialog
 */
test( "dialogContentClass", function() {
    expect(2);
    var dialog = $('<div></div>');

    dialog.dropdownDialog({"dialogContentClass":"active"});
    dialog.dropdownDialog("open");
    ok( $('.ui-dialog-content').hasClass("active"), "Class is added to dialog content when dialog opens" );
    dialog.dropdownDialog("close");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"dialogContentClass": null});
    dialog.dropdownDialog("open");
    ok( !$('.ui-dialog-content').hasClass("active"), "No class is added to dialog content" );
    dialog.dropdownDialog("close");
    dialog.dropdownDialog( "destroy" );
});

/*
 testing if a specified class is added to dialog
 */
test( "defaultDialogClass", function() {
    expect(3);
    var dialog = $('<div></div>');

    dialog.dropdownDialog({"defaultDialogClass":"custom"});
    ok( $('.ui-dialog').hasClass("custom"), "Class is added to dialog" );
    ok( !$('.ui-dialog').hasClass("mage-dropdown-dialog"), "Default class has been overwritten" );
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({});
    ok( $('.ui-dialog').hasClass("mage-dropdown-dialog"), "Default class hasn't been overwritten" );
    dialog.dropdownDialog( "destroy" );
});

/*
    testing if the specified trigger actually opens the dialog
 */
test( "triggerTarget", function() {
    expect(2);
    var opener = $('<div></div>');
    var dialog = $('<div></div>');

    dialog.dropdownDialog({"triggerTarget":opener});
    opener.trigger("click");
    equal(dialog.dropdownDialog("isOpen"), true, "Dropdown opens when click opener");
    dialog.dropdownDialog("close");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"triggerTarget":null});
    opener.trigger("click");
    equal(dialog.dropdownDialog("isOpen"), false, "Dropdown doesn't open when click opener");
    dialog.dropdownDialog( "destroy" );
});

/*
    testing if the dialog gets closed when clicking outside of it
 */
test( "closeOnClickOutside", function() {
    expect(2);
    var outside = $('<div></div>').attr({"id":"outside"});
    var dialog = $('<div></div>').attr({"id":"dialog"});
    outside.appendTo("#qunit-fixture");
    dialog.appendTo("#qunit-fixture");

    dialog.dropdownDialog({"closeOnClickOutside":true});
    dialog.dropdownDialog("open");
    outside.trigger("click");
    equal(dialog.dropdownDialog("isOpen"), false, "Dropdown closes when click outside dropdown");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"closeOnClickOutside":false});
    dialog.dropdownDialog("open");
    outside.trigger("click");
    equal(dialog.dropdownDialog("isOpen"), true, "Dropdown doesn't close when click outside dropdown");
    dialog.dropdownDialog( "destroy" );
});

/*
    testing if the dialog gets closed when mouse leaves the dialog area
 */
asyncTest( "closeOnMouseLeave true", function() {
    expect(1);
    var outside = $('<div></div>').attr({"id":"outside"});
    var dialog = $('<div></div>').attr({"id":"dialog"});
    var opener = $('<div></div>').attr({"id":"opener"});
    outside.appendTo("#qunit-fixture");
    dialog.appendTo("#qunit-fixture");
    opener.appendTo("#qunit-fixture");

    dialog.dropdownDialog({"closeOnMouseLeave":true});
    dialog.dropdownDialog("open");
    dialog.trigger("mouseenter");
    dialog.trigger("mouseleave");

    setTimeout(function() {
        equal(dialog.dropdownDialog("isOpen"), false, "Dropdown closes when mouseleave the dropdown area");
        dialog.dropdownDialog( "destroy" );
        start();
    }, 3000);

});

/*
 testing if the dialog gets closed when mouse leaves the dialog area
 */
asyncTest( "closeOnMouseLeave false", function() {
    expect(1);
    var outside = $('<div></div>').attr({"id":"outside"});
    var dialog = $('<div></div>').attr({"id":"dialog"});
    var opener = $('<div></div>').attr({"id":"opener"});
    outside.appendTo("#qunit-fixture");
    dialog.appendTo("#qunit-fixture");
    opener.appendTo("#qunit-fixture");

    dialog.dropdownDialog({"closeOnMouseLeave":false});
    dialog.dropdownDialog("open");
    dialog.trigger("mouseenter");
    dialog.trigger("mouseleave");

    setTimeout(function() {
        equal(dialog.dropdownDialog("isOpen"), true, "Dropdown doesn't close when mouseleave the dropdown area");
        dialog.dropdownDialog( "destroy" );
        start();
    }, 3000);

});

/*
    testing if the dialog gets closed with the specified delay
 */
asyncTest( "timeout", function() {
    expect(2);
    var outside = $('<div></div>').attr({"id":"outside"});
    var dialog = $('<div></div>').attr({"id":"dialog"});
    var opener = $('<div></div>').attr({"id":"opener"});
    outside.appendTo("#qunit-fixture");
    dialog.appendTo("#qunit-fixture");
    opener.appendTo("#qunit-fixture");

    dialog.dropdownDialog({"timeout":2000});
    dialog.dropdownDialog("open");
    dialog.trigger("mouseenter");
    dialog.trigger("mouseleave");
    equal(dialog.dropdownDialog("isOpen"), true, "Dropdown doesn't close when mouseleave the dropdown area");
    setTimeout(function() {
        equal(dialog.dropdownDialog("isOpen"), false, "Dropdown closes when mouseleave the dropdown area, after timeout passed");
        dialog.dropdownDialog( "destroy" );
        start();
    }, 3000);

});

/*
    testing if the title bar is prevented from being created
 */
test( "createTitileBar", function() {
    expect(2);
    var dialog = $('<div></div>');
    dialog.dropdownDialog({"createTitleBar":true});
    ok(($(".ui-dialog").find(".ui-dialog-titlebar").length > 0), "Title bar is created");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"createTitleBar":false});
    ok($(".ui-dialog").find(".ui-dialog-titlebar").length <= 0, "Title bar isn't created");
    dialog.dropdownDialog( "destroy" );
});

/*
    testing if the position function gets disabled
 */
test( "autoPosition", function() {
    expect(2);
    var dialog = $('<div></div>');
    dialog.dropdownDialog({"autoPosition":false});
    dialog.dropdownDialog("open");
    ok(($(".ui-dialog").css("top") === 'auto'), "_position function disabled");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"autoPosition":true});
    dialog.dropdownDialog("open");
    ok(($(".ui-dialog").css("top") !== '0px'), "_position function enabled");
    dialog.dropdownDialog( "destroy" );
});

/*
    testing if the size function gets disabled
 */
test( "autoSize", function() {
    expect(2);
    var dialog = $('<div></div>');
    dialog.dropdownDialog({"autoSize":true, width:"300"});
    dialog.dropdownDialog("open");
    ok(($(".ui-dialog").css("width") === '300px'), "_size function enabled");
    dialog.dropdownDialog( "destroy" );

    dialog.dropdownDialog({"autoSize":false, width:"300"});
    dialog.dropdownDialog("open");
    ok($(".ui-dialog").css("width") !== '300px', "_size function disabled");
    dialog.dropdownDialog( "destroy" );
});
