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
    Set key logger to check key press event 
 */
 function KeyLogger( target ) {
  if ( !(this instanceof KeyLogger) ) {
    return new KeyLogger( target );
  }
  this.target = target;
  this.log = [];
 
  var self = this;

  this.target.off( 'keydown' ).on( 'keydown', function( event ) {
    self.log.push( event.keyCode );
  });
}
/*
    testing if menu get expanded class when option set to true
 */
test( 'Menu Expanded', function() {
    expect(1);
    var menu = $('#menu');
    var menuItems = menu.find('li');
    var submenu = menuItems.find('ul');
    menu.menu({
        expanded: true
    });
    ok(submenu.hasClass('expanded'), 'Expanded Class added');
});
/*
    testing if down arrow is pressed
 */
test( 'Down Arrow', function() {
    expect(1);
    var event,
        menu = $('#menu'),
        keys = KeyLogger(menu);
    event = $.Event('keydown');
    event.keyCode = $.ui.keyCode.DOWN;
    menu.trigger( event );
    equal( keys.log[ 0 ], 40, 'Down Arrow Was Pressed' );
});
/*
    testing if up arrow is pressed
 */
test( 'Up Arrow', function() {
    expect(1);
    var event,
        menu = $('#menu'),
        keys = KeyLogger(menu);
    event = $.Event('keydown');
    event.keyCode = $.ui.keyCode.UP;
    menu.trigger( event );
    equal( keys.log[ 0 ], 38, 'Up Arrow Was Pressed' );
});
/*
    testing if left arrow is pressed
 */
test( 'Left Arrow', function() {
    expect(1);
    var event,
        menu = $('#menu'),
        keys = KeyLogger(menu);
    event = $.Event('keydown');
    event.keyCode = $.ui.keyCode.LEFT;
    menu.trigger( event );
    equal( keys.log[ 0 ], 37, 'Left Arrow Was Pressed' );
});
/*
    testing if right arrow is pressed
 */
test( 'Right Arrow', function() {
    expect(1);
    var event,
        menu = $('#menu'),
        keys = KeyLogger(menu);
    event = $.Event('keydown');
    event.keyCode = $.ui.keyCode.RIGHT;
    menu.trigger( event );
    equal( keys.log[ 0 ], 39, 'Right Arrow Was Pressed' );
});
/*
    testing if max limit being set
 */
test( 'Max Limit', function() {
    expect(1);
    var menu = $('#menu');
    menu.navigation({
        maxItems: 3
    });
    var menuItems = menu.find('> li:visible');
    equal(menuItems.length, 4, 'Max Limit Reach');
});
/*
    testing if responsive menu is set
 */
test( 'Responsive: More Menu', function() {
    expect(1);
    var menu = $('#menu');
    menu.navigation({
        responsive: 'onResize'
    });
    ok($('body').find('.ui-menu.more'), 'More Menu Created');
});

