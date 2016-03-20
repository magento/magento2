/**
 * @category    mage.collapsible
 * @package     test
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*
    Test if the collapsible widget gets initialized when is called and destroy function works
 */
test('initialization & destroy', function() {
    expect(2);
    var group = $('<div id="1"></div>');
    group.collapsible();
    ok( group.is(':mage-collapsible'), "widget instantiated" );
    group.collapsible('destroy');
    ok( !group.is(':mage-collapsible'), "widget destroyed" );
});

/*
 Test enable, disable, activate, deactivate functions
 */
test('Enable, disable, activate, deactivate methods', function() {
    expect(5);
    var group = $('<div id="2"></div>');
    var title = $('<div data-role="title"></div>');
    var content = $('<div data-role="content"></div>');
    title.appendTo(group);
    content.appendTo(group);
    group.appendTo("body");
    group.collapsible();
    group.collapsible("deactivate");
    ok(content.is(':hidden'), "Content is collapsed");
    group.collapsible("activate");
    ok(content.is(':visible'), "Content is expanded");
    group.collapsible("disable");
    ok(content.is(':hidden'), "Content is collapsed");
    group.collapsible("activate");
    ok(content.is(':hidden'), "Content is collapsed");
    group.collapsible("enable");
    group.collapsible("activate");
    ok(content.is(':visible'), "Content is expanded");
    group.collapsible('destroy');
});

/*
    Test if the widget gets expanded/collapsed when the title is clicked
 */
test('Collapse and expand', function() {
    expect(3);
    var group = $('<div id="3"></div>');
    var title = $('<div data-role="title"></div>');
    var content = $('<div data-role="content"></div>');
    title.appendTo(group);
    content.appendTo(group);
    group.appendTo("body");
    group.collapsible();
    group.collapsible("deactivate");
    ok(content.is(':hidden'), "Content is collapsed");
    title.trigger("click");
    ok(content.is(':visible'), "Content gets expanded on click title");
    title.trigger("click");
    ok(content.is(':hidden'), "Content gets collapsed on click again");
    group.collapsible('destroy');
});


/*
 Test state Classes
 */
test('State classes', function() {
    expect(3);
    var group = $('<div id="4"></div>');
    var title = $('<div data-role="title"></div>');
    var content = $('<div data-role="content"></div>');
    title.appendTo(group);
    content.appendTo(group);
    group.collapsible({openedState:"opened", closedState:"closed", disabledState:"disabled"});
    ok( group.hasClass("closed"));
    title.trigger("click");
    ok( group.hasClass("opened"));
    group.collapsible("disable");
    ok( group.hasClass("disabled"));
    group.collapsible('destroy');
});

/*
    Test if icons are added to title when widget gets initialized and are removed when gets destroyed
 */
test('Create  & destroy icons', function() {
    expect(2);
    var group = $('<div id="5"></div>');
    var title = $('<div data-role="title"></div>');
    var content = $('<div data-role="content"></div>');
    title.appendTo(group);
    content.appendTo(group);
    group.collapsible({icons: {header:"minus",activeHeader:"plus"}});
    ok(title.children("[data-role=icons]").length, "Icons added to title" );
    group.collapsible('destroy');
    ok(!title.children("[data-role=icons]").length, "Icons removed from title" );
});

/*
    Test if icon classes are changed when content gets expanded/collapsed
 */
test('Change icons when content gets expanded/collapsed', function() {
    expect(2);
    var group = $('<div id="6"></div>');
    var title = $('<div data-role="title"></div>');
    var content = $('<div data-role="content"></div>');
    title.appendTo(group);
    content.appendTo(group);
    group.collapsible({icons: {header:"minus",activeHeader:"plus"}});
    group.collapsible("deactivate");
    var icons = group.collapsible("option","icons");
    ok(title.children("[data-role=icons]").hasClass(icons.header), "When content is collapsed,header has the right class for icons" );
    title.trigger("click");
    ok(title.children("[data-role=icons]").hasClass(icons.activeHeader), "When content is expanded,header has the right class for icons" );
    group.collapsible('destroy');
});


/*
    Test if content gets expanded/collapsed when certain keys are pressed
 */
asyncTest( "keyboard support", function() {

    expect( 5 );
    var group = $('<div id="7"></div>');
    var title = $('<div data-role="title"></div>');
    var content = $('<div data-role="content"></div>');
    title.appendTo(group);
    content.appendTo(group);
    group.appendTo("body");
    group.collapsible();
    group.collapsible("deactivate");

    title.on("focus",function(ev){
        ok(content.is(':hidden'), "Content is collapsed");
        title.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.ENTER } ));
        ok(content.is(':visible'), "Content is expanded");
        title.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.ENTER } ));
        ok(content.is(':hidden'), "Content is collapsed");
        title.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.SPACE } ));
        ok(content.is(':visible'), "Content is expanded");
        title.trigger($.Event( 'keydown', { keyCode: $.ui.keyCode.SPACE } ));
        ok(content.is(':hidden'), "Content is collapsed");
        group.collapsible('destroy');
        start();
    } );
    
    setTimeout(function(){
        title.focus();
    },10);

});

/*
    Test if content gets updated via Ajax when title is clicked
 */
test('Update content via ajax', function() {
    expect(2);
    var group = $('<div id="8"></div>');
    var title = $('<div data-role="title"></div>');
    var content = $('<div data-role="content"></div>');
    var ajax = $('<a data-ajax="true" href="testsuite/mage/collapsible/content.html"></a>');
    title.appendTo(group);
    content.appendTo(group);
    ajax.appendTo(content);
    group.appendTo("body");
    group.collapsible({ajaxContent : true});
    group.collapsible("deactivate");
    ok(!content.children("p").length, "Content has no data");
    title.trigger("click");
    ok(content.children("p"), "Content gets data from content.html");
    group.collapsible('destroy');
});



