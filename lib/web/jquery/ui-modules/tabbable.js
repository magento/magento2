/*!
 * jQuery UI Tabbable 1.13.0
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */

//>>label: :tabbable Selector
//>>group: Core
//>>description: Selects elements which can be tabbed to.
//>>docs: http://api.jqueryui.com/tabbable-selector/

( function( factory ) {
	"use strict";

	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "jquery", "./version", "./focusable" ], factory );
	} else {

		// Browser globals
		factory( jQuery );
	}
} )( function( $ ) {
"use strict";

return $.extend( $.expr.pseudos, {
	tabbable: function( element ) {
		var tabIndex = $.attr( element, "tabindex" ),
			hasTabindex = tabIndex != null;
		return ( !hasTabindex || tabIndex >= 0 ) && $.ui.focusable( element, hasTabindex );
	}
} );

} );
