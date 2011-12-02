/* prevent execution of jQuery if included more than once */
if(typeof window.jQuery == "undefined") {
/*
 * jQuery 1.1.1 - New Wave Javascript
 *
 * Copyright (c) 2007 John Resig (jquery.com)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * $Date: 2007-01-22 00:27:54 -0500 (Mon, 22 Jan 2007) $
 * $Rev: 1153 $
 */

// Global undefined variable
window.undefined = window.undefined;
var jQuery = function(a,c) {
	// If the context is global, return a new object
	if ( window == this )
		return new jQuery(a,c);

	// Make sure that a selection was provided
	a = a || document;
	
	// HANDLE: $(function)
	// Shortcut for document ready
	if ( jQuery.isFunction(a) )
		return new jQuery(document)[ jQuery.fn.ready ? "ready" : "load" ]( a );
	
	// Handle HTML strings
	if ( typeof a  == "string" ) {
		// HANDLE: $(html) -> $(array)
		var m = /^[^<]*(<(.|\n)+>)[^>]*$/.exec(a);
		if ( m )
			a = jQuery.clean( [ m[1] ] );
		
		// HANDLE: $(expr)
		else
			return new jQuery( c ).find( a );
	}
	
	return this.setArray(
		// HANDLE: $(array)
		a.constructor == Array && a ||

		// HANDLE: $(arraylike)
		// Watch for when an array-like object is passed as the selector
		(a.jquery || a.length && a != window && !a.nodeType && a[0] != undefined && a[0].nodeType) && jQuery.makeArray( a ) ||

		// HANDLE: $(*)
		[ a ] );
};

// Map over the $ in case of overwrite
if ( typeof $ != "undefined" )
	jQuery._$ = $;
	
// Map the jQuery namespace to the '$' one
var $ = jQuery;

jQuery.fn = jQuery.prototype = {
	jquery: "1.1.1",

	size: function() {
		return this.length;
	},
	
	length: 0,

	get: function( num ) {
		return num == undefined ?

			// Return a 'clean' array
			jQuery.makeArray( this ) :

			// Return just the object
			this[num];
	},
	pushStack: function( a ) {
		var ret = jQuery(this);
		ret.prevObject = this;
		return ret.setArray( a );
	},
	setArray: function( a ) {
		this.length = 0;
		[].push.apply( this, a );
		return this;
	},
	each: function( fn, args ) {
		return jQuery.each( this, fn, args );
	},
	index: function( obj ) {
		var pos = -1;
		this.each(function(i){
			if ( this == obj ) pos = i;
		});
		return pos;
	},

	attr: function( key, value, type ) {
		var obj = key;
		
		// Look for the case where we're accessing a style value
		if ( key.constructor == String )
			if ( value == undefined )
				return this.length && jQuery[ type || "attr" ]( this[0], key ) || undefined;
			else {
				obj = {};
				obj[ key ] = value;
			}
		
		// Check to see if we're setting style values
		return this.each(function(index){
			// Set all the styles
			for ( var prop in obj )
				jQuery.attr(
					type ? this.style : this,
					prop, jQuery.prop(this, obj[prop], type, index, prop)
				);
		});
	},

	css: function( key, value ) {
		return this.attr( key, value, "curCSS" );
	},

	text: function(e) {
		if ( typeof e == "string" )
			return this.empty().append( document.createTextNode( e ) );

		var t = "";
		jQuery.each( e || this, function(){
			jQuery.each( this.childNodes, function(){
				if ( this.nodeType != 8 )
					t += this.nodeType != 1 ?
						this.nodeValue : jQuery.fn.text([ this ]);
			});
		});
		return t;
	},

	wrap: function() {
		// The elements to wrap the target around
		var a = jQuery.clean(arguments);

		// Wrap each of the matched elements individually
		return this.each(function(){
			// Clone the structure that we're using to wrap
			var b = a[0].cloneNode(true);

			// Insert it before the element to be wrapped
			this.parentNode.insertBefore( b, this );

			// Find the deepest point in the wrap structure
			while ( b.firstChild )
				b = b.firstChild;

			// Move the matched element to within the wrap structure
			b.appendChild( this );
		});
	},
	append: function() {
		return this.domManip(arguments, true, 1, function(a){
			this.appendChild( a );
		});
	},
	prepend: function() {
		return this.domManip(arguments, true, -1, function(a){
			this.insertBefore( a, this.firstChild );
		});
	},
	before: function() {
		return this.domManip(arguments, false, 1, function(a){
			this.parentNode.insertBefore( a, this );
		});
	},
	after: function() {
		return this.domManip(arguments, false, -1, function(a){
			this.parentNode.insertBefore( a, this.nextSibling );
		});
	},
	end: function() {
		return this.prevObject || jQuery([]);
	},
	find: function(t) {
		return this.pushStack( jQuery.map( this, function(a){
			return jQuery.find(t,a);
		}) );
	},
	clone: function(deep) {
		return this.pushStack( jQuery.map( this, function(a){
			return a.cloneNode( deep != undefined ? deep : true );
		}) );
	},

	filter: function(t) {
		return this.pushStack(
			jQuery.isFunction( t ) &&
			jQuery.grep(this, function(el, index){
				return t.apply(el, [index])
			}) ||

			jQuery.multiFilter(t,this) );
	},

	not: function(t) {
		return this.pushStack(
			t.constructor == String &&
			jQuery.multiFilter(t,this,true) ||

			jQuery.grep(this,function(a){
					if ( t.constructor == Array || t.jquery )
						return jQuery.inArray( t, a ) < 0;
					else
						return a != t;
			}) );
	},

	add: function(t) {
		return this.pushStack( jQuery.merge(
			this.get(),
			t.constructor == String ?
				jQuery(t).get() :
				t.length != undefined && !t.nodeName ?
					t : [t] )
		);
	},
	is: function(expr) {
		return expr ? jQuery.filter(expr,this).r.length > 0 : false;
	},

	val: function( val ) {
		return val == undefined ?
			( this.length ? this[0].value : null ) :
			this.attr( "value", val );
	},

	html: function( val ) {
		return val == undefined ?
			( this.length ? this[0].innerHTML : null ) :
			this.empty().append( val );
	},
	domManip: function(args, table, dir, fn){
		var clone = this.length > 1; 
		var a = jQuery.clean(args);
		if ( dir < 0 )
			a.reverse();

		return this.each(function(){
			var obj = this;

			if ( table && jQuery.nodeName(this, "table") && jQuery.nodeName(a[0], "tr") )
				obj = this.getElementsByTagName("tbody")[0] || this.appendChild(document.createElement("tbody"));

			jQuery.each( a, function(){
				fn.apply( obj, [ clone ? this.cloneNode(true) : this ] );
			});

		});
	}
};

jQuery.extend = jQuery.fn.extend = function() {
	// copy reference to target object
	var target = arguments[0],
		a = 1;

	// extend jQuery itself if only one argument is passed
	if ( arguments.length == 1 ) {
		target = this;
		a = 0;
	}
	var prop;
	while (prop = arguments[a++])
		// Extend the base object
		for ( var i in prop ) target[i] = prop[i];

	// Return the modified object
	return target;
};

jQuery.extend({
	noConflict: function() {
		if ( jQuery._$ )
			$ = jQuery._$;
		return jQuery;
	},

	// This may seem like some crazy code, but trust me when I say that this
	// is the only cross-browser way to do this. --John
	isFunction: function( fn ) {
		return !!fn && typeof fn != "string" &&
			typeof fn[0] == "undefined" && /function/i.test( fn + "" );
	},

	nodeName: function( elem, name ) {
		return elem.nodeName && elem.nodeName.toUpperCase() == name.toUpperCase();
	},
	// args is for internal usage only
	each: function( obj, fn, args ) {
		if ( obj.length == undefined )
			for ( var i in obj )
				fn.apply( obj[i], args || [i, obj[i]] );
		else
			for ( var i = 0, ol = obj.length; i < ol; i++ )
				if ( fn.apply( obj[i], args || [i, obj[i]] ) === false ) break;
		return obj;
	},
	
	prop: function(elem, value, type, index, prop){
			// Handle executable functions
			if ( jQuery.isFunction( value ) )
				return value.call( elem, [index] );
				
			// exclude the following css properties to add px
			var exclude = /z-?index|font-?weight|opacity|zoom|line-?height/i;

			// Handle passing in a number to a CSS property
			if ( value.constructor == Number && type == "curCSS" && !exclude.test(prop) )
				return value + "px";

			return value;
	},

	className: {
		// internal only, use addClass("class")
		add: function( elem, c ){
			jQuery.each( c.split(/\s+/), function(i, cur){
				if ( !jQuery.className.has( elem.className, cur ) )
					elem.className += ( elem.className ? " " : "" ) + cur;
			});
		},

		// internal only, use removeClass("class")
		remove: function( elem, c ){
			elem.className = c ?
				jQuery.grep( elem.className.split(/\s+/), function(cur){
					return !jQuery.className.has( c, cur );	
				}).join(" ") : "";
		},

		// internal only, use is(".class")
		has: function( t, c ) {
			t = t.className || t;
			return t && new RegExp("(^|\\s)" + c + "(\\s|$)").test( t );
		}
	},
	swap: function(e,o,f) {
		for ( var i in o ) {
			e.style["old"+i] = e.style[i];
			e.style[i] = o[i];
		}
		f.apply( e, [] );
		for ( var i in o )
			e.style[i] = e.style["old"+i];
	},

	css: function(e,p) {
		if ( p == "height" || p == "width" ) {
			var old = {}, oHeight, oWidth, d = ["Top","Bottom","Right","Left"];

			jQuery.each( d, function(){
				old["padding" + this] = 0;
				old["border" + this + "Width"] = 0;
			});

			jQuery.swap( e, old, function() {
				if (jQuery.css(e,"display") != "none") {
					oHeight = e.offsetHeight;
					oWidth = e.offsetWidth;
				} else {
					e = jQuery(e.cloneNode(true))
						.find(":radio").removeAttr("checked").end()
						.css({
							visibility: "hidden", position: "absolute", display: "block", right: "0", left: "0"
						}).appendTo(e.parentNode)[0];

					var parPos = jQuery.css(e.parentNode,"position");
					if ( parPos == "" || parPos == "static" )
						e.parentNode.style.position = "relative";

					oHeight = e.clientHeight;
					oWidth = e.clientWidth;

					if ( parPos == "" || parPos == "static" )
						e.parentNode.style.position = "static";

					e.parentNode.removeChild(e);
				}
			});

			return p == "height" ? oHeight : oWidth;
		}

		return jQuery.curCSS( e, p );
	},

	curCSS: function(elem, prop, force) {
		var ret;
		
		if (prop == "opacity" && jQuery.browser.msie)
			return jQuery.attr(elem.style, "opacity");
			
		if (prop == "float" || prop == "cssFloat")
		    prop = jQuery.browser.msie ? "styleFloat" : "cssFloat";

		if (!force && elem.style[prop])
			ret = elem.style[prop];

		else if (document.defaultView && document.defaultView.getComputedStyle) {

			if (prop == "cssFloat" || prop == "styleFloat")
				prop = "float";

			prop = prop.replace(/([A-Z])/g,"-$1").toLowerCase();
			var cur = document.defaultView.getComputedStyle(elem, null);

			if ( cur )
				ret = cur.getPropertyValue(prop);
			else if ( prop == "display" )
				ret = "none";
			else
				jQuery.swap(elem, { display: "block" }, function() {
				    var c = document.defaultView.getComputedStyle(this, "");
				    ret = c && c.getPropertyValue(prop) || "";
				});

		} else if (elem.currentStyle) {

			var newProp = prop.replace(/\-(\w)/g,function(m,c){return c.toUpperCase();});
			ret = elem.currentStyle[prop] || elem.currentStyle[newProp];
			
		}

		return ret;
	},
	
	clean: function(a) {
		var r = [];

		jQuery.each( a, function(i,arg){
			if ( !arg ) return;

			if ( arg.constructor == Number )
				arg = arg.toString();
			
			 // Convert html string into DOM nodes
			if ( typeof arg == "string" ) {
				// Trim whitespace, otherwise indexOf won't work as expected
				var s = jQuery.trim(arg), div = document.createElement("div"), tb = [];

				var wrap =
					 // option or optgroup
					!s.indexOf("<opt") &&
					[1, "<select>", "</select>"] ||
					
					(!s.indexOf("<thead") || !s.indexOf("<tbody") || !s.indexOf("<tfoot")) &&
					[1, "<table>", "</table>"] ||
					
					!s.indexOf("<tr") &&
					[2, "<table><tbody>", "</tbody></table>"] ||
					
				 	// <thead> matched above
					(!s.indexOf("<td") || !s.indexOf("<th")) &&
					[3, "<table><tbody><tr>", "</tr></tbody></table>"] ||
					
					[0,"",""];

				// Go to html and back, then peel off extra wrappers
				div.innerHTML = wrap[1] + s + wrap[2];
				
				// Move to the right depth
				while ( wrap[0]-- )
					div = div.firstChild;
				
				// Remove IE's autoinserted <tbody> from table fragments
				if ( jQuery.browser.msie ) {
					
					// String was a <table>, *may* have spurious <tbody>
					if ( !s.indexOf("<table") && s.indexOf("<tbody") < 0 ) 
						tb = div.firstChild && div.firstChild.childNodes;
						
					// String was a bare <thead> or <tfoot>
					else if ( wrap[1] == "<table>" && s.indexOf("<tbody") < 0 )
						tb = div.childNodes;

					for ( var n = tb.length-1; n >= 0 ; --n )
						if ( jQuery.nodeName(tb[n], "tbody") && !tb[n].childNodes.length )
							tb[n].parentNode.removeChild(tb[n]);
					
				}
				
				arg = div.childNodes;
			}

			if ( arg.length === 0 )
				return;
			
			if ( arg[0] == undefined )
				r.push( arg );
			else
				r = jQuery.merge( r, arg );

		});

		return r;
	},
	
	attr: function(elem, name, value){
		var fix = {
			"for": "htmlFor",
			"class": "className",
			"float": jQuery.browser.msie ? "styleFloat" : "cssFloat",
			cssFloat: jQuery.browser.msie ? "styleFloat" : "cssFloat",
			innerHTML: "innerHTML",
			className: "className",
			value: "value",
			disabled: "disabled",
			checked: "checked",
			readonly: "readOnly",
			selected: "selected"
		};
		
		// IE actually uses filters for opacity ... elem is actually elem.style
		if ( name == "opacity" && jQuery.browser.msie && value != undefined ) {
			// IE has trouble with opacity if it does not have layout
			// Force it by setting the zoom level
			elem.zoom = 1; 

			// Set the alpha filter to set the opacity
			return elem.filter = elem.filter.replace(/alpha\([^\)]*\)/gi,"") +
				( value == 1 ? "" : "alpha(opacity=" + value * 100 + ")" );

		} else if ( name == "opacity" && jQuery.browser.msie )
			return elem.filter ? 
				parseFloat( elem.filter.match(/alpha\(opacity=(.*)\)/)[1] ) / 100 : 1;
		
		// Mozilla doesn't play well with opacity 1
		if ( name == "opacity" && jQuery.browser.mozilla && value == 1 )
			value = 0.9999;

		// Certain attributes only work when accessed via the old DOM 0 way
		if ( fix[name] ) {
			if ( value != undefined ) elem[fix[name]] = value;
			return elem[fix[name]];

		} else if ( value == undefined && jQuery.browser.msie && jQuery.nodeName(elem, "form") && (name == "action" || name == "method") )
			return elem.getAttributeNode(name).nodeValue;

		// IE elem.getAttribute passes even for style
		else if ( elem.tagName ) {
			if ( value != undefined ) elem.setAttribute( name, value );
			return elem.getAttribute( name );

		} else {
			name = name.replace(/-([a-z])/ig,function(z,b){return b.toUpperCase();});
			if ( value != undefined ) elem[name] = value;
			return elem[name];
		}
	},
	trim: function(t){
		return t.replace(/^\s+|\s+$/g, "");
	},

	makeArray: function( a ) {
		var r = [];

		if ( a.constructor != Array )
			for ( var i = 0, al = a.length; i < al; i++ )
				r.push( a[i] );
		else
			r = a.slice( 0 );

		return r;
	},

	inArray: function( b, a ) {
		for ( var i = 0, al = a.length; i < al; i++ )
			if ( a[i] == b )
				return i;
		return -1;
	},
	merge: function(first, second) {
		var r = [].slice.call( first, 0 );

		// Now check for duplicates between the two arrays
		// and only add the unique items
		for ( var i = 0, sl = second.length; i < sl; i++ )
			// Check for duplicates
			if ( jQuery.inArray( second[i], r ) == -1 )
				// The item is unique, add it
				first.push( second[i] );

		return first;
	},
	grep: function(elems, fn, inv) {
		// If a string is passed in for the function, make a function
		// for it (a handy shortcut)
		if ( typeof fn == "string" )
			fn = new Function("a","i","return " + fn);

		var result = [];

		// Go through the array, only saving the items
		// that pass the validator function
		for ( var i = 0, el = elems.length; i < el; i++ )
			if ( !inv && fn(elems[i],i) || inv && !fn(elems[i],i) )
				result.push( elems[i] );

		return result;
	},
	map: function(elems, fn) {
		// If a string is passed in for the function, make a function
		// for it (a handy shortcut)
		if ( typeof fn == "string" )
			fn = new Function("a","return " + fn);

		var result = [], r = [];

		// Go through the array, translating each of the items to their
		// new value (or values).
		for ( var i = 0, el = elems.length; i < el; i++ ) {
			var val = fn(elems[i],i);

			if ( val !== null && val != undefined ) {
				if ( val.constructor != Array ) val = [val];
				result = result.concat( val );
			}
		}

		var r = result.length ? [ result[0] ] : [];

		check: for ( var i = 1, rl = result.length; i < rl; i++ ) {
			for ( var j = 0; j < i; j++ )
				if ( result[i] == r[j] )
					continue check;

			r.push( result[i] );
		}

		return r;
	}
});
 
/*
 * Whether the W3C compliant box model is being used.
 *
 * @property
 * @name $.boxModel
 * @type Boolean
 * @cat JavaScript
 */
new function() {
	var b = navigator.userAgent.toLowerCase();

	// Figure out what browser is being used
	jQuery.browser = {
		safari: /webkit/.test(b),
		opera: /opera/.test(b),
		msie: /msie/.test(b) && !/opera/.test(b),
		mozilla: /mozilla/.test(b) && !/(compatible|webkit)/.test(b)
	};

	// Check to see if the W3C box model is being used
	jQuery.boxModel = !jQuery.browser.msie || document.compatMode == "CSS1Compat";
};

jQuery.each({
	parent: "a.parentNode",
	parents: "jQuery.parents(a)",
	next: "jQuery.nth(a,2,'nextSibling')",
	prev: "jQuery.nth(a,2,'previousSibling')",
	siblings: "jQuery.sibling(a.parentNode.firstChild,a)",
	children: "jQuery.sibling(a.firstChild)"
}, function(i,n){
	jQuery.fn[ i ] = function(a) {
		var ret = jQuery.map(this,n);
		if ( a && typeof a == "string" )
			ret = jQuery.multiFilter(a,ret);
		return this.pushStack( ret );
	};
});

jQuery.each({
	appendTo: "append",
	prependTo: "prepend",
	insertBefore: "before",
	insertAfter: "after"
}, function(i,n){
	jQuery.fn[ i ] = function(){
		var a = arguments;
		return this.each(function(){
			for ( var j = 0, al = a.length; j < al; j++ )
				jQuery(a[j])[n]( this );
		});
	};
});

jQuery.each( {
	removeAttr: function( key ) {
		jQuery.attr( this, key, "" );
		this.removeAttribute( key );
	},
	addClass: function(c){
		jQuery.className.add(this,c);
	},
	removeClass: function(c){
		jQuery.className.remove(this,c);
	},
	toggleClass: function( c ){
		jQuery.className[ jQuery.className.has(this,c) ? "remove" : "add" ](this, c);
	},
	remove: function(a){
		if ( !a || jQuery.filter( a, [this] ).r.length )
			this.parentNode.removeChild( this );
	},
	empty: function() {
		while ( this.firstChild )
			this.removeChild( this.firstChild );
	}
}, function(i,n){
	jQuery.fn[ i ] = function() {
		return this.each( n, arguments );
	};
});

jQuery.each( [ "eq", "lt", "gt", "contains" ], function(i,n){
	jQuery.fn[ n ] = function(num,fn) {
		return this.filter( ":" + n + "(" + num + ")", fn );
	};
});

jQuery.each( [ "height", "width" ], function(i,n){
	jQuery.fn[ n ] = function(h) {
		return h == undefined ?
			( this.length ? jQuery.css( this[0], n ) : null ) :
			this.css( n, h.constructor == String ? h : h + "px" );
	};
});
jQuery.extend({
	expr: {
		"": "m[2]=='*'||jQuery.nodeName(a,m[2])",
		"#": "a.getAttribute('id')==m[2]",
		":": {
			// Position Checks
			lt: "i<m[3]-0",
			gt: "i>m[3]-0",
			nth: "m[3]-0==i",
			eq: "m[3]-0==i",
			first: "i==0",
			last: "i==r.length-1",
			even: "i%2==0",
			odd: "i%2",

			// Child Checks
			"nth-child": "jQuery.nth(a.parentNode.firstChild,m[3],'nextSibling',a)==a",
			"first-child": "jQuery.nth(a.parentNode.firstChild,1,'nextSibling')==a",
			"last-child": "jQuery.nth(a.parentNode.lastChild,1,'previousSibling')==a",
			"only-child": "jQuery.sibling(a.parentNode.firstChild).length==1",

			// Parent Checks
			parent: "a.firstChild",
			empty: "!a.firstChild",

			// Text Check
			contains: "jQuery.fn.text.apply([a]).indexOf(m[3])>=0",

			// Visibility
			visible: 'a.type!="hidden"&&jQuery.css(a,"display")!="none"&&jQuery.css(a,"visibility")!="hidden"',
			hidden: 'a.type=="hidden"||jQuery.css(a,"display")=="none"||jQuery.css(a,"visibility")=="hidden"',

			// Form attributes
			enabled: "!a.disabled",
			disabled: "a.disabled",
			checked: "a.checked",
			selected: "a.selected||jQuery.attr(a,'selected')",

			// Form elements
			text: "a.type=='text'",
			radio: "a.type=='radio'",
			checkbox: "a.type=='checkbox'",
			file: "a.type=='file'",
			password: "a.type=='password'",
			submit: "a.type=='submit'",
			image: "a.type=='image'",
			reset: "a.type=='reset'",
			button: 'a.type=="button"||jQuery.nodeName(a,"button")',
			input: "/input|select|textarea|button/i.test(a.nodeName)"
		},
		".": "jQuery.className.has(a,m[2])",
		"@": {
			"=": "z==m[4]",
			"!=": "z!=m[4]",
			"^=": "z&&!z.indexOf(m[4])",
			"$=": "z&&z.substr(z.length - m[4].length,m[4].length)==m[4]",
			"*=": "z&&z.indexOf(m[4])>=0",
			"": "z",
			_resort: function(m){
				return ["", m[1], m[3], m[2], m[5]];
			},
			_prefix: "z=a[m[3]]||jQuery.attr(a,m[3]);"
		},
		"[": "jQuery.find(m[2],a).length"
	},
	
	// The regular expressions that power the parsing engine
	parse: [
		// Match: [@value='test'], [@foo]
		/^\[ *(@)([a-z0-9_-]*) *([!*$^=]*) *('?"?)(.*?)\4 *\]/i,

		// Match: [div], [div p]
		/^(\[)\s*(.*?(\[.*?\])?[^[]*?)\s*\]/,

		// Match: :contains('foo')
		/^(:)([a-z0-9_-]*)\("?'?(.*?(\(.*?\))?[^(]*?)"?'?\)/i,

		// Match: :even, :last-chlid
		/^([:.#]*)([a-z0-9_*-]*)/i
	],

	token: [
		/^(\/?\.\.)/, "a.parentNode",
		/^(>|\/)/, "jQuery.sibling(a.firstChild)",
		/^(\+)/, "jQuery.nth(a,2,'nextSibling')",
		/^(~)/, function(a){
			var s = jQuery.sibling(a.parentNode.firstChild);
			return s.slice(0, jQuery.inArray(a,s));
		}
	],

	multiFilter: function( expr, elems, not ) {
		var old, cur = [];

		while ( expr && expr != old ) {
			old = expr;
			var f = jQuery.filter( expr, elems, not );
			expr = f.t.replace(/^\s*,\s*/, "" );
			cur = not ? elems = f.r : jQuery.merge( cur, f.r );
		}

		return cur;
	},
	find: function( t, context ) {
		// Quickly handle non-string expressions
		if ( typeof t != "string" )
			return [ t ];

		// Make sure that the context is a DOM Element
		if ( context && !context.nodeType )
			context = null;

		// Set the correct context (if none is provided)
		context = context || document;

		// Handle the common XPath // expression
		if ( !t.indexOf("//") ) {
			context = context.documentElement;
			t = t.substr(2,t.length);

		// And the / root expression
		} else if ( !t.indexOf("/") ) {
			context = context.documentElement;
			t = t.substr(1,t.length);
			if ( t.indexOf("/") >= 1 )
				t = t.substr(t.indexOf("/"),t.length);
		}

		// Initialize the search
		var ret = [context], done = [], last = null;

		// Continue while a selector expression exists, and while
		// we're no longer looping upon ourselves
		while ( t && last != t ) {
			var r = [];
			last = t;

			t = jQuery.trim(t).replace( /^\/\//i, "" );

			var foundToken = false;

			// An attempt at speeding up child selectors that
			// point to a specific element tag
			var re = /^[\/>]\s*([a-z0-9*-]+)/i;
			var m = re.exec(t);

			if ( m ) {
				// Perform our own iteration and filter
				jQuery.each( ret, function(){
					for ( var c = this.firstChild; c; c = c.nextSibling )
						if ( c.nodeType == 1 && ( jQuery.nodeName(c, m[1]) || m[1] == "*" ) )
							r.push( c );
				});

				ret = r;
				t = t.replace( re, "" );
				if ( t.indexOf(" ") == 0 ) continue;
				foundToken = true;
			} else {
				// Look for pre-defined expression tokens
				for ( var i = 0; i < jQuery.token.length; i += 2 ) {
					// Attempt to match each, individual, token in
					// the specified order
					var re = jQuery.token[i];
					var m = re.exec(t);

					// If the token match was found
					if ( m ) {
						// Map it against the token's handler
						r = ret = jQuery.map( ret, jQuery.isFunction( jQuery.token[i+1] ) ?
							jQuery.token[i+1] :
							function(a){ return eval(jQuery.token[i+1]); });

						// And remove the token
						t = jQuery.trim( t.replace( re, "" ) );
						foundToken = true;
						break;
					}
				}
			}

			// See if there's still an expression, and that we haven't already
			// matched a token
			if ( t && !foundToken ) {
				// Handle multiple expressions
				if ( !t.indexOf(",") ) {
					// Clean the result set
					if ( ret[0] == context ) ret.shift();

					// Merge the result sets
					jQuery.merge( done, ret );

					// Reset the context
					r = ret = [context];

					// Touch up the selector string
					t = " " + t.substr(1,t.length);

				} else {
					// Optomize for the case nodeName#idName
					var re2 = /^([a-z0-9_-]+)(#)([a-z0-9\\*_-]*)/i;
					var m = re2.exec(t);
					
					// Re-organize the results, so that they're consistent
					if ( m ) {
					   m = [ 0, m[2], m[3], m[1] ];

					} else {
						// Otherwise, do a traditional filter check for
						// ID, class, and element selectors
						re2 = /^([#.]?)([a-z0-9\\*_-]*)/i;
						m = re2.exec(t);
					}

					// Try to do a global search by ID, where we can
					if ( m[1] == "#" && ret[ret.length-1].getElementById ) {
						// Optimization for HTML document case
						var oid = ret[ret.length-1].getElementById(m[2]);

						// Do a quick check for node name (where applicable) so
						// that div#foo searches will be really fast
						ret = r = oid && 
						  (!m[3] || jQuery.nodeName(oid, m[3])) ? [oid] : [];

					} else {
						// Pre-compile a regular expression to handle class searches
						if ( m[1] == "." )
							var rec = new RegExp("(^|\\s)" + m[2] + "(\\s|$)");

						// We need to find all descendant elements, it is more
						// efficient to use getAll() when we are already further down
						// the tree - we try to recognize that here
						jQuery.each( ret, function(){
							// Grab the tag name being searched for
							var tag = m[1] != "" || m[0] == "" ? "*" : m[2];

							// Handle IE7 being really dumb about <object>s
							if ( jQuery.nodeName(this, "object") && tag == "*" )
								tag = "param";

							jQuery.merge( r,
								m[1] != "" && ret.length != 1 ?
									jQuery.getAll( this, [], m[1], m[2], rec ) :
									this.getElementsByTagName( tag )
							);
						});

						// It's faster to filter by class and be done with it
						if ( m[1] == "." && ret.length == 1 )
							r = jQuery.grep( r, function(e) {
								return rec.test(e.className);
							});

						// Same with ID filtering
						if ( m[1] == "#" && ret.length == 1 ) {
							// Remember, then wipe out, the result set
							var tmp = r;
							r = [];

							// Then try to find the element with the ID
							jQuery.each( tmp, function(){
								if ( this.getAttribute("id") == m[2] ) {
									r = [ this ];
									return false;
								}
							});
						}

						ret = r;
					}

					t = t.replace( re2, "" );
				}

			}

			// If a selector string still exists
			if ( t ) {
				// Attempt to filter it
				var val = jQuery.filter(t,r);
				ret = r = val.r;
				t = jQuery.trim(val.t);
			}
		}

		// Remove the root context
		if ( ret && ret[0] == context ) ret.shift();

		// And combine the results
		jQuery.merge( done, ret );

		return done;
	},

	filter: function(t,r,not) {
		// Look for common filter expressions
		while ( t && /^[a-z[({<*:.#]/i.test(t) ) {

			var p = jQuery.parse, m;

			jQuery.each( p, function(i,re){
		
				// Look for, and replace, string-like sequences
				// and finally build a regexp out of it
				m = re.exec( t );

				if ( m ) {
					// Remove what we just matched
					t = t.substring( m[0].length );

					// Re-organize the first match
					if ( jQuery.expr[ m[1] ]._resort )
						m = jQuery.expr[ m[1] ]._resort( m );

					return false;
				}
			});

			// :not() is a special case that can be optimized by
			// keeping it out of the expression list
			if ( m[1] == ":" && m[2] == "not" )
				r = jQuery.filter(m[3], r, true).r;

			// Handle classes as a special case (this will help to
			// improve the speed, as the regexp will only be compiled once)
			else if ( m[1] == "." ) {

				var re = new RegExp("(^|\\s)" + m[2] + "(\\s|$)");
				r = jQuery.grep( r, function(e){
					return re.test(e.className || "");
				}, not);

			// Otherwise, find the expression to execute
			} else {
				var f = jQuery.expr[m[1]];
				if ( typeof f != "string" )
					f = jQuery.expr[m[1]][m[2]];

				// Build a custom macro to enclose it
				eval("f = function(a,i){" +
					( jQuery.expr[ m[1] ]._prefix || "" ) +
					"return " + f + "}");

				// Execute it against the current filter
				r = jQuery.grep( r, f, not );
			}
		}

		// Return an array of filtered elements (r)
		// and the modified expression string (t)
		return { r: r, t: t };
	},
	
	getAll: function( o, r, token, name, re ) {
		for ( var s = o.firstChild; s; s = s.nextSibling )
			if ( s.nodeType == 1 ) {
				var add = true;

				if ( token == "." )
					add = s.className && re.test(s.className);
				else if ( token == "#" )
					add = s.getAttribute("id") == name;
	
				if ( add )
					r.push( s );

				if ( token == "#" && r.length ) break;

				if ( s.firstChild )
					jQuery.getAll( s, r, token, name, re );
			}

		return r;
	},
	parents: function( elem ){
		var matched = [];
		var cur = elem.parentNode;
		while ( cur && cur != document ) {
			matched.push( cur );
			cur = cur.parentNode;
		}
		return matched;
	},
	nth: function(cur,result,dir,elem){
		result = result || 1;
		var num = 0;
		for ( ; cur; cur = cur[dir] ) {
			if ( cur.nodeType == 1 ) num++;
			if ( num == result || result == "even" && num % 2 == 0 && num > 1 && cur == elem ||
				result == "odd" && num % 2 == 1 && cur == elem ) return cur;
		}
	},
	sibling: function( n, elem ) {
		var r = [];

		for ( ; n; n = n.nextSibling ) {
			if ( n.nodeType == 1 && (!elem || n != elem) )
				r.push( n );
		}

		return r;
	}
});
/*
 * A number of helper functions used for managing events.
 * Many of the ideas behind this code orignated from 
 * Dean Edwards' addEvent library.
 */
jQuery.event = {

	// Bind an event to an element
	// Original by Dean Edwards
	add: function(element, type, handler, data) {
		// For whatever reason, IE has trouble passing the window object
		// around, causing it to be cloned in the process
		if ( jQuery.browser.msie && element.setInterval != undefined )
			element = window;

		// if data is passed, bind to handler
		if( data ) 
			handler.data = data;

		// Make sure that the function being executed has a unique ID
		if ( !handler.guid )
			handler.guid = this.guid++;

		// Init the element's event structure
		if (!element.events)
			element.events = {};

		// Get the current list of functions bound to this event
		var handlers = element.events[type];

		// If it hasn't been initialized yet
		if (!handlers) {
			// Init the event handler queue
			handlers = element.events[type] = {};

			// Remember an existing handler, if it's already there
			if (element["on" + type])
				handlers[0] = element["on" + type];
		}

		// Add the function to the element's handler list
		handlers[handler.guid] = handler;

		// And bind the global event handler to the element
		element["on" + type] = this.handle;

		// Remember the function in a global list (for triggering)
		if (!this.global[type])
			this.global[type] = [];
		this.global[type].push( element );
	},

	guid: 1,
	global: {},

	// Detach an event or set of events from an element
	remove: function(element, type, handler) {
		if (element.events)
			if ( type && type.type )
				delete element.events[ type.type ][ type.handler.guid ];
			else if (type && element.events[type])
				if ( handler )
					delete element.events[type][handler.guid];
				else
					for ( var i in element.events[type] )
						delete element.events[type][i];
			else
				for ( var j in element.events )
					this.remove( element, j );
	},

	trigger: function(type,data,element) {
		// Clone the incoming data, if any
		data = jQuery.makeArray(data || []);

		// Handle a global trigger
		if ( !element )
			jQuery.each( this.global[type] || [], function(){
				jQuery.event.trigger( type, data, this );
			});

		// Handle triggering a single element
		else {
			var handler = element["on" + type ], val,
				fn = jQuery.isFunction( element[ type ] );

			if ( handler ) {
				// Pass along a fake event
				data.unshift( this.fix({ type: type, target: element }) );
	
				// Trigger the event
				if ( (val = handler.apply( element, data )) !== false )
					this.triggered = true;
			}

			if ( fn && val !== false )
				element[ type ]();

			this.triggered = false;
		}
	},

	handle: function(event) {
		// Handle the second event of a trigger and when
		// an event is called after a page has unloaded
		if ( typeof jQuery == "undefined" || jQuery.event.triggered ) return;

		// Empty object is for triggered events with no data
		event = jQuery.event.fix( event || window.event || {} ); 

		// returned undefined or false
		var returnValue;

		var c = this.events[event.type];

		var args = [].slice.call( arguments, 1 );
		args.unshift( event );

		for ( var j in c ) {
			// Pass in a reference to the handler function itself
			// So that we can later remove it
			args[0].handler = c[j];
			args[0].data = c[j].data;

			if ( c[j].apply( this, args ) === false ) {
				event.preventDefault();
				event.stopPropagation();
				returnValue = false;
			}
		}

		// Clean up added properties in IE to prevent memory leak
		if (jQuery.browser.msie) event.target = event.preventDefault = event.stopPropagation = event.handler = event.data = null;

		return returnValue;
	},

	fix: function(event) {
		// Fix target property, if necessary
		if ( !event.target && event.srcElement )
			event.target = event.srcElement;

		// Calculate pageX/Y if missing and clientX/Y available
		if ( event.pageX == undefined && event.clientX != undefined ) {
			var e = document.documentElement, b = document.body;
			event.pageX = event.clientX + (e.scrollLeft || b.scrollLeft);
			event.pageY = event.clientY + (e.scrollTop || b.scrollTop);
		}
				
		// check if target is a textnode (safari)
		if (jQuery.browser.safari && event.target.nodeType == 3) {
			// store a copy of the original event object 
			// and clone because target is read only
			var originalEvent = event;
			event = jQuery.extend({}, originalEvent);
			
			// get parentnode from textnode
			event.target = originalEvent.target.parentNode;
			
			// add preventDefault and stopPropagation since 
			// they will not work on the clone
			event.preventDefault = function() {
				return originalEvent.preventDefault();
			};
			event.stopPropagation = function() {
				return originalEvent.stopPropagation();
			};
		}
		
		// fix preventDefault and stopPropagation
		if (!event.preventDefault)
			event.preventDefault = function() {
				this.returnValue = false;
			};
			
		if (!event.stopPropagation)
			event.stopPropagation = function() {
				this.cancelBubble = true;
			};
			
		return event;
	}
};

jQuery.fn.extend({
	bind: function( type, data, fn ) {
		return this.each(function(){
			jQuery.event.add( this, type, fn || data, data );
		});
	},
	one: function( type, data, fn ) {
		return this.each(function(){
			jQuery.event.add( this, type, function(event) {
				jQuery(this).unbind(event);
				return (fn || data).apply( this, arguments);
			}, data);
		});
	},
	unbind: function( type, fn ) {
		return this.each(function(){
			jQuery.event.remove( this, type, fn );
		});
	},
	trigger: function( type, data ) {
		return this.each(function(){
			jQuery.event.trigger( type, data, this );
		});
	},
	toggle: function() {
		// Save reference to arguments for access in closure
		var a = arguments;

		return this.click(function(e) {
			// Figure out which function to execute
			this.lastToggle = this.lastToggle == 0 ? 1 : 0;
			
			// Make sure that clicks stop
			e.preventDefault();
			
			// and execute the function
			return a[this.lastToggle].apply( this, [e] ) || false;
		});
	},
	hover: function(f,g) {
		
		// A private function for handling mouse 'hovering'
		function handleHover(e) {
			// Check if mouse(over|out) are still within the same parent element
			var p = (e.type == "mouseover" ? e.fromElement : e.toElement) || e.relatedTarget;
	
			// Traverse up the tree
			while ( p && p != this ) try { p = p.parentNode } catch(e) { p = this; };
			
			// If we actually just moused on to a sub-element, ignore it
			if ( p == this ) return false;
			
			// Execute the right function
			return (e.type == "mouseover" ? f : g).apply(this, [e]);
		}
		
		// Bind the function to the two event listeners
		return this.mouseover(handleHover).mouseout(handleHover);
	},
	ready: function(f) {
		// If the DOM is already ready
		if ( jQuery.isReady )
			// Execute the function immediately
			f.apply( document, [jQuery] );
			
		// Otherwise, remember the function for later
		else {
			// Add the function to the wait list
			jQuery.readyList.push( function() { return f.apply(this, [jQuery]) } );
		}
	
		return this;
	}
});

jQuery.extend({
	/*
	 * All the code that makes DOM Ready work nicely.
	 */
	isReady: false,
	readyList: [],
	
	// Handle when the DOM is ready
	ready: function() {
		// Make sure that the DOM is not already loaded
		if ( !jQuery.isReady ) {
			// Remember that the DOM is ready
			jQuery.isReady = true;
			
			// If there are functions bound, to execute
			if ( jQuery.readyList ) {
				// Execute all of them
				jQuery.each( jQuery.readyList, function(){
					this.apply( document );
				});
				
				// Reset the list of functions
				jQuery.readyList = null;
			}
			// Remove event lisenter to avoid memory leak
			if ( jQuery.browser.mozilla || jQuery.browser.opera )
				document.removeEventListener( "DOMContentLoaded", jQuery.ready, false );
		}
	}
});

new function(){

	jQuery.each( ("blur,focus,load,resize,scroll,unload,click,dblclick," +
		"mousedown,mouseup,mousemove,mouseover,mouseout,change,select," + 
		"submit,keydown,keypress,keyup,error").split(","), function(i,o){
		
		// Handle event binding
		jQuery.fn[o] = function(f){
			return f ? this.bind(o, f) : this.trigger(o);
		};
			
	});
	
	// If Mozilla is used
	if ( jQuery.browser.mozilla || jQuery.browser.opera )
		// Use the handy event callback
		document.addEventListener( "DOMContentLoaded", jQuery.ready, false );
	
	// If IE is used, use the excellent hack by Matthias Miller
	// http://www.outofhanwell.com/blog/index.php?title=the_window_onload_problem_revisited
	else if ( jQuery.browser.msie ) {
	
		// Only works if you document.write() it
		document.write("<scr" + "ipt id=__ie_init defer=true " + 
			"src=//:><\/script>");
	
		// Use the defer script hack
		var script = document.getElementById("__ie_init");
		
		// script does not exist if jQuery is loaded dynamically
		if ( script ) 
			script.onreadystatechange = function() {
				if ( this.readyState != "complete" ) return;
				this.parentNode.removeChild( this );
				jQuery.ready();
			};
	
		// Clear from memory
		script = null;
	
	// If Safari  is used
	} else if ( jQuery.browser.safari )
		// Continually check to see if the document.readyState is valid
		jQuery.safariTimer = setInterval(function(){
			// loaded and complete are both valid states
			if ( document.readyState == "loaded" || 
				document.readyState == "complete" ) {
	
				// If either one are found, remove the timer
				clearInterval( jQuery.safariTimer );
				jQuery.safariTimer = null;
	
				// and execute any waiting functions
				jQuery.ready();
			}
		}, 10); 

	// A fallback to window.onload, that will always work
	jQuery.event.add( window, "load", jQuery.ready );
	
};

// Clean up after IE to avoid memory leaks
if (jQuery.browser.msie)
	jQuery(window).one("unload", function() {
		var global = jQuery.event.global;
		for ( var type in global ) {
			var els = global[type], i = els.length;
			if ( i && type != 'unload' )
				do
					jQuery.event.remove(els[i-1], type);
				while (--i);
		}
	});
jQuery.fn.extend({

	show: function(speed,callback){
		var hidden = this.filter(":hidden");
		speed ?
			hidden.animate({
				height: "show", width: "show", opacity: "show"
			}, speed, callback) :
			
			hidden.each(function(){
				this.style.display = this.oldblock ? this.oldblock : "";
				if ( jQuery.css(this,"display") == "none" )
					this.style.display = "block";
			});
		return this;
	},

	hide: function(speed,callback){
		var visible = this.filter(":visible");
		speed ?
			visible.animate({
				height: "hide", width: "hide", opacity: "hide"
			}, speed, callback) :
			
			visible.each(function(){
				this.oldblock = this.oldblock || jQuery.css(this,"display");
				if ( this.oldblock == "none" )
					this.oldblock = "block";
				this.style.display = "none";
			});
		return this;
	},

	// Save the old toggle function
	_toggle: jQuery.fn.toggle,
	toggle: function( fn, fn2 ){
		var args = arguments;
		return jQuery.isFunction(fn) && jQuery.isFunction(fn2) ?
			this._toggle( fn, fn2 ) :
			this.each(function(){
				jQuery(this)[ jQuery(this).is(":hidden") ? "show" : "hide" ]
					.apply( jQuery(this), args );
			});
	},
	slideDown: function(speed,callback){
		return this.animate({height: "show"}, speed, callback);
	},
	slideUp: function(speed,callback){
		return this.animate({height: "hide"}, speed, callback);
	},
	slideToggle: function(speed, callback){
		return this.each(function(){
			var state = jQuery(this).is(":hidden") ? "show" : "hide";
			jQuery(this).animate({height: state}, speed, callback);
		});
	},
	fadeIn: function(speed, callback){
		return this.animate({opacity: "show"}, speed, callback);
	},
	fadeOut: function(speed, callback){
		return this.animate({opacity: "hide"}, speed, callback);
	},
	fadeTo: function(speed,to,callback){
		return this.animate({opacity: to}, speed, callback);
	},
	animate: function( prop, speed, easing, callback ) {
		return this.queue(function(){
		
			this.curAnim = jQuery.extend({}, prop);
			var opt = jQuery.speed(speed, easing, callback);
			
			for ( var p in prop ) {
				var e = new jQuery.fx( this, opt, p );
				if ( prop[p].constructor == Number )
					e.custom( e.cur(), prop[p] );
				else
					e[ prop[p] ]( prop );
			}
			
		});
	},
	queue: function(type,fn){
		if ( !fn ) {
			fn = type;
			type = "fx";
		}
	
		return this.each(function(){
			if ( !this.queue )
				this.queue = {};
	
			if ( !this.queue[type] )
				this.queue[type] = [];
	
			this.queue[type].push( fn );
		
			if ( this.queue[type].length == 1 )
				fn.apply(this);
		});
	}

});

jQuery.extend({
	
	speed: function(speed, easing, fn) {
		var opt = speed && speed.constructor == Object ? speed : {
			complete: fn || !fn && easing || 
				jQuery.isFunction( speed ) && speed,
			duration: speed,
			easing: fn && easing || easing && easing.constructor != Function && easing
		};

		opt.duration = (opt.duration && opt.duration.constructor == Number ? 
			opt.duration : 
			{ slow: 600, fast: 200 }[opt.duration]) || 400;
	
		// Queueing
		opt.old = opt.complete;
		opt.complete = function(){
			jQuery.dequeue(this, "fx");
			if ( jQuery.isFunction( opt.old ) )
				opt.old.apply( this );
		};
	
		return opt;
	},
	
	easing: {},
	
	queue: {},
	
	dequeue: function(elem,type){
		type = type || "fx";
	
		if ( elem.queue && elem.queue[type] ) {
			// Remove self
			elem.queue[type].shift();
	
			// Get next function
			var f = elem.queue[type][0];
		
			if ( f ) f.apply( elem );
		}
	},

	/*
	 * I originally wrote fx() as a clone of moo.fx and in the process
	 * of making it small in size the code became illegible to sane
	 * people. You've been warned.
	 */
	
	fx: function( elem, options, prop ){

		var z = this;

		// The styles
		var y = elem.style;
		
		// Store display property
		var oldDisplay = jQuery.css(elem, "display");

		// Set display property to block for animation
		y.display = "block";

		// Make sure that nothing sneaks out
		y.overflow = "hidden";

		// Simple function for setting a style value
		z.a = function(){
			if ( options.step )
				options.step.apply( elem, [ z.now ] );

			if ( prop == "opacity" )
				jQuery.attr(y, "opacity", z.now); // Let attr handle opacity
			else if ( parseInt(z.now) ) // My hate for IE will never die
				y[prop] = parseInt(z.now) + "px";
		};

		// Figure out the maximum number to run to
		z.max = function(){
			return parseFloat( jQuery.css(elem,prop) );
		};

		// Get the current size
		z.cur = function(){
			var r = parseFloat( jQuery.curCSS(elem, prop) );
			return r && r > -10000 ? r : z.max();
		};

		// Start an animation from one number to another
		z.custom = function(from,to){
			z.startTime = (new Date()).getTime();
			z.now = from;
			z.a();

			z.timer = setInterval(function(){
				z.step(from, to);
			}, 13);
		};

		// Simple 'show' function
		z.show = function(){
			if ( !elem.orig ) elem.orig = {};

			// Remember where we started, so that we can go back to it later
			elem.orig[prop] = this.cur();

			options.show = true;

			// Begin the animation
			z.custom(0, elem.orig[prop]);

			// Stupid IE, look what you made me do
			if ( prop != "opacity" )
				y[prop] = "1px";
		};

		// Simple 'hide' function
		z.hide = function(){
			if ( !elem.orig ) elem.orig = {};

			// Remember where we started, so that we can go back to it later
			elem.orig[prop] = this.cur();

			options.hide = true;

			// Begin the animation
			z.custom(elem.orig[prop], 0);
		};
		
		//Simple 'toggle' function
		z.toggle = function() {
			if ( !elem.orig ) elem.orig = {};

			// Remember where we started, so that we can go back to it later
			elem.orig[prop] = this.cur();

			if(oldDisplay == "none")  {
				options.show = true;
				
				// Stupid IE, look what you made me do
				if ( prop != "opacity" )
					y[prop] = "1px";

				// Begin the animation
				z.custom(0, elem.orig[prop]);	
			} else {
				options.hide = true;

				// Begin the animation
				z.custom(elem.orig[prop], 0);
			}		
		};

		// Each step of an animation
		z.step = function(firstNum, lastNum){
			var t = (new Date()).getTime();

			if (t > options.duration + z.startTime) {
				// Stop the timer
				clearInterval(z.timer);
				z.timer = null;

				z.now = lastNum;
				z.a();

				if (elem.curAnim) elem.curAnim[ prop ] = true;

				var done = true;
				for ( var i in elem.curAnim )
					if ( elem.curAnim[i] !== true )
						done = false;

				if ( done ) {
					// Reset the overflow
					y.overflow = "";
					
					// Reset the display
					y.display = oldDisplay;
					if (jQuery.css(elem, "display") == "none")
						y.display = "block";

					// Hide the element if the "hide" operation was done
					if ( options.hide ) 
						y.display = "none";

					// Reset the properties, if the item has been hidden or shown
					if ( options.hide || options.show )
						for ( var p in elem.curAnim )
							if (p == "opacity")
								jQuery.attr(y, p, elem.orig[p]);
							else
								y[p] = "";
				}

				// If a callback was provided, execute it
				if ( done && jQuery.isFunction( options.complete ) )
					// Execute the complete function
					options.complete.apply( elem );
			} else {
				var n = t - this.startTime;
				// Figure out where in the animation we are and set the number
				var p = n / options.duration;
				
				// If the easing function exists, then use it 
				z.now = options.easing && jQuery.easing[options.easing] ?
					jQuery.easing[options.easing](p, n,  firstNum, (lastNum-firstNum), options.duration) :
					// else use default linear easing
					((-Math.cos(p*Math.PI)/2) + 0.5) * (lastNum-firstNum) + firstNum;

				// Perform the next step of the animation
				z.a();
			}
		};
	
	}
});
jQuery.fn.extend({
	loadIfModified: function( url, params, callback ) {
		this.load( url, params, callback, 1 );
	},
	load: function( url, params, callback, ifModified ) {
		if ( jQuery.isFunction( url ) )
			return this.bind("load", url);

		callback = callback || function(){};

		// Default to a GET request
		var type = "GET";

		// If the second parameter was provided
		if ( params )
			// If it's a function
			if ( jQuery.isFunction( params ) ) {
				// We assume that it's the callback
				callback = params;
				params = null;

			// Otherwise, build a param string
			} else {
				params = jQuery.param( params );
				type = "POST";
			}

		var self = this;

		// Request the remote document
		jQuery.ajax({
			url: url,
			type: type,
			data: params,
			ifModified: ifModified,
			complete: function(res, status){
				if ( status == "success" || !ifModified && status == "notmodified" )
					// Inject the HTML into all the matched elements
					self.attr("innerHTML", res.responseText)
					  // Execute all the scripts inside of the newly-injected HTML
					  .evalScripts()
					  // Execute callback
					  .each( callback, [res.responseText, status, res] );
				else
					callback.apply( self, [res.responseText, status, res] );
			}
		});
		return this;
	},
	serialize: function() {
		return jQuery.param( this );
	},
	evalScripts: function() {
		return this.find("script").each(function(){
			if ( this.src )
				jQuery.getScript( this.src );
			else
				jQuery.globalEval( this.text || this.textContent || this.innerHTML || "" );
		}).end();
	}

});

// If IE is used, create a wrapper for the XMLHttpRequest object
if ( !window.XMLHttpRequest )
	XMLHttpRequest = function(){
		return new ActiveXObject("Microsoft.XMLHTTP");
	};

// Attach a bunch of functions for handling common AJAX events

jQuery.each( "ajaxStart,ajaxStop,ajaxComplete,ajaxError,ajaxSuccess,ajaxSend".split(","), function(i,o){
	jQuery.fn[o] = function(f){
		return this.bind(o, f);
	};
});

jQuery.extend({
	get: function( url, data, callback, type, ifModified ) {
		// shift arguments if data argument was ommited
		if ( jQuery.isFunction( data ) ) {
			callback = data;
			data = null;
		}
		
		return jQuery.ajax({
			url: url,
			data: data,
			success: callback,
			dataType: type,
			ifModified: ifModified
		});
	},
	getIfModified: function( url, data, callback, type ) {
		return jQuery.get(url, data, callback, type, 1);
	},
	getScript: function( url, callback ) {
		return jQuery.get(url, null, callback, "script");
	},
	getJSON: function( url, data, callback ) {
		return jQuery.get(url, data, callback, "json");
	},
	post: function( url, data, callback, type ) {
		if ( jQuery.isFunction( data ) ) {
			callback = data;
			data = {};
		}

		return jQuery.ajax({
			type: "POST",
			url: url,
			data: data,
			success: callback,
			dataType: type
		});
	},

	// timeout (ms)
	//timeout: 0,
	ajaxTimeout: function( timeout ) {
		jQuery.ajaxSettings.timeout = timeout;
	},
	ajaxSetup: function( settings ) {
		jQuery.extend( jQuery.ajaxSettings, settings );
	},

	ajaxSettings: {
		global: true,
		type: "GET",
		timeout: 0,
		contentType: "application/x-www-form-urlencoded",
		processData: true,
		async: true,
		data: null
	},
	
	// Last-Modified header cache for next request
	lastModified: {},
	ajax: function( s ) {
		// TODO introduce global settings, allowing the client to modify them for all requests, not only timeout
		s = jQuery.extend({}, jQuery.ajaxSettings, s);

		// if data available
		if ( s.data ) {
			// convert data if not already a string
			if (s.processData && typeof s.data != "string")
    			s.data = jQuery.param(s.data);
			// append data to url for get requests
			if( s.type.toLowerCase() == "get" )
				// "?" + data or "&" + data (in case there are already params)
				s.url += ((s.url.indexOf("?") > -1) ? "&" : "?") + s.data;
		}

		// Watch for a new set of requests
		if ( s.global && ! jQuery.active++ )
			jQuery.event.trigger( "ajaxStart" );

		var requestDone = false;

		// Create the request object
		var xml = new XMLHttpRequest();

		// Open the socket
		xml.open(s.type, s.url, s.async);

		// Set the correct header, if data is being sent
		if ( s.data )
			xml.setRequestHeader("Content-Type", s.contentType);

		// Set the If-Modified-Since header, if ifModified mode.
		if ( s.ifModified )
			xml.setRequestHeader("If-Modified-Since",
				jQuery.lastModified[s.url] || "Thu, 01 Jan 1970 00:00:00 GMT" );

		// Set header so the called script knows that it's an XMLHttpRequest
		xml.setRequestHeader("X-Requested-With", "XMLHttpRequest");

		// Make sure the browser sends the right content length
		if ( xml.overrideMimeType )
			xml.setRequestHeader("Connection", "close");
			
		// Allow custom headers/mimetypes
		if( s.beforeSend )
			s.beforeSend(xml);
			
		if ( s.global )
		    jQuery.event.trigger("ajaxSend", [xml, s]);

		// Wait for a response to come back
		var onreadystatechange = function(isTimeout){
			// The transfer is complete and the data is available, or the request timed out
			if ( xml && (xml.readyState == 4 || isTimeout == "timeout") ) {
				requestDone = true;
				var status;
				try {
					status = jQuery.httpSuccess( xml ) && isTimeout != "timeout" ?
						s.ifModified && jQuery.httpNotModified( xml, s.url ) ? "notmodified" : "success" : "error";
					// Make sure that the request was successful or notmodified
					if ( status != "error" ) {
						// Cache Last-Modified header, if ifModified mode.
						var modRes;
						try {
							modRes = xml.getResponseHeader("Last-Modified");
						} catch(e) {} // swallow exception thrown by FF if header is not available
	
						if ( s.ifModified && modRes )
							jQuery.lastModified[s.url] = modRes;
	
						// process the data (runs the xml through httpData regardless of callback)
						var data = jQuery.httpData( xml, s.dataType );
	
						// If a local callback was specified, fire it and pass it the data
						if ( s.success )
							s.success( data, status );
	
						// Fire the global callback
						if( s.global )
							jQuery.event.trigger( "ajaxSuccess", [xml, s] );
					} else
						jQuery.handleError(s, xml, status);
				} catch(e) {
					status = "error";
					jQuery.handleError(s, xml, status, e);
				}

				// The request was completed
				if( s.global )
					jQuery.event.trigger( "ajaxComplete", [xml, s] );

				// Handle the global AJAX counter
				if ( s.global && ! --jQuery.active )
					jQuery.event.trigger( "ajaxStop" );

				// Process result
				if ( s.complete )
					s.complete(xml, status);

				// Stop memory leaks
				xml.onreadystatechange = function(){};
				xml = null;
			}
		};
		xml.onreadystatechange = onreadystatechange;

		// Timeout checker
		if ( s.timeout > 0 )
			setTimeout(function(){
				// Check to see if the request is still happening
				if ( xml ) {
					// Cancel the request
					xml.abort();

					if( !requestDone )
						onreadystatechange( "timeout" );
				}
			}, s.timeout);
			
		// save non-leaking reference 
		var xml2 = xml;

		// Send the data
		try {
			xml2.send(s.data);
		} catch(e) {
			jQuery.handleError(s, xml, null, e);
		}
		
		// firefox 1.5 doesn't fire statechange for sync requests
		if ( !s.async )
			onreadystatechange();
		
		// return XMLHttpRequest to allow aborting the request etc.
		return xml2;
	},

	handleError: function( s, xml, status, e ) {
		// If a local callback was specified, fire it
		if ( s.error ) s.error( xml, status, e );

		// Fire the global callback
		if ( s.global )
			jQuery.event.trigger( "ajaxError", [xml, s, e] );
	},

	// Counter for holding the number of active queries
	active: 0,

	// Determines if an XMLHttpRequest was successful or not
	httpSuccess: function( r ) {
		try {
			return !r.status && location.protocol == "file:" ||
				( r.status >= 200 && r.status < 300 ) || r.status == 304 ||
				jQuery.browser.safari && r.status == undefined;
		} catch(e){}
		return false;
	},

	// Determines if an XMLHttpRequest returns NotModified
	httpNotModified: function( xml, url ) {
		try {
			var xmlRes = xml.getResponseHeader("Last-Modified");

			// Firefox always returns 200. check Last-Modified date
			return xml.status == 304 || xmlRes == jQuery.lastModified[url] ||
				jQuery.browser.safari && xml.status == undefined;
		} catch(e){}
		return false;
	},

	/* Get the data out of an XMLHttpRequest.
	 * Return parsed XML if content-type header is "xml" and type is "xml" or omitted,
	 * otherwise return plain text.
	 * (String) data - The type of data that you're expecting back,
	 * (e.g. "xml", "html", "script")
	 */
	httpData: function( r, type ) {
		var ct = r.getResponseHeader("content-type");
		var data = !type && ct && ct.indexOf("xml") >= 0;
		data = type == "xml" || data ? r.responseXML : r.responseText;

		// If the type is "script", eval it in global context
		if ( type == "script" )
			jQuery.globalEval( data );

		// Get the JavaScript object, if JSON is used.
		if ( type == "json" )
			eval( "data = " + data );

		// evaluate scripts within html
		if ( type == "html" )
			jQuery("<div>").html(data).evalScripts();

		return data;
	},

	// Serialize an array of form elements or a set of
	// key/values into a query string
	param: function( a ) {
		var s = [];

		// If an array was passed in, assume that it is an array
		// of form elements
		if ( a.constructor == Array || a.jquery )
			// Serialize the form elements
			jQuery.each( a, function(){
				s.push( encodeURIComponent(this.name) + "=" + encodeURIComponent( this.value ) );
			});

		// Otherwise, assume that it's an object of key/value pairs
		else
			// Serialize the key/values
			for ( var j in a )
				// If the value is an array then the key names need to be repeated
				if ( a[j] && a[j].constructor == Array )
					jQuery.each( a[j], function(){
						s.push( encodeURIComponent(j) + "=" + encodeURIComponent( this ) );
					});
				else
					s.push( encodeURIComponent(j) + "=" + encodeURIComponent( a[j] ) );

		// Return the resulting serialization
		return s.join("&");
	},
	
	// evalulates a script in global context
	// not reliable for safari
	globalEval: function( data ) {
		if ( window.execScript )
			window.execScript( data );
		else if ( jQuery.browser.safari )
			// safari doesn't provide a synchronous global eval
			window.setTimeout( data, 0 );
		else
			eval.call( window, data );
	}

});
}
