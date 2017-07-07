/**
 * @category    mage.loader
 * @package     test
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
test('init & destroy', function() {
    expect(2);
    var element = $('<div></div>');
    element.list();
    ok(element.hasClass('list-widget'), "Class added" );
    element.list('destroy');
    ok(!element.hasClass('list-widget'), "Class removed" );
});

test('add to list', function() {
    expect(1);
    var element = $('<div></div>');
    var button = $('<button data-button="add"></button>');
    button.appendTo(element);
    element.appendTo('body');
    var destination = $('<div data-role="container"></div>');
    destination.appendTo('body');
    element.list({
        template : '<span>test</span>',
        templateWrapper : '<fieldset data-role="item"></fieldset>',
        templateClass : 'fieldset'
    });
    button.trigger('click');
    ok(destination.children('[data-role="addedItem"]').length, "Content is added to list");
    element.list('destroy');
    element.remove();
    destination.remove();
});

test('remove from list', function() {
    expect(1);
    var button = $('<button></button>');
    var removeButton = $('[data-button=remove]');
    var destination = $('<div id="test"></div>');
    button.list({template: '#template',destinationSelector: '#test',listLimit: 5});
    button.trigger('click');
    removeButton.trigger('click');
    ok(!destination.children('[data-role=item]').length, "Content is removed from the list");
    button.list('destroy');
});
