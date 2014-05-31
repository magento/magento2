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
TestCase('options', function() {
	expect(3);

	var element = $('<div>');
	element.appendTo('body');
	element.loader();
	element.loader('show');
	equal( element.find('p').text(), 'Please wait...', '.loader() text matches' );
	equal( element.find('img').prop('src').split('/').pop(), 'icon.gif', '.loader() icons match' );
	equal( element.find('img').prop('alt'), 'Loading...', '.loader() image alt text matches' );
	element.loader('destroy');

});

TestCase( 'element init', function() {
	expect(1);

	var element = $('<div>');
	element.appendTo('body');
	element.loader();
	element.loader('show');
    equal(element.is(':mage-loader'), true, '.loader() init on element');
    element.loader('destroy');

});

TestCase( 'body init', function() {
	expect(1);

	//Initialize Loader on Body
	var body = $('body').loader();
    body.loader('show');
    equal(body.is(':mage-loader'), true, '.loader() init on body');
    body.loader('destroy');
});

TestCase( 'show/hide', function() {
	expect(3);

	var element = $('<div>');
	element.appendTo('body');
	element.loader();

	//Loader show
	element.loader('show');
	equal($('.loading-mask').is(':visible'), true, '.loader() open');

	//Loader hide
	element.loader('hide');
	equal($('.loading-mask').is( ":hidden" ), true, '.loader() closed' );

	//Loader hide on process complete
    element.loader('show');
    element.trigger('processStop');
    equal($('.loading-mask').is('visible'), false, '.loader() closed after process');

    element.loader('destroy');

});

TestCase( 'destroy', function() {
	expect(1);

	var element = $("#loader").loader();
	element.loader('show');
    element.loader('destroy');
    equal( $('.loading-mask').is(':visible'), false, '.loader() destroyed');

});