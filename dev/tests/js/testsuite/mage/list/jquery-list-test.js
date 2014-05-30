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
 * @category    mage.loader
 * @package     test
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
