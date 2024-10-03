/*!
 * jQuery UI Support for jQuery core 1.8.x and newer 1.13.2
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 */

//>>label: jQuery 1.8+ Support
//>>group: Core
//>>description: Support version 1.8.x and newer of jQuery core

( function( factory ) {
    "use strict";

    if ( typeof define === "function" && define.amd ) {

        // AMD. Register as an anonymous module.
        define( [ "jquery", "./version" ], factory );
    } else {

        // Browser globals
        factory( jQuery );
    }
} )( function( $ ) {
    "use strict";

// Support: jQuery 1.9.x or older
// $.expr[ ":" ] is deprecated.
    if ( !$.expr.pseudos ) {
        $.expr.pseudos = $.expr[ ":" ];
    }

// Support: jQuery 1.11.x or older
// $.unique has been renamed to $.uniqueSort
    if ( !$.uniqueSort ) {
        $.uniqueSort = $.unique;
    }

// Support: jQuery 2.2.x or older.
// This method has been defined in jQuery 3.0.0.
// Code from https://github.com/jquery/jquery/blob/e539bac79e666bba95bba86d690b4e609dca2286/src/selector/escapeSelector.js
    if ( !$.escapeSelector ) {

        // CSS string/identifier serialization
        // https://drafts.csswg.org/cssom/#common-serializing-idioms
        var rcssescape = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;

        var fcssescape = function( ch, asCodePoint ) {
            if ( asCodePoint ) {

                // U+0000 NULL becomes U+FFFD REPLACEMENT CHARACTER
                if ( ch === "\0" ) {
                    return "\uFFFD";
                }

                // Control characters and (dependent upon position) numbers get escaped as code points
                return ch.slice( 0, -1 ) + "\\" + ch.charCodeAt( ch.length - 1 ).toString( 16 ) + " ";
            }

            // Other potentially-special ASCII characters get backslash-escaped
            return "\\" + ch;
        };

        $.escapeSelector = function( sel ) {
            return ( sel + "" ).replace( rcssescape, fcssescape );
        };
    }

// Support: jQuery 3.4.x or older
// These methods have been defined in jQuery 3.5.0.
    if ( !$.fn.even || !$.fn.odd ) {
        $.fn.extend( {
            even: function() {
                return this.filter( function( i ) {
                    return i % 2 === 0;
                } );
            },
            odd: function() {
                return this.filter( function( i ) {
                    return i % 2 === 1;
                } );
            }
        } );
    }

} );
