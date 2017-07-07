/**
 * @category    mage.js
 * @package     test
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

