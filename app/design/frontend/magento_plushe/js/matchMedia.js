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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas. Dual MIT/BSD license */

window.matchMedia = window.matchMedia || (function( doc, undefined ) {

  "use strict";

  var bool,
      docElem = doc.documentElement,
      refNode = docElem.firstElementChild || docElem.firstChild,
      // fakeBody required for <FF4 when executed in <head>
      fakeBody = doc.createElement( "body" ),
      div = doc.createElement( "div" );

  div.id = "mq-test-1";
  div.style.cssText = "position:absolute;top:-100em";
  fakeBody.style.background = "none";
  fakeBody.appendChild(div);

  return function(q){

    div.innerHTML = "&shy;<style media=\"" + q + "\"> #mq-test-1 { width: 42px; }</style>";

    docElem.insertBefore( fakeBody, refNode );
    bool = div.offsetWidth === 42;
    docElem.removeChild( fakeBody );

    return {
      matches: bool,
      media: q
    };

  };

}( document ));

/*! matchMedia() polyfill addListener/removeListener extension. Author & copyright (c) 2012: Scott Jehl. Dual MIT/BSD license */
(function(){
  // monkeypatch unsupported addListener/removeListener with polling
  if( !window.matchMedia( "" ).addListener ){
    var oldMM = window.matchMedia;
    
    window.matchMedia = function( q ){
      var ret = oldMM( q ),
        listeners = [],
        last = false,
        timer,
        check = function(){
          var list = oldMM( q ),
            unmatchToMatch = list.matches && !last,
            matchToUnmatch = !list.matches && last;
                                                
                                        //fire callbacks only if transitioning to or from matched state
          if( unmatchToMatch || matchToUnmatch ){
            for( var i =0, il = listeners.length; i< il; i++ ){
              listeners[ i ].call( ret, list );
            }
          }
          last = list.matches;
        };
      
      ret.addListener = function( cb ){
        listeners.push( cb );
        if( !timer ){
          timer = setInterval( check, 1000 );
        }
      };

      ret.removeListener = function( cb ){
        for( var i =0, il = listeners.length; i< il; i++ ){
          if( listeners[ i ] === cb ){
            listeners.splice( i, 1 );
          }
        }
        if( !listeners.length && timer ){
          clearInterval( timer );
        }
      };
      
      return ret;
    };
  }
}());

var mediaCheck = function( options ) {
  var mq,
      matchMedia = (window.matchMedia !== undefined & window.matchMedia('').addListener !== undefined);
      
  mqChange = function( mq, options ) {
    if ( mq.matches ) {
      if ( typeof options.entry === "function" ) {
        options.entry();
      }
    } else if ( typeof options.exit === "function" ) {
      options.exit();
    }
  };
  
  if ( matchMedia ) {
    // Has matchMedia support
    createListener = function() {

      mq = window.matchMedia( options.media );
      mq.addListener( function() {
        mqChange( mq, options );
      });
      mqChange( mq, options );
    };
    createListener();
    
  } else {
    // capture the current pageWidth
    var pageWidth = window.outerWidth;

    // No matchMedia support
    var mmListener = function() {
      var parts = options.media.match( /\((.*)-.*:\s*(.*)\)/ ),
          constraint = parts[ 1 ],
          value = parseInt( parts[ 2 ], 10 ),
          fakeMatchMedia = {};

      // scope this to width changes to prevent small-screen scrolling (browser chrome off-screen)
      //   from triggering a change
      if (pageWidth != window.outerWidth) {
        fakeMatchMedia.matches = constraint === "max" && value > window.outerWidth ||
                                 constraint === "min" && value < window.outerWidth;
        mqChange( fakeMatchMedia, options );
        
        // reset pageWidth
        pageWidth = window.outerWidth;
      }
    };

    if (window.addEventListener) {
      window.addEventListener("resize", mmListener);
    } else if (window.attachEvent) {
      window.attachEvent("resize", mmListener);
    }
    mmListener();
  }
};
