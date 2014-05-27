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

	var element = $("#loader").loader({
		icon: 'icon.gif',
		texts: {
			imgAlt: 'Image Text',
			loaderText: 'Loader Text'
		},
		template: '<div class="loading-mask" data-role="loader"><div class="loader"><img alt="{{imgAlt}}" src="{{icon}}"><p>{{loaderText}}</p></div></div>'
	});
	element.loader('show');
	equal( element.find('p').text(), 'Loader Text', '.loader() text matches' );
	equal( element.find('img').prop('src').split('/').pop(), 'icon.gif', '.loader() icons match' );
	equal( element.find('img').prop('alt'), 'Image Text', '.loader() image alt text matches' );
	element.loader('destroy');

});

TestCase( 'element init', function() {
	expect(1);

	//Initialize Loader on element
	var element = $("#loader").loader({
		icon: 'icon.gif',
		texts: {
			imgAlt: 'Image Text',
			loaderText: 'Loader Text'
		},
		template: '<div class="loading-mask" data-role="loader"><div class="loader"><img alt="{{imgAlt}}" src="{{icon}}"><p>{{loaderText}}</p></div></div>'
	});
	element.loader('show');
    equal(element.is(':mage-loader'), true, '.loader() init on element');
    element.remove();

});

TestCase( 'body init', function() {
	expect(1);

	//Initialize Loader on Body
	var body = $('body').loader();
    body.loader('show');
    equal(true, $('body div:first').is('.loading-mask'));
    $('body').find('.loading-mask:first').remove();

});

TestCase( 'show/hide', function() {
	expect(3);

	var element = $('body').loader();

	//Loader show
	element.loader('show');
	equal($('.loading-mask').is(':visible'), true, '.loader() open');

	//Loader hide
	element.loader('hide');
	equal($('.loading-mask').is( ":hidden" ), false, '.loader() closed' );

	//Loader hide on process complete
    element.loader('show');
    element.trigger('processStop');
    equal($('.loading-mask').is('visible'), false, '.loader() closed after process');

    element.find('.loading-mask').remove();

});

TestCase( 'destroy', function() {
	expect(1);

	var element = $("#loader").loader({
		icon: 'icon.gif',
		texts: {
			imgAlt: 'Image Text',
			loaderText: 'Loader Text'
		},
		template: '<div class="loading-mask" data-role="loader"><div class="loader"><img alt="{{imgAlt}}" src="{{icon}}"><p>{{loaderText}}</p></div></div>'
	});
	element.loader('show');
    element.loader('destroy');
    equal( $('.loading-mask').is(':visible'), false, '.loader() destroyed');

});