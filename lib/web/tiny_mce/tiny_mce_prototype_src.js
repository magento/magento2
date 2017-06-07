(function(win) {
	var whiteSpaceRe = /^\s*|\s*$/g,
		undefined, isRegExpBroken = 'B'.replace(/A(.)|B/, '$1') === '$1';

	var tinymce = {
		majorVersion : '3',

		minorVersion : '4.7',

		releaseDate : '2011-11-03',

		_init : function() {
			var t = this, d = document, na = navigator, ua = na.userAgent, i, nl, n, base, p, v;

			t.isOpera = win.opera && opera.buildNumber;

			t.isWebKit = /WebKit/.test(ua);

			t.isIE = !t.isWebKit && !t.isOpera && (/MSIE/gi).test(ua) && (/Explorer/gi).test(na.appName);

			t.isIE6 = t.isIE && /MSIE [56]/.test(ua);

			t.isIE7 = t.isIE && /MSIE [7]/.test(ua);

			t.isIE8 = t.isIE && /MSIE [8]/.test(ua);

			t.isIE9 = t.isIE && /MSIE [9]/.test(ua);

			t.isGecko = !t.isWebKit && /Gecko/.test(ua);

			t.isMac = ua.indexOf('Mac') != -1;

			t.isAir = /adobeair/i.test(ua);

			t.isIDevice = /(iPad|iPhone)/.test(ua);
			
			t.isIOS5 = t.isIDevice && ua.match(/AppleWebKit\/(\d*)/)[1]>=534;

			// TinyMCE .NET webcontrol might be setting the values for TinyMCE
			if (win.tinyMCEPreInit) {
				t.suffix = tinyMCEPreInit.suffix;
				t.baseURL = tinyMCEPreInit.base;
				t.query = tinyMCEPreInit.query;
				return;
			}

			// Get suffix and base
			t.suffix = '';

			// If base element found, add that infront of baseURL
			nl = d.getElementsByTagName('base');
			for (i=0; i<nl.length; i++) {
				if (v = nl[i].href) {
					// Host only value like http://site.com or http://site.com:8008
					if (/^https?:\/\/[^\/]+$/.test(v))
						v += '/';

					base = v ? v.match(/.*\//)[0] : ''; // Get only directory
				}
			}

			function getBase(n) {
				if (n.src && /tiny_mce(|_gzip|_jquery|_prototype|_full)(_dev|_src)?.js/.test(n.src)) {
					if (/_(src|dev)\.js/g.test(n.src))
						t.suffix = '_src';

					if ((p = n.src.indexOf('?')) != -1)
						t.query = n.src.substring(p + 1);

					t.baseURL = n.src.substring(0, n.src.lastIndexOf('/'));

					// If path to script is relative and a base href was found add that one infront
					// the src property will always be an absolute one on non IE browsers and IE 8
					// so this logic will basically only be executed on older IE versions
					if (base && t.baseURL.indexOf('://') == -1 && t.baseURL.indexOf('/') !== 0)
						t.baseURL = base + t.baseURL;

					return t.baseURL;
				}

				return null;
			};

			// Check document
			nl = d.getElementsByTagName('script');
			for (i=0; i<nl.length; i++) {
				if (getBase(nl[i]))
					return;
			}

			// Check head
			n = d.getElementsByTagName('head')[0];
			if (n) {
				nl = n.getElementsByTagName('script');
				for (i=0; i<nl.length; i++) {
					if (getBase(nl[i]))
						return;
				}
			}

			return;
		},

		is : function(o, t) {
			if (!t)
				return o !== undefined;

			if (t == 'array' && (o.hasOwnProperty && o instanceof Array))
				return true;

			return typeof(o) == t;
		},

		makeMap : function(items, delim, map) {
			var i;

			items = items || [];
			delim = delim || ',';

			if (typeof(items) == "string")
				items = items.split(delim);

			map = map || {};

			i = items.length;
			while (i--)
				map[items[i]] = {};

			return map;
		},

		each : function(o, cb, s) {
			var n, l;

			if (!o)
				return 0;

			s = s || o;

			if (o.length !== undefined) {
				// Indexed arrays, needed for Safari
				for (n=0, l = o.length; n < l; n++) {
					if (cb.call(s, o[n], n, o) === false)
						return 0;
				}
			} else {
				// Hashtables
				for (n in o) {
					if (o.hasOwnProperty(n)) {
						if (cb.call(s, o[n], n, o) === false)
							return 0;
					}
				}
			}

			return 1;
		},


		map : function(a, f) {
			var o = [];

			tinymce.each(a, function(v) {
				o.push(f(v));
			});

			return o;
		},

		grep : function(a, f) {
			var o = [];

			tinymce.each(a, function(v) {
				if (!f || f(v))
					o.push(v);
			});

			return o;
		},

		inArray : function(a, v) {
			var i, l;

			if (a) {
				for (i = 0, l = a.length; i < l; i++) {
					if (a[i] === v)
						return i;
				}
			}

			return -1;
		},

		extend : function(o, e) {
			var i, l, a = arguments;

			for (i = 1, l = a.length; i < l; i++) {
				e = a[i];

				tinymce.each(e, function(v, n) {
					if (v !== undefined)
						o[n] = v;
				});
			}

			return o;
		},


		trim : function(s) {
			return (s ? '' + s : '').replace(whiteSpaceRe, '');
		},

		create : function(s, p, root) {
			var t = this, sp, ns, cn, scn, c, de = 0;

			// Parse : <prefix> <class>:<super class>
			s = /^((static) )?([\w.]+)(:([\w.]+))?/.exec(s);
			cn = s[3].match(/(^|\.)(\w+)$/i)[2]; // Class name

			// Create namespace for new class
			ns = t.createNS(s[3].replace(/\.\w+$/, ''), root);

			// Class already exists
			if (ns[cn])
				return;

			// Make pure static class
			if (s[2] == 'static') {
				ns[cn] = p;

				if (this.onCreate)
					this.onCreate(s[2], s[3], ns[cn]);

				return;
			}

			// Create default constructor
			if (!p[cn]) {
				p[cn] = function() {};
				de = 1;
			}

			// Add constructor and methods
			ns[cn] = p[cn];
			t.extend(ns[cn].prototype, p);

			// Extend
			if (s[5]) {
				sp = t.resolve(s[5]).prototype;
				scn = s[5].match(/\.(\w+)$/i)[1]; // Class name

				// Extend constructor
				c = ns[cn];
				if (de) {
					// Add passthrough constructor
					ns[cn] = function() {
						return sp[scn].apply(this, arguments);
					};
				} else {
					// Add inherit constructor
					ns[cn] = function() {
						this.parent = sp[scn];
						return c.apply(this, arguments);
					};
				}
				ns[cn].prototype[cn] = ns[cn];

				// Add super methods
				t.each(sp, function(f, n) {
					ns[cn].prototype[n] = sp[n];
				});

				// Add overridden methods
				t.each(p, function(f, n) {
					// Extend methods if needed
					if (sp[n]) {
						ns[cn].prototype[n] = function() {
							this.parent = sp[n];
							return f.apply(this, arguments);
						};
					} else {
						if (n != cn)
							ns[cn].prototype[n] = f;
					}
				});
			}

			// Add static methods
			t.each(p['static'], function(f, n) {
				ns[cn][n] = f;
			});

			if (this.onCreate)
				this.onCreate(s[2], s[3], ns[cn].prototype);
		},

		walk : function(o, f, n, s) {
			s = s || this;

			if (o) {
				if (n)
					o = o[n];

				tinymce.each(o, function(o, i) {
					if (f.call(s, o, i, n) === false)
						return false;

					tinymce.walk(o, f, n, s);
				});
			}
		},

		createNS : function(n, o) {
			var i, v;

			o = o || win;

			n = n.split('.');
			for (i=0; i<n.length; i++) {
				v = n[i];

				if (!o[v])
					o[v] = {};

				o = o[v];
			}

			return o;
		},

		resolve : function(n, o) {
			var i, l;

			o = o || win;

			n = n.split('.');
			for (i = 0, l = n.length; i < l; i++) {
				o = o[n[i]];

				if (!o)
					break;
			}

			return o;
		},

		addUnload : function(f, s) {
			var t = this;

			f = {func : f, scope : s || this};

			if (!t.unloads) {
				function unload() {
					var li = t.unloads, o, n;

					if (li) {
						// Call unload handlers
						for (n in li) {
							o = li[n];

							if (o && o.func)
								o.func.call(o.scope, 1); // Send in one arg to distinct unload and user destroy
						}

						// Detach unload function
						if (win.detachEvent) {
							win.detachEvent('onbeforeunload', fakeUnload);
							win.detachEvent('onunload', unload);
						} else if (win.removeEventListener)
							win.removeEventListener('unload', unload, false);

						// Destroy references
						t.unloads = o = li = w = unload = 0;

						// Run garbarge collector on IE
						if (win.CollectGarbage)
							CollectGarbage();
					}
				};

				function fakeUnload() {
					var d = document;

					// Is there things still loading, then do some magic
					if (d.readyState == 'interactive') {
						function stop() {
							// Prevent memory leak
							d.detachEvent('onstop', stop);

							// Call unload handler
							if (unload)
								unload();

							d = 0;
						};

						// Fire unload when the currently loading page is stopped
						if (d)
							d.attachEvent('onstop', stop);

						// Remove onstop listener after a while to prevent the unload function
						// to execute if the user presses cancel in an onbeforeunload
						// confirm dialog and then presses the browser stop button
						win.setTimeout(function() {
							if (d)
								d.detachEvent('onstop', stop);
						}, 0);
					}
				};

				// Attach unload handler
				if (win.attachEvent) {
					win.attachEvent('onunload', unload);
					win.attachEvent('onbeforeunload', fakeUnload);
				} else if (win.addEventListener)
					win.addEventListener('unload', unload, false);

				// Setup initial unload handler array
				t.unloads = [f];
			} else
				t.unloads.push(f);

			return f;
		},

		removeUnload : function(f) {
			var u = this.unloads, r = null;

			tinymce.each(u, function(o, i) {
				if (o && o.func == f) {
					u.splice(i, 1);
					r = f;
					return false;
				}
			});

			return r;
		},

		explode : function(s, d) {
			return s ? tinymce.map(s.split(d || ','), tinymce.trim) : s;
		},

		_addVer : function(u) {
			var v;

			if (!this.query)
				return u;

			v = (u.indexOf('?') == -1 ? '?' : '&') + this.query;

			if (u.indexOf('#') == -1)
				return u + v;

			return u.replace('#', v + '#');
		},

		// Fix function for IE 9 where regexps isn't working correctly
		// Todo: remove me once MS fixes the bug
		_replace : function(find, replace, str) {
			// On IE9 we have to fake $x replacement
			if (isRegExpBroken) {
				return str.replace(find, function() {
					var val = replace, args = arguments, i;

					for (i = 0; i < args.length - 2; i++) {
						if (args[i] === undefined) {
							val = val.replace(new RegExp('\\$' + i, 'g'), '');
						} else {
							val = val.replace(new RegExp('\\$' + i, 'g'), args[i]);
						}
					}

					return val;
				});
			}

			return str.replace(find, replace);
		}

		};

	// Initialize the API
	tinymce._init();

	// Expose tinymce namespace to the global namespace (window)
	win.tinymce = win.tinyMCE = tinymce;

	// Describe the different namespaces

	})(window);



(function() {
	if (!window.Prototype)
		return alert("Load prototype first!");

	// Patch in core NS functions
	tinymce.extend(tinymce, {
		trim : function(s) {return s ? s.strip() : '';},
		inArray : function(a, v) {return a && a.indexOf ? a.indexOf(v) : -1;}
	});

	// Patch in functions in various clases
	// Add a "#ifndefjquery" statement around each core API function you add below
	var patches = {
		'tinymce.util.JSON' : {
			/*serialize : function(o) {
				return o.toJSON();
			}*/
		},
	};

	// Patch functions after a class is created
	tinymce.onCreate = function(ty, c, p) {
		tinymce.extend(p, patches[c]);
	};
})();


tinymce.create('tinymce.util.Dispatcher', {
	scope : null,
	listeners : null,

	Dispatcher : function(s) {
		this.scope = s || this;
		this.listeners = [];
	},

	add : function(cb, s) {
		this.listeners.push({cb : cb, scope : s || this.scope});

		return cb;
	},

	addToTop : function(cb, s) {
		this.listeners.unshift({cb : cb, scope : s || this.scope});

		return cb;
	},

	remove : function(cb) {
		var l = this.listeners, o = null;

		tinymce.each(l, function(c, i) {
			if (cb == c.cb) {
				o = cb;
				l.splice(i, 1);
				return false;
			}
		});

		return o;
	},

	dispatch : function() {
		var s, a = arguments, i, li = this.listeners, c;

		// Needs to be a real loop since the listener count might change while looping
		// And this is also more efficient
		for (i = 0; i<li.length; i++) {
			c = li[i];
			s = c.cb.apply(c.scope, a);

			if (s === false)
				break;
		}

		return s;
	}

	});

(function() {
	var each = tinymce.each;

	tinymce.create('tinymce.util.URI', {
		URI : function(u, s) {
			var t = this, o, a, b, base_url;

			// Trim whitespace
			u = tinymce.trim(u);

			// Default settings
			s = t.settings = s || {};

			// Strange app protocol that isn't http/https or local anchor
			// For example: mailto,skype,tel etc.
			if (/^([\w\-]+):([^\/]{2})/i.test(u) || /^\s*#/.test(u)) {
				t.source = u;
				return;
			}

			// Absolute path with no host, fake host and protocol
			if (u.indexOf('/') === 0 && u.indexOf('//') !== 0)
				u = (s.base_uri ? s.base_uri.protocol || 'http' : 'http') + '://mce_host' + u;

			// Relative path http:// or protocol relative //path
			if (!/^[\w-]*:?\/\//.test(u)) {
				base_url = s.base_uri ? s.base_uri.path : new tinymce.util.URI(location.href).directory;
				u = ((s.base_uri && s.base_uri.protocol) || 'http') + '://mce_host' + t.toAbsPath(base_url, u);
			}

			// Parse URL (Credits goes to Steave, http://blog.stevenlevithan.com/archives/parseuri)
			u = u.replace(/@@/g, '(mce_at)'); // Zope 3 workaround, they use @@something
			u = /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/.exec(u);
			each(["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"], function(v, i) {
				var s = u[i];

				// Zope 3 workaround, they use @@something
				if (s)
					s = s.replace(/\(mce_at\)/g, '@@');

				t[v] = s;
			});

			if (b = s.base_uri) {
				if (!t.protocol)
					t.protocol = b.protocol;

				if (!t.userInfo)
					t.userInfo = b.userInfo;

				if (!t.port && t.host == 'mce_host')
					t.port = b.port;

				if (!t.host || t.host == 'mce_host')
					t.host = b.host;

				t.source = '';
			}

			//t.path = t.path || '/';
		},

		setPath : function(p) {
			var t = this;

			p = /^(.*?)\/?(\w+)?$/.exec(p);

			// Update path parts
			t.path = p[0];
			t.directory = p[1];
			t.file = p[2];

			// Rebuild source
			t.source = '';
			t.getURI();
		},

		toRelative : function(u) {
			var t = this, o;

			if (u === "./")
				return u;

			u = new tinymce.util.URI(u, {base_uri : t});

			// Not on same domain/port or protocol
			if ((u.host != 'mce_host' && t.host != u.host && u.host) || t.port != u.port || t.protocol != u.protocol)
				return u.getURI();

			o = t.toRelPath(t.path, u.path);

			// Add query
			if (u.query)
				o += '?' + u.query;

			// Add anchor
			if (u.anchor)
				o += '#' + u.anchor;

			return o;
		},
	
		toAbsolute : function(u, nh) {
			var u = new tinymce.util.URI(u, {base_uri : this});

			return u.getURI(this.host == u.host && this.protocol == u.protocol ? nh : 0);
		},

		toRelPath : function(base, path) {
			var items, bp = 0, out = '', i, l;

			// Split the paths
			base = base.substring(0, base.lastIndexOf('/'));
			base = base.split('/');
			items = path.split('/');

			if (base.length >= items.length) {
				for (i = 0, l = base.length; i < l; i++) {
					if (i >= items.length || base[i] != items[i]) {
						bp = i + 1;
						break;
					}
				}
			}

			if (base.length < items.length) {
				for (i = 0, l = items.length; i < l; i++) {
					if (i >= base.length || base[i] != items[i]) {
						bp = i + 1;
						break;
					}
				}
			}

			if (bp == 1)
				return path;

			for (i = 0, l = base.length - (bp - 1); i < l; i++)
				out += "../";

			for (i = bp - 1, l = items.length; i < l; i++) {
				if (i != bp - 1)
					out += "/" + items[i];
				else
					out += items[i];
			}

			return out;
		},

		toAbsPath : function(base, path) {
			var i, nb = 0, o = [], tr, outPath;

			// Split paths
			tr = /\/$/.test(path) ? '/' : '';
			base = base.split('/');
			path = path.split('/');

			// Remove empty chunks
			each(base, function(k) {
				if (k)
					o.push(k);
			});

			base = o;

			// Merge relURLParts chunks
			for (i = path.length - 1, o = []; i >= 0; i--) {
				// Ignore empty or .
				if (path[i].length == 0 || path[i] == ".")
					continue;

				// Is parent
				if (path[i] == '..') {
					nb++;
					continue;
				}

				// Move up
				if (nb > 0) {
					nb--;
					continue;
				}

				o.push(path[i]);
			}

			i = base.length - nb;

			// If /a/b/c or /
			if (i <= 0)
				outPath = o.reverse().join('/');
			else
				outPath = base.slice(0, i).join('/') + '/' + o.reverse().join('/');

			// Add front / if it's needed
			if (outPath.indexOf('/') !== 0)
				outPath = '/' + outPath;

			// Add traling / if it's needed
			if (tr && outPath.lastIndexOf('/') !== outPath.length - 1)
				outPath += tr;

			return outPath;
		},

		getURI : function(nh) {
			var s, t = this;

			// Rebuild source
			if (!t.source || nh) {
				s = '';

				if (!nh) {
					if (t.protocol)
						s += t.protocol + '://';

					if (t.userInfo)
						s += t.userInfo + '@';

					if (t.host)
						s += t.host;

					if (t.port)
						s += ':' + t.port;
				}

				if (t.path)
					s += t.path;

				if (t.query)
					s += '?' + t.query;

				if (t.anchor)
					s += '#' + t.anchor;

				t.source = s;
			}

			return t.source;
		}
	});
})();

(function() {
	var each = tinymce.each;

	tinymce.create('static tinymce.util.Cookie', {
		getHash : function(n) {
			var v = this.get(n), h;

			if (v) {
				each(v.split('&'), function(v) {
					v = v.split('=');
					h = h || {};
					h[unescape(v[0])] = unescape(v[1]);
				});
			}

			return h;
		},

		setHash : function(n, v, e, p, d, s) {
			var o = '';

			each(v, function(v, k) {
				o += (!o ? '' : '&') + escape(k) + '=' + escape(v);
			});

			this.set(n, o, e, p, d, s);
		},

		get : function(n) {
			var c = document.cookie, e, p = n + "=", b;

			// Strict mode
			if (!c)
				return;

			b = c.indexOf("; " + p);

			if (b == -1) {
				b = c.indexOf(p);

				if (b != 0)
					return null;
			} else
				b += 2;

			e = c.indexOf(";", b);

			if (e == -1)
				e = c.length;

			return unescape(c.substring(b + p.length, e));
		},

		set : function(n, v, e, p, d, s) {
			document.cookie = n + "=" + escape(v) +
				((e) ? "; expires=" + e.toGMTString() : "") +
				((p) ? "; path=" + escape(p) : "") +
				((d) ? "; domain=" + d : "") +
				((s) ? "; secure" : "");
		},

		remove : function(n, p) {
			var d = new Date();

			d.setTime(d.getTime() - 1000);

			this.set(n, '', d, p, d);
		}
	});
})();

(function() {
	function serialize(o, quote) {
		var i, v, t;

		quote = quote || '"';

		if (o == null)
			return 'null';

		t = typeof o;

		if (t == 'string') {
			v = '\bb\tt\nn\ff\rr\""\'\'\\\\';

			return quote + o.replace(/([\u0080-\uFFFF\x00-\x1f\"\'\\])/g, function(a, b) {
				// Make sure single quotes never get encoded inside double quotes for JSON compatibility
				if (quote === '"' && a === "'")
					return a;

				i = v.indexOf(b);

				if (i + 1)
					return '\\' + v.charAt(i + 1);

				a = b.charCodeAt().toString(16);

				return '\\u' + '0000'.substring(a.length) + a;
			}) + quote;
		}

		if (t == 'object') {
			if (o.hasOwnProperty && o instanceof Array) {
					for (i=0, v = '['; i<o.length; i++)
						v += (i > 0 ? ',' : '') + serialize(o[i], quote);

					return v + ']';
				}

				v = '{';

				for (i in o) {
					if (o.hasOwnProperty(i)) {
						v += typeof o[i] != 'function' ? (v.length > 1 ? ',' + quote : quote) + i + quote +':' + serialize(o[i], quote) : '';
					}
				}

				return v + '}';
		}

		return '' + o;
	};

	tinymce.util.JSON = {
		serialize: serialize,

		parse: function(s) {
			try {
				return eval('(' + s + ')');
			} catch (ex) {
				// Ignore
			}
		}

		};
})();

tinymce.create('static tinymce.util.XHR', {
	send : function(o) {
		var x, t, w = window, c = 0;

		// Default settings
		o.scope = o.scope || this;
		o.success_scope = o.success_scope || o.scope;
		o.error_scope = o.error_scope || o.scope;
		o.async = o.async === false ? false : true;
		o.data = o.data || '';

		function get(s) {
			x = 0;

			try {
				x = new ActiveXObject(s);
			} catch (ex) {
			}

			return x;
		};

		x = w.XMLHttpRequest ? new XMLHttpRequest() : get('Microsoft.XMLHTTP') || get('Msxml2.XMLHTTP');

		if (x) {
			if (x.overrideMimeType)
				x.overrideMimeType(o.content_type);

			x.open(o.type || (o.data ? 'POST' : 'GET'), o.url, o.async);

			if (o.content_type)
				x.setRequestHeader('Content-Type', o.content_type);

			x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			x.send(o.data);

			function ready() {
				if (!o.async || x.readyState == 4 || c++ > 10000) {
					if (o.success && c < 10000 && x.status == 200)
						o.success.call(o.success_scope, '' + x.responseText, x, o);
					else if (o.error)
						o.error.call(o.error_scope, c > 10000 ? 'TIMED_OUT' : 'GENERAL', x, o);

					x = null;
				} else
					w.setTimeout(ready, 10);
			};

			// Syncronous request
			if (!o.async)
				return ready();

			// Wait for response, onReadyStateChange can not be used since it leaks memory in IE
			t = w.setTimeout(ready, 10);
		}
	}
});

(function() {
	var extend = tinymce.extend, JSON = tinymce.util.JSON, XHR = tinymce.util.XHR;

	tinymce.create('tinymce.util.JSONRequest', {
		JSONRequest : function(s) {
			this.settings = extend({
			}, s);
			this.count = 0;
		},

		send : function(o) {
			var ecb = o.error, scb = o.success;

			o = extend(this.settings, o);

			o.success = function(c, x) {
				c = JSON.parse(c);

				if (typeof(c) == 'undefined') {
					c = {
						error : 'JSON Parse error.'
					};
				}

				if (c.error)
					ecb.call(o.error_scope || o.scope, c.error, x);
				else
					scb.call(o.success_scope || o.scope, c.result);
			};

			o.error = function(ty, x) {
				if (ecb)
					ecb.call(o.error_scope || o.scope, ty, x);
			};

			o.data = JSON.serialize({
				id : o.id || 'c' + (this.count++),
				method : o.method,
				params : o.params
			});

			// JSON content type for Ruby on rails. Bug: #1883287
			o.content_type = 'application/json';

			XHR.send(o);
		},

		'static' : {
			sendRPC : function(o) {
				return new tinymce.util.JSONRequest().send(o);
			}
		}
	});
}());
(function(tinymce){
	tinymce.VK = {
		DELETE: 46,
		BACKSPACE: 8,
		ENTER: 13,
		TAB: 9,
        SPACEBAR: 32,
		UP: 38,
		DOWN: 40
	}
})(tinymce);

(function(tinymce) {
	var VK = tinymce.VK, BACKSPACE = VK.BACKSPACE, DELETE = VK.DELETE;

	function cleanupStylesWhenDeleting(ed) {
		var dom = ed.dom, selection = ed.selection;

		ed.onKeyDown.add(function(ed, e) {
			var rng, blockElm, node, clonedSpan, isDelete;

			isDelete = e.keyCode == DELETE;
			if (isDelete || e.keyCode == BACKSPACE) {
				e.preventDefault();
				rng = selection.getRng();

				// Find root block
				blockElm = dom.getParent(rng.startContainer, dom.isBlock);

				// On delete clone the root span of the next block element
				if (isDelete)
					blockElm = dom.getNext(blockElm, dom.isBlock);

				// Locate root span element and clone it since it would otherwise get merged by the "apple-style-span" on delete/backspace
				if (blockElm) {
					node = blockElm.firstChild;

					// Ignore empty text nodes
					while (node && node.nodeType == 3 && node.nodeValue.length == 0)
						node = node.nextSibling;

					if (node && node.nodeName === 'SPAN') {
						clonedSpan = node.cloneNode(false);
					}
				}

				// Do the backspace/delete actiopn
				ed.getDoc().execCommand(isDelete ? 'ForwardDelete' : 'Delete', false, null);

				// Find all odd apple-style-spans
				blockElm = dom.getParent(rng.startContainer, dom.isBlock);
				tinymce.each(dom.select('span.Apple-style-span,font.Apple-style-span', blockElm), function(span) {
					var bm = selection.getBookmark();

					if (clonedSpan) {
						dom.replace(clonedSpan.cloneNode(false), span, true);
					} else {
						dom.remove(span, true);
					}

					// Restore the selection
					selection.moveToBookmark(bm);
				});
			}
		});
	};

	function emptyEditorWhenDeleting(ed) {
		ed.onKeyUp.add(function(ed, e) {
			var keyCode = e.keyCode;

			if (keyCode == DELETE || keyCode == BACKSPACE) {
				if (ed.dom.isEmpty(ed.getBody())) {
					ed.setContent('', {format : 'raw'});
					ed.nodeChanged();
					return;
				}
			}
		});
	};

	function inputMethodFocus(ed) {
		ed.dom.bind(ed.getDoc(), 'focusin', function() {
			ed.selection.setRng(ed.selection.getRng());
		});
	};

	function removeHrOnBackspace(ed) {
		ed.onKeyDown.add(function(ed, e) {
			if (e.keyCode === BACKSPACE) {
				if (ed.selection.isCollapsed() && ed.selection.getRng(true).startOffset === 0) {
					var node = ed.selection.getNode();
					var previousSibling = node.previousSibling;
					if (previousSibling && previousSibling.nodeName && previousSibling.nodeName.toLowerCase() === "hr") {
						ed.dom.remove(previousSibling);
						tinymce.dom.Event.cancel(e);
					}
				}
			}
		})
	}

	function focusBody(ed) {
		// Fix for a focus bug in FF 3.x where the body element
		// wouldn't get proper focus if the user clicked on the HTML element
		if (!Range.prototype.getClientRects) { // Detect getClientRects got introduced in FF 4
			ed.onMouseDown.add(function(ed, e) {
				if (e.target.nodeName === "HTML") {
					var body = ed.getBody();

					// Blur the body it's focused but not correctly focused
					body.blur();

					// Refocus the body after a little while
					setTimeout(function() {
						body.focus();
					}, 0);
				}
			});
		}
	};

	function selectControlElements(ed) {
		ed.onClick.add(function(ed, e) {
			e = e.target;

			if (/^(IMG|HR)$/.test(e.nodeName))
				ed.selection.select(e);

			if (e.nodeName == 'A' && ed.dom.hasClass(e, 'mceItemAnchor'))
				ed.selection.select(e);

			ed.nodeChanged();
		});
	};

	function selectionChangeNodeChanged(ed) {
		var lastRng, selectionTimer;

		ed.dom.bind(ed.getDoc(), 'selectionchange', function() {
			if (selectionTimer) {
				clearTimeout(selectionTimer);
				selectionTimer = 0;
			}

			selectionTimer = window.setTimeout(function() {
				var rng = ed.selection.getRng();

				// Compare the ranges to see if it was a real change or not
				if (!lastRng || !tinymce.dom.RangeUtils.compareRanges(rng, lastRng)) {
					ed.nodeChanged();
					lastRng = rng;
				}
			}, 50);
		});
	}

	function ensureBodyHasRoleApplication(ed) {
		document.body.setAttribute("role", "application");
	}

	tinymce.create('tinymce.util.Quirks', {
		Quirks: function(ed) {
			// WebKit
			if (tinymce.isWebKit) {
				cleanupStylesWhenDeleting(ed);
				emptyEditorWhenDeleting(ed);
				inputMethodFocus(ed);
				selectControlElements(ed);

				// iOS
				if (tinymce.isIDevice) {
					selectionChangeNodeChanged(ed);
				}
			}

			// IE
			if (tinymce.isIE) {
				removeHrOnBackspace(ed);
				emptyEditorWhenDeleting(ed);
				ensureBodyHasRoleApplication(ed);
			}

			// Gecko
			if (tinymce.isGecko) {
				removeHrOnBackspace(ed);
				focusBody(ed);
			}
		}
	});
})(tinymce);

(function(tinymce) {
	var namedEntities, baseEntities, reverseEntities,
		attrsCharsRegExp = /[&<>\"\u007E-\uD7FF\uE000-\uFFEF]|[\uD800-\uDBFF][\uDC00-\uDFFF]/g,
		textCharsRegExp = /[<>&\u007E-\uD7FF\uE000-\uFFEF]|[\uD800-\uDBFF][\uDC00-\uDFFF]/g,
		rawCharsRegExp = /[<>&\"\']/g,
		entityRegExp = /&(#x|#)?([\w]+);/g,
		asciiMap = {
				128 : "\u20AC", 130 : "\u201A", 131 : "\u0192", 132 : "\u201E", 133 : "\u2026", 134 : "\u2020",
				135 : "\u2021", 136 : "\u02C6", 137 : "\u2030", 138 : "\u0160", 139 : "\u2039", 140 : "\u0152",
				142 : "\u017D", 145 : "\u2018", 146 : "\u2019", 147 : "\u201C", 148 : "\u201D", 149 : "\u2022",
				150 : "\u2013", 151 : "\u2014", 152 : "\u02DC", 153 : "\u2122", 154 : "\u0161", 155 : "\u203A",
				156 : "\u0153", 158 : "\u017E", 159 : "\u0178"
		};

	// Raw entities
	baseEntities = {
		'\"' : '&quot;', // Needs to be escaped since the YUI compressor would otherwise break the code
		"'" : '&#39;',
		'<' : '&lt;',
		'>' : '&gt;',
		'&' : '&amp;'
	};

	// Reverse lookup table for raw entities
	reverseEntities = {
		'&lt;' : '<',
		'&gt;' : '>',
		'&amp;' : '&',
		'&quot;' : '"',
		'&apos;' : "'"
	};

	// Decodes text by using the browser
	function nativeDecode(text) {
		var elm;

		elm = document.createElement("div");
		elm.innerHTML = text;

		return elm.textContent || elm.innerText || text;
	};

	// Build a two way lookup table for the entities
	function buildEntitiesLookup(items, radix) {
		var i, chr, entity, lookup = {};

		if (items) {
			items = items.split(',');
			radix = radix || 10;

			// Build entities lookup table
			for (i = 0; i < items.length; i += 2) {
				chr = String.fromCharCode(parseInt(items[i], radix));

				// Only add non base entities
				if (!baseEntities[chr]) {
					entity = '&' + items[i + 1] + ';';
					lookup[chr] = entity;
					lookup[entity] = chr;
				}
			}

			return lookup;
		}
	};

	// Unpack entities lookup where the numbers are in radix 32 to reduce the size
	namedEntities = buildEntitiesLookup(
		'50,nbsp,51,iexcl,52,cent,53,pound,54,curren,55,yen,56,brvbar,57,sect,58,uml,59,copy,' +
		'5a,ordf,5b,laquo,5c,not,5d,shy,5e,reg,5f,macr,5g,deg,5h,plusmn,5i,sup2,5j,sup3,5k,acute,' +
		'5l,micro,5m,para,5n,middot,5o,cedil,5p,sup1,5q,ordm,5r,raquo,5s,frac14,5t,frac12,5u,frac34,' +
		'5v,iquest,60,Agrave,61,Aacute,62,Acirc,63,Atilde,64,Auml,65,Aring,66,AElig,67,Ccedil,' +
		'68,Egrave,69,Eacute,6a,Ecirc,6b,Euml,6c,Igrave,6d,Iacute,6e,Icirc,6f,Iuml,6g,ETH,6h,Ntilde,' +
		'6i,Ograve,6j,Oacute,6k,Ocirc,6l,Otilde,6m,Ouml,6n,times,6o,Oslash,6p,Ugrave,6q,Uacute,' +
		'6r,Ucirc,6s,Uuml,6t,Yacute,6u,THORN,6v,szlig,70,agrave,71,aacute,72,acirc,73,atilde,74,auml,' +
		'75,aring,76,aelig,77,ccedil,78,egrave,79,eacute,7a,ecirc,7b,euml,7c,igrave,7d,iacute,7e,icirc,' +
		'7f,iuml,7g,eth,7h,ntilde,7i,ograve,7j,oacute,7k,ocirc,7l,otilde,7m,ouml,7n,divide,7o,oslash,' +
		'7p,ugrave,7q,uacute,7r,ucirc,7s,uuml,7t,yacute,7u,thorn,7v,yuml,ci,fnof,sh,Alpha,si,Beta,' +
		'sj,Gamma,sk,Delta,sl,Epsilon,sm,Zeta,sn,Eta,so,Theta,sp,Iota,sq,Kappa,sr,Lambda,ss,Mu,' +
		'st,Nu,su,Xi,sv,Omicron,t0,Pi,t1,Rho,t3,Sigma,t4,Tau,t5,Upsilon,t6,Phi,t7,Chi,t8,Psi,' +
		't9,Omega,th,alpha,ti,beta,tj,gamma,tk,delta,tl,epsilon,tm,zeta,tn,eta,to,theta,tp,iota,' +
		'tq,kappa,tr,lambda,ts,mu,tt,nu,tu,xi,tv,omicron,u0,pi,u1,rho,u2,sigmaf,u3,sigma,u4,tau,' +
		'u5,upsilon,u6,phi,u7,chi,u8,psi,u9,omega,uh,thetasym,ui,upsih,um,piv,812,bull,816,hellip,' +
		'81i,prime,81j,Prime,81u,oline,824,frasl,88o,weierp,88h,image,88s,real,892,trade,89l,alefsym,' +
		'8cg,larr,8ch,uarr,8ci,rarr,8cj,darr,8ck,harr,8dl,crarr,8eg,lArr,8eh,uArr,8ei,rArr,8ej,dArr,' +
		'8ek,hArr,8g0,forall,8g2,part,8g3,exist,8g5,empty,8g7,nabla,8g8,isin,8g9,notin,8gb,ni,8gf,prod,' +
		'8gh,sum,8gi,minus,8gn,lowast,8gq,radic,8gt,prop,8gu,infin,8h0,ang,8h7,and,8h8,or,8h9,cap,8ha,cup,' +
		'8hb,int,8hk,there4,8hs,sim,8i5,cong,8i8,asymp,8j0,ne,8j1,equiv,8j4,le,8j5,ge,8k2,sub,8k3,sup,8k4,' +
		'nsub,8k6,sube,8k7,supe,8kl,oplus,8kn,otimes,8l5,perp,8m5,sdot,8o8,lceil,8o9,rceil,8oa,lfloor,8ob,' +
		'rfloor,8p9,lang,8pa,rang,9ea,loz,9j0,spades,9j3,clubs,9j5,hearts,9j6,diams,ai,OElig,aj,oelig,b0,' +
		'Scaron,b1,scaron,bo,Yuml,m6,circ,ms,tilde,802,ensp,803,emsp,809,thinsp,80c,zwnj,80d,zwj,80e,lrm,' +
		'80f,rlm,80j,ndash,80k,mdash,80o,lsquo,80p,rsquo,80q,sbquo,80s,ldquo,80t,rdquo,80u,bdquo,810,dagger,' +
		'811,Dagger,81g,permil,81p,lsaquo,81q,rsaquo,85c,euro'
	, 32);

	tinymce.html = tinymce.html || {};

	tinymce.html.Entities = {
		encodeRaw : function(text, attr) {
			return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function(chr) {
				return baseEntities[chr] || chr;
			});
		},

		encodeAllRaw : function(text) {
			return ('' + text).replace(rawCharsRegExp, function(chr) {
				return baseEntities[chr] || chr;
			});
		},

		encodeNumeric : function(text, attr) {
			return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function(chr) {
				// Multi byte sequence convert it to a single entity
				if (chr.length > 1)
					return '&#' + (((chr.charCodeAt(0) - 0xD800) * 0x400) + (chr.charCodeAt(1) - 0xDC00) + 0x10000) + ';';

				return baseEntities[chr] || '&#' + chr.charCodeAt(0) + ';';
			});
		},

		encodeNamed : function(text, attr, entities) {
			entities = entities || namedEntities;

			return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function(chr) {
				return baseEntities[chr] || entities[chr] || chr;
			});
		},

		getEncodeFunc : function(name, entities) {
			var Entities = tinymce.html.Entities;

			entities = buildEntitiesLookup(entities) || namedEntities;

			function encodeNamedAndNumeric(text, attr) {
				return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function(chr) {
					return baseEntities[chr] || entities[chr] || '&#' + chr.charCodeAt(0) + ';' || chr;
				});
			};

			function encodeCustomNamed(text, attr) {
				return Entities.encodeNamed(text, attr, entities);
			};

			// Replace + with , to be compatible with previous TinyMCE versions
			name = tinymce.makeMap(name.replace(/\+/g, ','));

			// Named and numeric encoder
			if (name.named && name.numeric)
				return encodeNamedAndNumeric;

			// Named encoder
			if (name.named) {
				// Custom names
				if (entities)
					return encodeCustomNamed;

				return Entities.encodeNamed;
			}

			// Numeric
			if (name.numeric)
				return Entities.encodeNumeric;

			// Raw encoder
			return Entities.encodeRaw;
		},

		decode : function(text) {
			return text.replace(entityRegExp, function(all, numeric, value) {
				if (numeric) {
					value = parseInt(value, numeric.length === 2 ? 16 : 10);

					// Support upper UTF
					if (value > 0xFFFF) {
						value -= 0x10000;

						return String.fromCharCode(0xD800 + (value >> 10), 0xDC00 + (value & 0x3FF));
					} else
						return asciiMap[value] || String.fromCharCode(value);
				}

				return reverseEntities[all] || namedEntities[all] || nativeDecode(all);
			});
		}
	};
})(tinymce);

tinymce.html.Styles = function(settings, schema) {
	var rgbRegExp = /rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/gi,
		urlOrStrRegExp = /(?:url(?:(?:\(\s*\"([^\"]+)\"\s*\))|(?:\(\s*\'([^\']+)\'\s*\))|(?:\(\s*([^)\s]+)\s*\))))|(?:\'([^\']+)\')|(?:\"([^\"]+)\")/gi,
		styleRegExp = /\s*([^:]+):\s*([^;]+);?/g,
		trimRightRegExp = /\s+$/,
		urlColorRegExp = /rgb/,
		undef, i, encodingLookup = {}, encodingItems;

	settings = settings || {};

	encodingItems = '\\" \\\' \\; \\: ; : \uFEFF'.split(' ');
	for (i = 0; i < encodingItems.length; i++) {
		encodingLookup[encodingItems[i]] = '\uFEFF' + i;
		encodingLookup['\uFEFF' + i] = encodingItems[i];
	}

	function toHex(match, r, g, b) {
		function hex(val) {
			val = parseInt(val).toString(16);

			return val.length > 1 ? val : '0' + val; // 0 -> 00
		};

		return '#' + hex(r) + hex(g) + hex(b);
	};

	return {
		toHex : function(color) {
			return color.replace(rgbRegExp, toHex);
		},

		parse : function(css) {
			var styles = {}, matches, name, value, isEncoded, urlConverter = settings.url_converter, urlConverterScope = settings.url_converter_scope || this;

			function compress(prefix, suffix) {
				var top, right, bottom, left;

				// Get values and check it it needs compressing
				top = styles[prefix + '-top' + suffix];
				if (!top)
					return;

				right = styles[prefix + '-right' + suffix];
				if (top != right)
					return;

				bottom = styles[prefix + '-bottom' + suffix];
				if (right != bottom)
					return;

				left = styles[prefix + '-left' + suffix];
				if (bottom != left)
					return;

				// Compress
				styles[prefix + suffix] = left;
				delete styles[prefix + '-top' + suffix];
				delete styles[prefix + '-right' + suffix];
				delete styles[prefix + '-bottom' + suffix];
				delete styles[prefix + '-left' + suffix];
			};

			function canCompress(key) {
				var value = styles[key], i;

				if (!value || value.indexOf(' ') < 0)
					return;

				value = value.split(' ');
				i = value.length;
				while (i--) {
					if (value[i] !== value[0])
						return false;
				}

				styles[key] = value[0];

				return true;
			};

			function compress2(target, a, b, c) {
				if (!canCompress(a))
					return;

				if (!canCompress(b))
					return;

				if (!canCompress(c))
					return;

				// Compress
				styles[target] = styles[a] + ' ' + styles[b] + ' ' + styles[c];
				delete styles[a];
				delete styles[b];
				delete styles[c];
			};

			// Encodes the specified string by replacing all \" \' ; : with _<num>
			function encode(str) {
				isEncoded = true;

				return encodingLookup[str];
			};

			// Decodes the specified string by replacing all _<num> with it's original value \" \' etc
			// It will also decode the \" \' if keep_slashes is set to fale or omitted
			function decode(str, keep_slashes) {
				if (isEncoded) {
					str = str.replace(/\uFEFF[0-9]/g, function(str) {
						return encodingLookup[str];
					});
				}

				if (!keep_slashes)
					str = str.replace(/\\([\'\";:])/g, "$1");

				return str;
			}

			if (css) {
				// Encode \" \' % and ; and : inside strings so they don't interfere with the style parsing
				css = css.replace(/\\[\"\';:\uFEFF]/g, encode).replace(/\"[^\"]+\"|\'[^\']+\'/g, function(str) {
					return str.replace(/[;:]/g, encode);
				});

				// Parse styles
				while (matches = styleRegExp.exec(css)) {
					name = matches[1].replace(trimRightRegExp, '').toLowerCase();
					value = matches[2].replace(trimRightRegExp, '');

					if (name && value.length > 0) {
						// Opera will produce 700 instead of bold in their style values
						if (name === 'font-weight' && value === '700')
							value = 'bold';
						else if (name === 'color' || name === 'background-color') // Lowercase colors like RED
							value = value.toLowerCase();		

						// Convert RGB colors to HEX
						value = value.replace(rgbRegExp, toHex);

						// Convert URLs and force them into url('value') format
						value = value.replace(urlOrStrRegExp, function(match, url, url2, url3, str, str2) {
							str = str || str2;

							if (str) {
								str = decode(str);

								// Force strings into single quote format
								return "'" + str.replace(/\'/g, "\\'") + "'";
							}

							url = decode(url || url2 || url3);

							// Convert the URL to relative/absolute depending on config
							if (urlConverter)
								url = urlConverter.call(urlConverterScope, url, 'style');

							// Output new URL format
							return "url('" + url.replace(/\'/g, "\\'") + "')";
						});

						styles[name] = isEncoded ? decode(value, true) : value;
					}

					styleRegExp.lastIndex = matches.index + matches[0].length;
				}

				// Compress the styles to reduce it's size for example IE will expand styles
				compress("border", "");
				compress("border", "-width");
				compress("border", "-color");
				compress("border", "-style");
				compress("padding", "");
				compress("margin", "");
				compress2('border', 'border-width', 'border-style', 'border-color');

				// Remove pointless border, IE produces these
				if (styles.border === 'medium none')
					delete styles.border;
			}

			return styles;
		},

		serialize : function(styles, element_name) {
			var css = '', name, value;

			function serializeStyles(name) {
				var styleList, i, l, value;

				styleList = schema.styles[name];
				if (styleList) {
					for (i = 0, l = styleList.length; i < l; i++) {
						name = styleList[i];
						value = styles[name];

						if (value !== undef && value.length > 0)
							css += (css.length > 0 ? ' ' : '') + name + ': ' + value + ';';
					}
				}
			};

			// Serialize styles according to schema
			if (element_name && schema && schema.styles) {
				// Serialize global styles and element specific styles
				serializeStyles('*');
				serializeStyles(element_name);
			} else {
				// Output the styles in the order they are inside the object
				for (name in styles) {
					value = styles[name];

					if (value !== undef && value.length > 0)
						css += (css.length > 0 ? ' ' : '') + name + ': ' + value + ';';
				}
			}

			return css;
		}
	};
};

(function(tinymce) {
	var transitional = {}, boolAttrMap, blockElementsMap, shortEndedElementsMap, nonEmptyElementsMap, customElementsMap = {},
		defaultWhiteSpaceElementsMap, selfClosingElementsMap, makeMap = tinymce.makeMap, each = tinymce.each;

	function split(str, delim) {
		return str.split(delim || ',');
	};

	function unpack(lookup, data) {
		var key, elements = {};

		function replace(value) {
			return value.replace(/[A-Z]+/g, function(key) {
				return replace(lookup[key]);
			});
		};

		// Unpack lookup
		for (key in lookup) {
			if (lookup.hasOwnProperty(key))
				lookup[key] = replace(lookup[key]);
		}

		// Unpack and parse data into object map
		replace(data).replace(/#/g, '#text').replace(/(\w+)\[([^\]]+)\]\[([^\]]*)\]/g, function(str, name, attributes, children) {
			attributes = split(attributes, '|');

			elements[name] = {
				attributes : makeMap(attributes),
				attributesOrder : attributes,
				children : makeMap(children, '|', {'#comment' : {}})
			}
		});

		return elements;
	};

	// Build a lookup table for block elements both lowercase and uppercase
	blockElementsMap = 'h1,h2,h3,h4,h5,h6,hr,p,div,address,pre,form,table,tbody,thead,tfoot,' + 
						'th,tr,td,li,ol,ul,caption,blockquote,center,dl,dt,dd,dir,fieldset,' + 
						'noscript,menu,isindex,samp,header,footer,article,section,hgroup';
	blockElementsMap = makeMap(blockElementsMap, ',', makeMap(blockElementsMap.toUpperCase()));

	// This is the XHTML 1.0 transitional elements with it's attributes and children packed to reduce it's size
	transitional = unpack({
		Z : 'H|K|N|O|P',
		Y : 'X|form|R|Q',
		ZG : 'E|span|width|align|char|charoff|valign',
		X : 'p|T|div|U|W|isindex|fieldset|table',
		ZF : 'E|align|char|charoff|valign',
		W : 'pre|hr|blockquote|address|center|noframes',
		ZE : 'abbr|axis|headers|scope|rowspan|colspan|align|char|charoff|valign|nowrap|bgcolor|width|height',
		ZD : '[E][S]',
		U : 'ul|ol|dl|menu|dir',
		ZC : 'p|Y|div|U|W|table|br|span|bdo|object|applet|img|map|K|N|Q',
		T : 'h1|h2|h3|h4|h5|h6',
		ZB : 'X|S|Q',
		S : 'R|P',
		ZA : 'a|G|J|M|O|P',
		R : 'a|H|K|N|O',
		Q : 'noscript|P',
		P : 'ins|del|script',
		O : 'input|select|textarea|label|button',
		N : 'M|L',
		M : 'em|strong|dfn|code|q|samp|kbd|var|cite|abbr|acronym',
		L : 'sub|sup',
		K : 'J|I',
		J : 'tt|i|b|u|s|strike',
		I : 'big|small|font|basefont',
		H : 'G|F',
		G : 'br|span|bdo',
		F : 'object|applet|img|map|iframe',
		E : 'A|B|C',
		D : 'accesskey|tabindex|onfocus|onblur',
		C : 'onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup',
		B : 'lang|xml:lang|dir',
		A : 'id|class|style|title'
	}, 'script[id|charset|type|language|src|defer|xml:space][]' + 
		'style[B|id|type|media|title|xml:space][]' + 
		'object[E|declare|classid|codebase|data|type|codetype|archive|standby|width|height|usemap|name|tabindex|align|border|hspace|vspace][#|param|Y]' + 
		'param[id|name|value|valuetype|type][]' + 
		'p[E|align][#|S]' + 
		'a[E|D|charset|type|name|href|hreflang|rel|rev|shape|coords|target][#|Z]' + 
		'br[A|clear][]' + 
		'span[E][#|S]' + 
		'bdo[A|C|B][#|S]' + 
		'applet[A|codebase|archive|code|object|alt|name|width|height|align|hspace|vspace][#|param|Y]' + 
		'h1[E|align][#|S]' + 
		'img[E|src|alt|name|longdesc|width|height|usemap|ismap|align|border|hspace|vspace][]' + 
		'map[B|C|A|name][X|form|Q|area]' + 
		'h2[E|align][#|S]' + 
		'iframe[A|longdesc|name|src|frameborder|marginwidth|marginheight|scrolling|align|width|height][#|Y]' + 
		'h3[E|align][#|S]' + 
		'tt[E][#|S]' + 
		'i[E][#|S]' + 
		'b[E][#|S]' + 
		'u[E][#|S]' + 
		's[E][#|S]' + 
		'strike[E][#|S]' + 
		'big[E][#|S]' + 
		'small[E][#|S]' + 
		'font[A|B|size|color|face][#|S]' + 
		'basefont[id|size|color|face][]' + 
		'em[E][#|S]' + 
		'strong[E][#|S]' + 
		'dfn[E][#|S]' + 
		'code[E][#|S]' + 
		'q[E|cite][#|S]' + 
		'samp[E][#|S]' + 
		'kbd[E][#|S]' + 
		'var[E][#|S]' + 
		'cite[E][#|S]' + 
		'abbr[E][#|S]' + 
		'acronym[E][#|S]' + 
		'sub[E][#|S]' + 
		'sup[E][#|S]' + 
		'input[E|D|type|name|value|checked|disabled|readonly|size|maxlength|src|alt|usemap|onselect|onchange|accept|align][]' + 
		'select[E|name|size|multiple|disabled|tabindex|onfocus|onblur|onchange][optgroup|option]' + 
		'optgroup[E|disabled|label][option]' + 
		'option[E|selected|disabled|label|value][]' + 
		'textarea[E|D|name|rows|cols|disabled|readonly|onselect|onchange][]' + 
		'label[E|for|accesskey|onfocus|onblur][#|S]' + 
		'button[E|D|name|value|type|disabled][#|p|T|div|U|W|table|G|object|applet|img|map|K|N|Q]' + 
		'h4[E|align][#|S]' + 
		'ins[E|cite|datetime][#|Y]' + 
		'h5[E|align][#|S]' + 
		'del[E|cite|datetime][#|Y]' + 
		'h6[E|align][#|S]' + 
		'div[E|align][#|Y]' + 
		'ul[E|type|compact][li]' + 
		'li[E|type|value][#|Y]' + 
		'ol[E|type|compact|start][li]' + 
		'dl[E|compact][dt|dd]' + 
		'dt[E][#|S]' + 
		'dd[E][#|Y]' + 
		'menu[E|compact][li]' + 
		'dir[E|compact][li]' + 
		'pre[E|width|xml:space][#|ZA]' + 
		'hr[E|align|noshade|size|width][]' + 
		'blockquote[E|cite][#|Y]' + 
		'address[E][#|S|p]' + 
		'center[E][#|Y]' + 
		'noframes[E][#|Y]' + 
		'isindex[A|B|prompt][]' + 
		'fieldset[E][#|legend|Y]' + 
		'legend[E|accesskey|align][#|S]' + 
		'table[E|summary|width|border|frame|rules|cellspacing|cellpadding|align|bgcolor][caption|col|colgroup|thead|tfoot|tbody|tr]' + 
		'caption[E|align][#|S]' + 
		'col[ZG][]' + 
		'colgroup[ZG][col]' + 
		'thead[ZF][tr]' + 
		'tr[ZF|bgcolor][th|td]' + 
		'th[E|ZE][#|Y]' + 
		'form[E|action|method|name|enctype|onsubmit|onreset|accept|accept-charset|target][#|X|R|Q]' + 
		'noscript[E][#|Y]' + 
		'td[E|ZE][#|Y]' + 
		'tfoot[ZF][tr]' + 
		'tbody[ZF][tr]' + 
		'area[E|D|shape|coords|href|nohref|alt|target][]' + 
		'base[id|href|target][]' + 
		'body[E|onload|onunload|background|bgcolor|text|link|vlink|alink][#|Y]'
	);

	boolAttrMap = makeMap('checked,compact,declare,defer,disabled,ismap,multiple,nohref,noresize,noshade,nowrap,readonly,selected,autoplay,loop,controls');
	shortEndedElementsMap = makeMap('area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed,source');
	nonEmptyElementsMap = tinymce.extend(makeMap('td,th,iframe,video,audio,object'), shortEndedElementsMap);
	defaultWhiteSpaceElementsMap = makeMap('pre,script,style,textarea');
	selfClosingElementsMap = makeMap('colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr');

	tinymce.html.Schema = function(settings) {
		var self = this, elements = {}, children = {}, patternElements = [], validStyles, whiteSpaceElementsMap;

		settings = settings || {};

		// Allow all elements and attributes if verify_html is set to false
		if (settings.verify_html === false)
			settings.valid_elements = '*[*]';

		// Build styles list
		if (settings.valid_styles) {
			validStyles = {};

			// Convert styles into a rule list
			each(settings.valid_styles, function(value, key) {
				validStyles[key] = tinymce.explode(value);
			});
		}

		whiteSpaceElementsMap = settings.whitespace_elements ? makeMap(settings.whitespace_elements) : defaultWhiteSpaceElementsMap;

		// Converts a wildcard expression string to a regexp for example *a will become /.*a/.
		function patternToRegExp(str) {
			return new RegExp('^' + str.replace(/([?+*])/g, '.$1') + '$');
		};

		// Parses the specified valid_elements string and adds to the current rules
		// This function is a bit hard to read since it's heavily optimized for speed
		function addValidElements(valid_elements) {
			var ei, el, ai, al, yl, matches, element, attr, attrData, elementName, attrName, attrType, attributes, attributesOrder,
				prefix, outputName, globalAttributes, globalAttributesOrder, transElement, key, childKey, value,
				elementRuleRegExp = /^([#+-])?([^\[\/]+)(?:\/([^\[]+))?(?:\[([^\]]+)\])?$/,
				attrRuleRegExp = /^([!\-])?(\w+::\w+|[^=:<]+)?(?:([=:<])(.*))?$/,
				hasPatternsRegExp = /[*?+]/;

			if (valid_elements) {
				// Split valid elements into an array with rules
				valid_elements = split(valid_elements);

				if (elements['@']) {
					globalAttributes = elements['@'].attributes;
					globalAttributesOrder = elements['@'].attributesOrder;
				}

				// Loop all rules
				for (ei = 0, el = valid_elements.length; ei < el; ei++) {
					// Parse element rule
					matches = elementRuleRegExp.exec(valid_elements[ei]);
					if (matches) {
						// Setup local names for matches
						prefix = matches[1];
						elementName = matches[2];
						outputName = matches[3];
						attrData = matches[4];

						// Create new attributes and attributesOrder
						attributes = {};
						attributesOrder = [];

						// Create the new element
						element = {
							attributes : attributes,
							attributesOrder : attributesOrder
						};

						// Padd empty elements prefix
						if (prefix === '#')
							element.paddEmpty = true;

						// Remove empty elements prefix
						if (prefix === '-')
							element.removeEmpty = true;

						// Copy attributes from global rule into current rule
						if (globalAttributes) {
							for (key in globalAttributes)
								attributes[key] = globalAttributes[key];

							attributesOrder.push.apply(attributesOrder, globalAttributesOrder);
						}

						// Attributes defined
						if (attrData) {
							attrData = split(attrData, '|');
							for (ai = 0, al = attrData.length; ai < al; ai++) {
								matches = attrRuleRegExp.exec(attrData[ai]);
								if (matches) {
									attr = {};
									attrType = matches[1];
									attrName = matches[2].replace(/::/g, ':');
									prefix = matches[3];
									value = matches[4];

									// Required
									if (attrType === '!') {
										element.attributesRequired = element.attributesRequired || [];
										element.attributesRequired.push(attrName);
										attr.required = true;
									}

									// Denied from global
									if (attrType === '-') {
										delete attributes[attrName];
										attributesOrder.splice(tinymce.inArray(attributesOrder, attrName), 1);
										continue;
									}

									// Default value
									if (prefix) {
										// Default value
										if (prefix === '=') {
											element.attributesDefault = element.attributesDefault || [];
											element.attributesDefault.push({name: attrName, value: value});
											attr.defaultValue = value;
										}

										// Forced value
										if (prefix === ':') {
											element.attributesForced = element.attributesForced || [];
											element.attributesForced.push({name: attrName, value: value});
											attr.forcedValue = value;
										}

										// Required values
										if (prefix === '<')
											attr.validValues = makeMap(value, '?');
									}

									// Check for attribute patterns
									if (hasPatternsRegExp.test(attrName)) {
										element.attributePatterns = element.attributePatterns || [];
										attr.pattern = patternToRegExp(attrName);
										element.attributePatterns.push(attr);
									} else {
										// Add attribute to order list if it doesn't already exist
										if (!attributes[attrName])
											attributesOrder.push(attrName);

										attributes[attrName] = attr;
									}
								}
							}
						}

						// Global rule, store away these for later usage
						if (!globalAttributes && elementName == '@') {
							globalAttributes = attributes;
							globalAttributesOrder = attributesOrder;
						}

						// Handle substitute elements such as b/strong
						if (outputName) {
							element.outputName = elementName;
							elements[outputName] = element;
						}

						// Add pattern or exact element
						if (hasPatternsRegExp.test(elementName)) {
							element.pattern = patternToRegExp(elementName);
							patternElements.push(element);
						} else
							elements[elementName] = element;
					}
				}
			}
		};

		function setValidElements(valid_elements) {
			elements = {};
			patternElements = [];

			addValidElements(valid_elements);

			each(transitional, function(element, name) {
				children[name] = element.children;
			});
		};

		// Adds custom non HTML elements to the schema
		function addCustomElements(custom_elements) {
			var customElementRegExp = /^(~)?(.+)$/;

			if (custom_elements) {
				each(split(custom_elements), function(rule) {
					var matches = customElementRegExp.exec(rule),
						inline = matches[1] === '~',
						cloneName = inline ? 'span' : 'div',
						name = matches[2];

					children[name] = children[cloneName];
					customElementsMap[name] = cloneName;

					// If it's not marked as inline then add it to valid block elements
					if (!inline)
						blockElementsMap[name] = {};

					// Add custom elements at span/div positions
					each(children, function(element, child) {
						if (element[cloneName])
							element[name] = element[cloneName];
					});
				});
			}
		};

		// Adds valid children to the schema object
		function addValidChildren(valid_children) {
			var childRuleRegExp = /^([+\-]?)(\w+)\[([^\]]+)\]$/;

			if (valid_children) {
				each(split(valid_children), function(rule) {
					var matches = childRuleRegExp.exec(rule), parent, prefix;

					if (matches) {
						prefix = matches[1];

						// Add/remove items from default
						if (prefix)
							parent = children[matches[2]];
						else
							parent = children[matches[2]] = {'#comment' : {}};

						parent = children[matches[2]];

						each(split(matches[3], '|'), function(child) {
							if (prefix === '-')
								delete parent[child];
							else
								parent[child] = {};
						});
					}
				});
			}
		};

		function getElementRule(name) {
			var element = elements[name], i;

			// Exact match found
			if (element)
				return element;

			// No exact match then try the patterns
			i = patternElements.length;
			while (i--) {
				element = patternElements[i];

				if (element.pattern.test(name))
					return element;
			}
		};

		if (!settings.valid_elements) {
			// No valid elements defined then clone the elements from the transitional spec
			each(transitional, function(element, name) {
				elements[name] = {
					attributes : element.attributes,
					attributesOrder : element.attributesOrder
				};

				children[name] = element.children;
			});

			// Switch these
			each(split('strong/b,em/i'), function(item) {
				item = split(item, '/');
				elements[item[1]].outputName = item[0];
			});

			// Add default alt attribute for images
			elements.img.attributesDefault = [{name: 'alt', value: ''}];

			// Remove these if they are empty by default
			each(split('ol,ul,sub,sup,blockquote,span,font,a,table,tbody,tr'), function(name) {
				elements[name].removeEmpty = true;
			});

			// Padd these by default
			each(split('p,h1,h2,h3,h4,h5,h6,th,td,pre,div,address,caption'), function(name) {
				elements[name].paddEmpty = true;
			});
		} else
			setValidElements(settings.valid_elements);

		addCustomElements(settings.custom_elements);
		addValidChildren(settings.valid_children);
		addValidElements(settings.extended_valid_elements);

		// Todo: Remove this when we fix list handling to be valid
		addValidChildren('+ol[ul|ol],+ul[ul|ol]');

		// If the user didn't allow span only allow internal spans
		if (!getElementRule('span'))
			addValidElements('span[!data-mce-type|*]');

		// Delete invalid elements
		if (settings.invalid_elements) {
			tinymce.each(tinymce.explode(settings.invalid_elements), function(item) {
				if (elements[item])
					delete elements[item];
			});
		}

		self.children = children;

		self.styles = validStyles;

		self.getBoolAttrs = function() {
			return boolAttrMap;
		};

		self.getBlockElements = function() {
			return blockElementsMap;
		};

		self.getShortEndedElements = function() {
			return shortEndedElementsMap;
		};

		self.getSelfClosingElements = function() {
			return selfClosingElementsMap;
		};

		self.getNonEmptyElements = function() {
			return nonEmptyElementsMap;
		};

		self.getWhiteSpaceElements = function() {
			return whiteSpaceElementsMap;
		};

		self.isValidChild = function(name, child) {
			var parent = children[name];

			return !!(parent && parent[child]);
		};

		self.getElementRule = getElementRule;

		self.getCustomElements = function() {
			return customElementsMap;
		};

		self.addValidElements = addValidElements;

		self.setValidElements = setValidElements;

		self.addCustomElements = addCustomElements;

		self.addValidChildren = addValidChildren;
	};

	// Expose boolMap and blockElementMap as static properties for usage in DOMUtils
	tinymce.html.Schema.boolAttrMap = boolAttrMap;
	tinymce.html.Schema.blockElementsMap = blockElementsMap;
})(tinymce);

(function(tinymce) {
	tinymce.html.SaxParser = function(settings, schema) {
		var self = this, noop = function() {};

		settings = settings || {};
		self.schema = schema = schema || new tinymce.html.Schema();

		if (settings.fix_self_closing !== false)
			settings.fix_self_closing = true;

		// Add handler functions from settings and setup default handlers
		tinymce.each('comment cdata text start end pi doctype'.split(' '), function(name) {
			if (name)
				self[name] = settings[name] || noop;
		});

		self.parse = function(html) {
			var self = this, matches, index = 0, value, endRegExp, stack = [], attrList, i, text, name, isInternalElement, removeInternalElements,
				shortEndedElements, fillAttrsMap, isShortEnded, validate, elementRule, isValidElement, attr, attribsValue, invalidPrefixRegExp,
				validAttributesMap, validAttributePatterns, attributesRequired, attributesDefault, attributesForced, selfClosing,
				tokenRegExp, attrRegExp, specialElements, attrValue, idCount = 0, decode = tinymce.html.Entities.decode, fixSelfClosing, isIE;

			function processEndTag(name) {
				var pos, i;

				// Find position of parent of the same type
				pos = stack.length;
				while (pos--) {
					if (stack[pos].name === name)
						break;						
				}

				// Found parent
				if (pos >= 0) {
					// Close all the open elements
					for (i = stack.length - 1; i >= pos; i--) {
						name = stack[i];

						if (name.valid)
							self.end(name.name);
					}

					// Remove the open elements from the stack
					stack.length = pos;
				}
			};

			// Precompile RegExps and map objects
			tokenRegExp = new RegExp('<(?:' +
				'(?:!--([\\w\\W]*?)-->)|' + // Comment
				'(?:!\\[CDATA\\[([\\w\\W]*?)\\]\\]>)|' + // CDATA
				'(?:!DOCTYPE([\\w\\W]*?)>)|' + // DOCTYPE
				'(?:\\?([^\\s\\/<>]+) ?([\\w\\W]*?)[?/]>)|' + // PI
				'(?:\\/([^>]+)>)|' + // End element
				'(?:([^\\s\\/<>]+)((?:\\s+[^"\'>]+(?:(?:"[^"]*")|(?:\'[^\']*\')|[^>]*))*|\\/)>)' + // Start element
			')', 'g');

			attrRegExp = /([\w:\-]+)(?:\s*=\s*(?:(?:\"((?:\\.|[^\"])*)\")|(?:\'((?:\\.|[^\'])*)\')|([^>\s]+)))?/g;
			specialElements = {
				'script' : /<\/script[^>]*>/gi,
				'style' : /<\/style[^>]*>/gi,
				'noscript' : /<\/noscript[^>]*>/gi
			};

			// Setup lookup tables for empty elements and boolean attributes
			shortEndedElements = schema.getShortEndedElements();
			selfClosing = schema.getSelfClosingElements();
			fillAttrsMap = schema.getBoolAttrs();
			validate = settings.validate;
			removeInternalElements = settings.remove_internals;
			fixSelfClosing = settings.fix_self_closing;
			isIE = tinymce.isIE;
			invalidPrefixRegExp = /^:/;

			while (matches = tokenRegExp.exec(html)) {
				// Text
				if (index < matches.index)
					self.text(decode(html.substr(index, matches.index - index)));

				if (value = matches[6]) { // End element
					value = value.toLowerCase();

					// IE will add a ":" in front of elements it doesn't understand like custom elements or HTML5 elements
					if (isIE && invalidPrefixRegExp.test(value))
						value = value.substr(1);

					processEndTag(value);
				} else if (value = matches[7]) { // Start element
					value = value.toLowerCase();

					// IE will add a ":" in front of elements it doesn't understand like custom elements or HTML5 elements
					if (isIE && invalidPrefixRegExp.test(value))
						value = value.substr(1);

					isShortEnded = value in shortEndedElements;

					// Is self closing tag for example an <li> after an open <li>
					if (fixSelfClosing && selfClosing[value] && stack.length > 0 && stack[stack.length - 1].name === value)
						processEndTag(value);

					// Validate element
					if (!validate || (elementRule = schema.getElementRule(value))) {
						isValidElement = true;

						// Grab attributes map and patters when validation is enabled
						if (validate) {
							validAttributesMap = elementRule.attributes;
							validAttributePatterns = elementRule.attributePatterns;
						}

						// Parse attributes
						if (attribsValue = matches[8]) {
							isInternalElement = attribsValue.indexOf('data-mce-type') !== -1; // Check if the element is an internal element

							// If the element has internal attributes then remove it if we are told to do so
							if (isInternalElement && removeInternalElements)
								isValidElement = false;

							attrList = [];
							attrList.map = {};

							attribsValue.replace(attrRegExp, function(match, name, value, val2, val3) {
								var attrRule, i;

								name = name.toLowerCase();
								value = name in fillAttrsMap ? name : decode(value || val2 || val3 || ''); // Handle boolean attribute than value attribute

								// Validate name and value
								if (validate && !isInternalElement && name.indexOf('data-') !== 0) {
									attrRule = validAttributesMap[name];

									// Find rule by pattern matching
									if (!attrRule && validAttributePatterns) {
										i = validAttributePatterns.length;
										while (i--) {
											attrRule = validAttributePatterns[i];
											if (attrRule.pattern.test(name))
												break;
										}

										// No rule matched
										if (i === -1)
											attrRule = null;
									}

									// No attribute rule found
									if (!attrRule)
										return;

									// Validate value
									if (attrRule.validValues && !(value in attrRule.validValues))
										return;
								}

								// Add attribute to list and map
								attrList.map[name] = value;
								attrList.push({
									name: name,
									value: value
								});
							});
						} else {
							attrList = [];
							attrList.map = {};
						}

						// Process attributes if validation is enabled
						if (validate && !isInternalElement) {
							attributesRequired = elementRule.attributesRequired;
							attributesDefault = elementRule.attributesDefault;
							attributesForced = elementRule.attributesForced;

							// Handle forced attributes
							if (attributesForced) {
								i = attributesForced.length;
								while (i--) {
									attr = attributesForced[i];
									name = attr.name;
									attrValue = attr.value;

									if (attrValue === '{$uid}')
										attrValue = 'mce_' + idCount++;

									attrList.map[name] = attrValue;
									attrList.push({name: name, value: attrValue});
								}
							}

							// Handle default attributes
							if (attributesDefault) {
								i = attributesDefault.length;
								while (i--) {
									attr = attributesDefault[i];
									name = attr.name;

									if (!(name in attrList.map)) {
										attrValue = attr.value;

										if (attrValue === '{$uid}')
											attrValue = 'mce_' + idCount++;

										attrList.map[name] = attrValue;
										attrList.push({name: name, value: attrValue});
									}
								}
							}

							// Handle required attributes
							if (attributesRequired) {
								i = attributesRequired.length;
								while (i--) {
									if (attributesRequired[i] in attrList.map)
										break;
								}

								// None of the required attributes where found
								if (i === -1)
									isValidElement = false;
							}

							// Invalidate element if it's marked as bogus
							if (attrList.map['data-mce-bogus'])
								isValidElement = false;
						}

						if (isValidElement)
							self.start(value, attrList, isShortEnded);
					} else
						isValidElement = false;

					// Treat script, noscript and style a bit different since they may include code that looks like elements
					if (endRegExp = specialElements[value]) {
						endRegExp.lastIndex = index = matches.index + matches[0].length;

						if (matches = endRegExp.exec(html)) {
							if (isValidElement)
								text = html.substr(index, matches.index - index);

							index = matches.index + matches[0].length;
						} else {
							text = html.substr(index);
							index = html.length;
						}

						if (isValidElement && text.length > 0)
							self.text(text, true);

						if (isValidElement)
							self.end(value);

						tokenRegExp.lastIndex = index;
						continue;
					}

					// Push value on to stack
					if (!isShortEnded) {
						if (!attribsValue || attribsValue.indexOf('/') != attribsValue.length - 1)
							stack.push({name: value, valid: isValidElement});
						else if (isValidElement)
							self.end(value);
					}
				} else if (value = matches[1]) { // Comment
					self.comment(value);
				} else if (value = matches[2]) { // CDATA
					self.cdata(value);
				} else if (value = matches[3]) { // DOCTYPE
					self.doctype(value);
				} else if (value = matches[4]) { // PI
					self.pi(value, matches[5]);
				}

				index = matches.index + matches[0].length;
			}

			// Text
			if (index < html.length)
				self.text(decode(html.substr(index)));

			// Close any open elements
			for (i = stack.length - 1; i >= 0; i--) {
				value = stack[i];

				if (value.valid)
					self.end(value.name);
			}
		};
	}
})(tinymce);

(function(tinymce) {
	var whiteSpaceRegExp = /^[ \t\r\n]*$/, typeLookup = {
		'#text' : 3,
		'#comment' : 8,
		'#cdata' : 4,
		'#pi' : 7,
		'#doctype' : 10,
		'#document-fragment' : 11
	};

	// Walks the tree left/right
	function walk(node, root_node, prev) {
		var sibling, parent, startName = prev ? 'lastChild' : 'firstChild', siblingName = prev ? 'prev' : 'next';

		// Walk into nodes if it has a start
		if (node[startName])
			return node[startName];

		// Return the sibling if it has one
		if (node !== root_node) {
			sibling = node[siblingName];

			if (sibling)
				return sibling;

			// Walk up the parents to look for siblings
			for (parent = node.parent; parent && parent !== root_node; parent = parent.parent) {
				sibling = parent[siblingName];

				if (sibling)
					return sibling;
			}
		}
	};

	function Node(name, type) {
		this.name = name;
		this.type = type;

		if (type === 1) {
			this.attributes = [];
			this.attributes.map = {};
		}
	}

	tinymce.extend(Node.prototype, {
		replace : function(node) {
			var self = this;

			if (node.parent)
				node.remove();

			self.insert(node, self);
			self.remove();

			return self;
		},

		attr : function(name, value) {
			var self = this, attrs, i, undef;

			if (typeof name !== "string") {
				for (i in name)
					self.attr(i, name[i]);

				return self;
			}

			if (attrs = self.attributes) {
				if (value !== undef) {
					// Remove attribute
					if (value === null) {
						if (name in attrs.map) {
							delete attrs.map[name];

							i = attrs.length;
							while (i--) {
								if (attrs[i].name === name) {
									attrs = attrs.splice(i, 1);
									return self;
								}
							}
						}

						return self;
					}

					// Set attribute
					if (name in attrs.map) {
						// Set attribute
						i = attrs.length;
						while (i--) {
							if (attrs[i].name === name) {
								attrs[i].value = value;
								break;
							}
						}
					} else
						attrs.push({name: name, value: value});

					attrs.map[name] = value;

					return self;
				} else {
					return attrs.map[name];
				}
			}
		},

		clone : function() {
			var self = this, clone = new Node(self.name, self.type), i, l, selfAttrs, selfAttr, cloneAttrs;

			// Clone element attributes
			if (selfAttrs = self.attributes) {
				cloneAttrs = [];
				cloneAttrs.map = {};

				for (i = 0, l = selfAttrs.length; i < l; i++) {
					selfAttr = selfAttrs[i];

					// Clone everything except id
					if (selfAttr.name !== 'id') {
						cloneAttrs[cloneAttrs.length] = {name: selfAttr.name, value: selfAttr.value};
						cloneAttrs.map[selfAttr.name] = selfAttr.value;
					}
				}

				clone.attributes = cloneAttrs;
			}

			clone.value = self.value;
			clone.shortEnded = self.shortEnded;

			return clone;
		},

		wrap : function(wrapper) {
			var self = this;

			self.parent.insert(wrapper, self);
			wrapper.append(self);

			return self;
		},

		unwrap : function() {
			var self = this, node, next;

			for (node = self.firstChild; node; ) {
				next = node.next;
				self.insert(node, self, true);
				node = next;
			}

			self.remove();
		},

		remove : function() {
			var self = this, parent = self.parent, next = self.next, prev = self.prev;

			if (parent) {
				if (parent.firstChild === self) {
					parent.firstChild = next;

					if (next)
						next.prev = null;
				} else {
					prev.next = next;
				}

				if (parent.lastChild === self) {
					parent.lastChild = prev;

					if (prev)
						prev.next = null;
				} else {
					next.prev = prev;
				}

				self.parent = self.next = self.prev = null;
			}

			return self;
		},

		append : function(node) {
			var self = this, last;

			if (node.parent)
				node.remove();

			last = self.lastChild;
			if (last) {
				last.next = node;
				node.prev = last;
				self.lastChild = node;
			} else
				self.lastChild = self.firstChild = node;

			node.parent = self;

			return node;
		},

		insert : function(node, ref_node, before) {
			var parent;

			if (node.parent)
				node.remove();

			parent = ref_node.parent || this;

			if (before) {
				if (ref_node === parent.firstChild)
					parent.firstChild = node;
				else
					ref_node.prev.next = node;

				node.prev = ref_node.prev;
				node.next = ref_node;
				ref_node.prev = node;
			} else {
				if (ref_node === parent.lastChild)
					parent.lastChild = node;
				else
					ref_node.next.prev = node;

				node.next = ref_node.next;
				node.prev = ref_node;
				ref_node.next = node;
			}

			node.parent = parent;

			return node;
		},

		getAll : function(name) {
			var self = this, node, collection = [];

			for (node = self.firstChild; node; node = walk(node, self)) {
				if (node.name === name)
					collection.push(node);
			}

			return collection;
		},

		empty : function() {
			var self = this, nodes, i, node;

			// Remove all children
			if (self.firstChild) {
				nodes = [];

				// Collect the children
				for (node = self.firstChild; node; node = walk(node, self))
					nodes.push(node);

				// Remove the children
				i = nodes.length;
				while (i--) {
					node = nodes[i];
					node.parent = node.firstChild = node.lastChild = node.next = node.prev = null;
				}
			}

			self.firstChild = self.lastChild = null;

			return self;
		},

		isEmpty : function(elements) {
			var self = this, node = self.firstChild, i, name;

			if (node) {
				do {
					if (node.type === 1) {
						// Ignore bogus elements
						if (node.attributes.map['data-mce-bogus'])
							continue;

						// Keep empty elements like <img />
						if (elements[node.name])
							return false;

						// Keep elements with data attributes or name attribute like <a name="1"></a>
						i = node.attributes.length;
						while (i--) {
							name = node.attributes[i].name;
							if (name === "name" || name.indexOf('data-') === 0)
								return false;
						}
					}

					// Keep non whitespace text nodes
					if ((node.type === 3 && !whiteSpaceRegExp.test(node.value)))
						return false;
				} while (node = walk(node, self));
			}

			return true;
		},

		walk : function(prev) {
			return walk(this, null, prev);
		}
	});

	tinymce.extend(Node, {
		create : function(name, attrs) {
			var node, attrName;

			// Create node
			node = new Node(name, typeLookup[name] || 1);

			// Add attributes if needed
			if (attrs) {
				for (attrName in attrs)
					node.attr(attrName, attrs[attrName]);
			}

			return node;
		}
	});

	tinymce.html.Node = Node;
})(tinymce);

(function(tinymce) {
	var Node = tinymce.html.Node;

	tinymce.html.DomParser = function(settings, schema) {
		var self = this, nodeFilters = {}, attributeFilters = [], matchedNodes = {}, matchedAttributes = {};

		settings = settings || {};
		settings.validate = "validate" in settings ? settings.validate : true;
		settings.root_name = settings.root_name || 'body';
		self.schema = schema = schema || new tinymce.html.Schema();

		function fixInvalidChildren(nodes) {
			var ni, node, parent, parents, newParent, currentNode, tempNode, childNode, i,
				childClone, nonEmptyElements, nonSplitableElements, sibling, nextNode;

			nonSplitableElements = tinymce.makeMap('tr,td,th,tbody,thead,tfoot,table');
			nonEmptyElements = schema.getNonEmptyElements();

			for (ni = 0; ni < nodes.length; ni++) {
				node = nodes[ni];

				// Already removed
				if (!node.parent)
					continue;

				// Get list of all parent nodes until we find a valid parent to stick the child into
				parents = [node];
				for (parent = node.parent; parent && !schema.isValidChild(parent.name, node.name) && !nonSplitableElements[parent.name]; parent = parent.parent)
					parents.push(parent);

				// Found a suitable parent
				if (parent && parents.length > 1) {
					// Reverse the array since it makes looping easier
					parents.reverse();

					// Clone the related parent and insert that after the moved node
					newParent = currentNode = self.filterNode(parents[0].clone());

					// Start cloning and moving children on the left side of the target node
					for (i = 0; i < parents.length - 1; i++) {
						if (schema.isValidChild(currentNode.name, parents[i].name)) {
							tempNode = self.filterNode(parents[i].clone());
							currentNode.append(tempNode);
						} else
							tempNode = currentNode;

						for (childNode = parents[i].firstChild; childNode && childNode != parents[i + 1]; ) {
							nextNode = childNode.next;
							tempNode.append(childNode);
							childNode = nextNode;
						}

						currentNode = tempNode;
					}

					if (!newParent.isEmpty(nonEmptyElements)) {
						parent.insert(newParent, parents[0], true);
						parent.insert(node, newParent);
					} else {
						parent.insert(node, parents[0], true);
					}

					// Check if the element is empty by looking through it's contents and special treatment for <p><br /></p>
					parent = parents[0];
					if (parent.isEmpty(nonEmptyElements) || parent.firstChild === parent.lastChild && parent.firstChild.name === 'br') {
						parent.empty().remove();
					}
				} else if (node.parent) {
					// If it's an LI try to find a UL/OL for it or wrap it
					if (node.name === 'li') {
						sibling = node.prev;
						if (sibling && (sibling.name === 'ul' || sibling.name === 'ul')) {
							sibling.append(node);
							continue;
						}

						sibling = node.next;
						if (sibling && (sibling.name === 'ul' || sibling.name === 'ul')) {
							sibling.insert(node, sibling.firstChild, true);
							continue;
						}

						node.wrap(self.filterNode(new Node('ul', 1)));
						continue;
					}

					// Try wrapping the element in a DIV
					if (schema.isValidChild(node.parent.name, 'div') && schema.isValidChild('div', node.name)) {
						node.wrap(self.filterNode(new Node('div', 1)));
					} else {
						// We failed wrapping it, then remove or unwrap it
						if (node.name === 'style' || node.name === 'script')
							node.empty().remove();
						else
							node.unwrap();
					}
				}
			}
		};

		self.filterNode = function(node) {
			var i, name, list;

			// Run element filters
			if (name in nodeFilters) {
				list = matchedNodes[name];

				if (list)
					list.push(node);
				else
					matchedNodes[name] = [node];
			}

			// Run attribute filters
			i = attributeFilters.length;
			while (i--) {
				name = attributeFilters[i].name;

				if (name in node.attributes.map) {
					list = matchedAttributes[name];

					if (list)
						list.push(node);
					else
						matchedAttributes[name] = [node];
				}
			}

			return node;
		};

		self.addNodeFilter = function(name, callback) {
			tinymce.each(tinymce.explode(name), function(name) {
				var list = nodeFilters[name];

				if (!list)
					nodeFilters[name] = list = [];

				list.push(callback);
			});
		};

		self.addAttributeFilter = function(name, callback) {
			tinymce.each(tinymce.explode(name), function(name) {
				var i;

				for (i = 0; i < attributeFilters.length; i++) {
					if (attributeFilters[i].name === name) {
						attributeFilters[i].callbacks.push(callback);
						return;
					}
				}

				attributeFilters.push({name: name, callbacks: [callback]});
			});
		};

		self.parse = function(html, args) {
			var parser, rootNode, node, nodes, i, l, fi, fl, list, name, validate,
				blockElements, startWhiteSpaceRegExp, invalidChildren = [],
				endWhiteSpaceRegExp, allWhiteSpaceRegExp, whiteSpaceElements, children, nonEmptyElements, rootBlockName;

			args = args || {};
			matchedNodes = {};
			matchedAttributes = {};
			blockElements = tinymce.extend(tinymce.makeMap('script,style,head,html,body,title,meta,param'), schema.getBlockElements());
			nonEmptyElements = schema.getNonEmptyElements();
			children = schema.children;
			validate = settings.validate;
			rootBlockName = "forced_root_block" in args ? args.forced_root_block : settings.forced_root_block;

			whiteSpaceElements = schema.getWhiteSpaceElements();
			startWhiteSpaceRegExp = /^[ \t\r\n]+/;
			endWhiteSpaceRegExp = /[ \t\r\n]+$/;
			allWhiteSpaceRegExp = /[ \t\r\n]+/g;

			function addRootBlocks() {
				var node = rootNode.firstChild, next, rootBlockNode;

				while (node) {
					next = node.next;

					if (node.type == 3 || (node.type == 1 && node.name !== 'p' && !blockElements[node.name] && !node.attr('data-mce-type'))) {
						if (!rootBlockNode) {
							// Create a new root block element
							rootBlockNode = createNode(rootBlockName, 1);
							rootNode.insert(rootBlockNode, node);
							rootBlockNode.append(node);
						} else
							rootBlockNode.append(node);
					} else {
						rootBlockNode = null;
					}

					node = next;
				};
			};

			function createNode(name, type) {
				var node = new Node(name, type), list;

				if (name in nodeFilters) {
					list = matchedNodes[name];

					if (list)
						list.push(node);
					else
						matchedNodes[name] = [node];
				}

				return node;
			};

			function removeWhitespaceBefore(node) {
				var textNode, textVal, sibling;

				for (textNode = node.prev; textNode && textNode.type === 3; ) {
					textVal = textNode.value.replace(endWhiteSpaceRegExp, '');

					if (textVal.length > 0) {
						textNode.value = textVal;
						textNode = textNode.prev;
					} else {
						sibling = textNode.prev;
						textNode.remove();
						textNode = sibling;
					}
				}
			};

			parser = new tinymce.html.SaxParser({
				validate : validate,
				fix_self_closing : !validate, // Let the DOM parser handle <li> in <li> or <p> in <p> for better results

				cdata: function(text) {
					node.append(createNode('#cdata', 4)).value = text;
				},

				text: function(text, raw) {
					var textNode;

					// Trim all redundant whitespace on non white space elements
					if (!whiteSpaceElements[node.name]) {
						text = text.replace(allWhiteSpaceRegExp, ' ');

						if (node.lastChild && blockElements[node.lastChild.name])
							text = text.replace(startWhiteSpaceRegExp, '');
					}

					// Do we need to create the node
					if (text.length !== 0) {
						textNode = createNode('#text', 3);
						textNode.raw = !!raw;
						node.append(textNode).value = text;
					}
				},

				comment: function(text) {
					node.append(createNode('#comment', 8)).value = text;
				},

				pi: function(name, text) {
					node.append(createNode(name, 7)).value = text;
					removeWhitespaceBefore(node);
				},

				doctype: function(text) {
					var newNode;
		
					newNode = node.append(createNode('#doctype', 10));
					newNode.value = text;
					removeWhitespaceBefore(node);
				},

				start: function(name, attrs, empty) {
					var newNode, attrFiltersLen, elementRule, textNode, attrName, text, sibling, parent;

					elementRule = validate ? schema.getElementRule(name) : {};
					if (elementRule) {
						newNode = createNode(elementRule.outputName || name, 1);
						newNode.attributes = attrs;
						newNode.shortEnded = empty;

						node.append(newNode);

						// Check if node is valid child of the parent node is the child is
						// unknown we don't collect it since it's probably a custom element
						parent = children[node.name];
						if (parent && children[newNode.name] && !parent[newNode.name])
							invalidChildren.push(newNode);

						attrFiltersLen = attributeFilters.length;
						while (attrFiltersLen--) {
							attrName = attributeFilters[attrFiltersLen].name;

							if (attrName in attrs.map) {
								list = matchedAttributes[attrName];

								if (list)
									list.push(newNode);
								else
									matchedAttributes[attrName] = [newNode];
							}
						}

						// Trim whitespace before block
						if (blockElements[name])
							removeWhitespaceBefore(newNode);

						// Change current node if the element wasn't empty i.e not <br /> or <img />
						if (!empty)
							node = newNode;
					}
				},

				end: function(name) {
					var textNode, elementRule, text, sibling, tempNode;

					elementRule = validate ? schema.getElementRule(name) : {};
					if (elementRule) {
						if (blockElements[name]) {
							if (!whiteSpaceElements[node.name]) {
								// Trim whitespace at beginning of block
								for (textNode = node.firstChild; textNode && textNode.type === 3; ) {
									text = textNode.value.replace(startWhiteSpaceRegExp, '');

									if (text.length > 0) {
										textNode.value = text;
										textNode = textNode.next;
									} else {
										sibling = textNode.next;
										textNode.remove();
										textNode = sibling;
									}
								}

								// Trim whitespace at end of block
								for (textNode = node.lastChild; textNode && textNode.type === 3; ) {
									text = textNode.value.replace(endWhiteSpaceRegExp, '');

									if (text.length > 0) {
										textNode.value = text;
										textNode = textNode.prev;
									} else {
										sibling = textNode.prev;
										textNode.remove();
										textNode = sibling;
									}
								}
							}

							// Trim start white space
							textNode = node.prev;
							if (textNode && textNode.type === 3) {
								text = textNode.value.replace(startWhiteSpaceRegExp, '');

								if (text.length > 0)
									textNode.value = text;
								else
									textNode.remove();
							}
						}

						// Handle empty nodes
						if (elementRule.removeEmpty || elementRule.paddEmpty) {
							if (node.isEmpty(nonEmptyElements)) {
								if (elementRule.paddEmpty)
									node.empty().append(new Node('#text', '3')).value = '\u00a0';
								else {
									// Leave nodes that have a name like <a name="name">
									if (!node.attributes.map.name) {
										tempNode = node.parent;
										node.empty().remove();
										node = tempNode;
										return;
									}
								}
							}
						}

						node = node.parent;
					}
				}
			}, schema);

			rootNode = node = new Node(args.context || settings.root_name, 11);

			parser.parse(html);

			// Fix invalid children or report invalid children in a contextual parsing
			if (validate && invalidChildren.length) {
				if (!args.context)
					fixInvalidChildren(invalidChildren);
				else
					args.invalid = true;
			}

			// Wrap nodes in the root into block elements if the root is body
			if (rootBlockName && rootNode.name == 'body')
				addRootBlocks();

			// Run filters only when the contents is valid
			if (!args.invalid) {
				// Run node filters
				for (name in matchedNodes) {
					list = nodeFilters[name];
					nodes = matchedNodes[name];

					// Remove already removed children
					fi = nodes.length;
					while (fi--) {
						if (!nodes[fi].parent)
							nodes.splice(fi, 1);
					}

					for (i = 0, l = list.length; i < l; i++)
						list[i](nodes, name, args);
				}

				// Run attribute filters
				for (i = 0, l = attributeFilters.length; i < l; i++) {
					list = attributeFilters[i];

					if (list.name in matchedAttributes) {
						nodes = matchedAttributes[list.name];

						// Remove already removed children
						fi = nodes.length;
						while (fi--) {
							if (!nodes[fi].parent)
								nodes.splice(fi, 1);
						}

						for (fi = 0, fl = list.callbacks.length; fi < fl; fi++)
							list.callbacks[fi](nodes, list.name, args);
					}
				}
			}

			return rootNode;
		};

		// Remove <br> at end of block elements Gecko and WebKit injects BR elements to
		// make it possible to place the caret inside empty blocks. This logic tries to remove
		// these elements and keep br elements that where intended to be there intact
		if (settings.remove_trailing_brs) {
			self.addNodeFilter('br', function(nodes, name) {
				var i, l = nodes.length, node, blockElements = schema.getBlockElements(),
					nonEmptyElements = schema.getNonEmptyElements(), parent, prev, prevName;

				// Remove brs from body element as well
				blockElements.body = 1;

				// Must loop forwards since it will otherwise remove all brs in <p>a<br><br><br></p>
				for (i = 0; i < l; i++) {
					node = nodes[i];
					parent = node.parent;

					if (blockElements[node.parent.name] && node === parent.lastChild) {
						// Loop all nodes to the right of the current node and check for other BR elements
						// excluding bookmarks since they are invisible
						prev = node.prev;
						while (prev) {
							prevName = prev.name;

							// Ignore bookmarks
							if (prevName !== "span" || prev.attr('data-mce-type') !== 'bookmark') {
								// Found a non BR element
								if (prevName !== "br")
									break;
	
								// Found another br it's a <br><br> structure then don't remove anything
								if (prevName === 'br') {
									node = null;
									break;
								}
							}

							prev = prev.prev;
						}

						if (node) {
							node.remove();

							// Is the parent to be considered empty after we removed the BR
							if (parent.isEmpty(nonEmptyElements)) {
								elementRule = schema.getElementRule(parent.name);

								// Remove or padd the element depending on schema rule
								if (elementRule) {
								  if (elementRule.removeEmpty)
									  parent.remove();
								  else if (elementRule.paddEmpty)
									  parent.empty().append(new tinymce.html.Node('#text', 3)).value = '\u00a0';
							  }
              }
						}
					}
				}
			});
		}
	}
})(tinymce);

tinymce.html.Writer = function(settings) {
	var html = [], indent, indentBefore, indentAfter, encode, htmlOutput;

	settings = settings || {};
	indent = settings.indent;
	indentBefore = tinymce.makeMap(settings.indent_before || '');
	indentAfter = tinymce.makeMap(settings.indent_after || '');
	encode = tinymce.html.Entities.getEncodeFunc(settings.entity_encoding || 'raw', settings.entities);
	htmlOutput = settings.element_format == "html";

	return {
		start: function(name, attrs, empty) {
			var i, l, attr, value;

			if (indent && indentBefore[name] && html.length > 0) {
				value = html[html.length - 1];

				if (value.length > 0 && value !== '\n')
					html.push('\n');
			}

			html.push('<', name);

			if (attrs) {
				for (i = 0, l = attrs.length; i < l; i++) {
					attr = attrs[i];
					html.push(' ', attr.name, '="', encode(attr.value, true), '"');
				}
			}

			if (!empty || htmlOutput)
				html[html.length] = '>';
			else
				html[html.length] = ' />';

			if (empty && indent && indentAfter[name] && html.length > 0) {
				value = html[html.length - 1];

				if (value.length > 0 && value !== '\n')
					html.push('\n');
			}
		},

		end: function(name) {
			var value;

			/*if (indent && indentBefore[name] && html.length > 0) {
				value = html[html.length - 1];

				if (value.length > 0 && value !== '\n')
					html.push('\n');
			}*/

			html.push('</', name, '>');

			if (indent && indentAfter[name] && html.length > 0) {
				value = html[html.length - 1];

				if (value.length > 0 && value !== '\n')
					html.push('\n');
			}
		},

		text: function(text, raw) {
			if (text.length > 0)
				html[html.length] = raw ? text : encode(text);
		},

		cdata: function(text) {
			html.push('<![CDATA[', text, ']]>');
		},

		comment: function(text) {
			html.push('<!--', text, '-->');
		},

		pi: function(name, text) {
			if (text)
				html.push('<?', name, ' ', text, '?>');
			else
				html.push('<?', name, '?>');

			if (indent)
				html.push('\n');
		},

		doctype: function(text) {
			html.push('<!DOCTYPE', text, '>', indent ? '\n' : '');
		},

		reset: function() {
			html.length = 0;
		},

		getContent: function() {
			return html.join('').replace(/\n$/, '');
		}
	};
};

(function(tinymce) {
	tinymce.html.Serializer = function(settings, schema) {
		var self = this, writer = new tinymce.html.Writer(settings);

		settings = settings || {};
		settings.validate = "validate" in settings ? settings.validate : true;

		self.schema = schema = schema || new tinymce.html.Schema();
		self.writer = writer;

		self.serialize = function(node) {
			var handlers, validate;

			validate = settings.validate;

			handlers = {
				// #text
				3: function(node, raw) {
					writer.text(node.value, node.raw);
				},

				// #comment
				8: function(node) {
					writer.comment(node.value);
				},

				// Processing instruction
				7: function(node) {
					writer.pi(node.name, node.value);
				},

				// Doctype
				10: function(node) {
					writer.doctype(node.value);
				},

				// CDATA
				4: function(node) {
					writer.cdata(node.value);
				},

 				// Document fragment
				11: function(node) {
					if ((node = node.firstChild)) {
						do {
							walk(node);
						} while (node = node.next);
					}
				}
			};

			writer.reset();

			function walk(node) {
				var handler = handlers[node.type], name, isEmpty, attrs, attrName, attrValue, sortedAttrs, i, l, elementRule;

				if (!handler) {
					name = node.name;
					isEmpty = node.shortEnded;
					attrs = node.attributes;

					// Sort attributes
					if (validate && attrs && attrs.length > 1) {
						sortedAttrs = [];
						sortedAttrs.map = {};

						elementRule = schema.getElementRule(node.name);
						for (i = 0, l = elementRule.attributesOrder.length; i < l; i++) {
							attrName = elementRule.attributesOrder[i];

							if (attrName in attrs.map) {
								attrValue = attrs.map[attrName];
								sortedAttrs.map[attrName] = attrValue;
								sortedAttrs.push({name: attrName, value: attrValue});
							}
						}

						for (i = 0, l = attrs.length; i < l; i++) {
							attrName = attrs[i].name;

							if (!(attrName in sortedAttrs.map)) {
								attrValue = attrs.map[attrName];
								sortedAttrs.map[attrName] = attrValue;
								sortedAttrs.push({name: attrName, value: attrValue});
							}
						}

						attrs = sortedAttrs;
					}

					writer.start(node.name, attrs, isEmpty);

					if (!isEmpty) {
						if ((node = node.firstChild)) {
							do {
								walk(node);
							} while (node = node.next);
						}

						writer.end(name);
					}
				} else
					handler(node);
			}

			// Serialize element and treat all non elements as fragments
			if (node.type == 1 && !settings.inner)
				walk(node);
			else
				handlers[11](node);

			return writer.getContent();
		};
	}
})(tinymce);

(function(tinymce) {
	// Shorten names
	var each = tinymce.each,
		is = tinymce.is,
		isWebKit = tinymce.isWebKit,
		isIE = tinymce.isIE,
		Entities = tinymce.html.Entities,
		simpleSelectorRe = /^([a-z0-9],?)+$/i,
		blockElementsMap = tinymce.html.Schema.blockElementsMap,
		whiteSpaceRegExp = /^[ \t\r\n]*$/;

	tinymce.create('tinymce.dom.DOMUtils', {
		doc : null,
		root : null,
		files : null,
		pixelStyles : /^(top|left|bottom|right|width|height|borderWidth)$/,
		props : {
			"for" : "htmlFor",
			"class" : "className",
			className : "className",
			checked : "checked",
			disabled : "disabled",
			maxlength : "maxLength",
			readonly : "readOnly",
			selected : "selected",
			value : "value",
			id : "id",
			name : "name",
			type : "type"
		},

		DOMUtils : function(d, s) {
			var t = this, globalStyle, name;

			t.doc = d;
			t.win = window;
			t.files = {};
			t.cssFlicker = false;
			t.counter = 0;
			t.stdMode = !tinymce.isIE || d.documentMode >= 8;
			t.boxModel = !tinymce.isIE || d.compatMode == "CSS1Compat" || t.stdMode;
			t.hasOuterHTML = "outerHTML" in d.createElement("a");

			t.settings = s = tinymce.extend({
				keep_values : false,
				hex_colors : 1
			}, s);
			
			t.schema = s.schema;
			t.styles = new tinymce.html.Styles({
				url_converter : s.url_converter,
				url_converter_scope : s.url_converter_scope
			}, s.schema);

			// Fix IE6SP2 flicker and check it failed for pre SP2
			if (tinymce.isIE6) {
				try {
					d.execCommand('BackgroundImageCache', false, true);
				} catch (e) {
					t.cssFlicker = true;
				}
			}

			if (isIE && s.schema) {
				// Add missing HTML 4/5 elements to IE
				('abbr article aside audio canvas ' +
				'details figcaption figure footer ' +
				'header hgroup mark menu meter nav ' +
				'output progress section summary ' +
				'time video').replace(/\w+/g, function(name) {
					d.createElement(name);
				});

				// Create all custom elements
				for (name in s.schema.getCustomElements()) {
					d.createElement(name);
				}
			}

			tinymce.addUnload(t.destroy, t);
		},

		getRoot : function() {
			var t = this, s = t.settings;

			return (s && t.get(s.root_element)) || t.doc.body;
		},

		getViewPort : function(w) {
			var d, b;

			w = !w ? this.win : w;
			d = w.document;
			b = this.boxModel ? d.documentElement : d.body;

			// Returns viewport size excluding scrollbars
			return {
				x : w.pageXOffset || b.scrollLeft,
				y : w.pageYOffset || b.scrollTop,
				w : w.innerWidth || b.clientWidth,
				h : w.innerHeight || b.clientHeight
			};
		},

		getRect : function(e) {
			var p, t = this, sr;

			e = t.get(e);
			p = t.getPos(e);
			sr = t.getSize(e);

			return {
				x : p.x,
				y : p.y,
				w : sr.w,
				h : sr.h
			};
		},

		getSize : function(e) {
			var t = this, w, h;

			e = t.get(e);
			w = t.getStyle(e, 'width');
			h = t.getStyle(e, 'height');

			// Non pixel value, then force offset/clientWidth
			if (w.indexOf('px') === -1)
				w = 0;

			// Non pixel value, then force offset/clientWidth
			if (h.indexOf('px') === -1)
				h = 0;

			return {
				w : parseInt(w) || e.offsetWidth || e.clientWidth,
				h : parseInt(h) || e.offsetHeight || e.clientHeight
			};
		},

		getParent : function(n, f, r) {
			return this.getParents(n, f, r, false);
		},

		getParents : function(n, f, r, c) {
			var t = this, na, se = t.settings, o = [];

			n = t.get(n);
			c = c === undefined;

			if (se.strict_root)
				r = r || t.getRoot();

			// Wrap node name as func
			if (is(f, 'string')) {
				na = f;

				if (f === '*') {
					f = function(n) {return n.nodeType == 1;};
				} else {
					f = function(n) {
						return t.is(n, na);
					};
				}
			}

			while (n) {
				if (n == r || !n.nodeType || n.nodeType === 9)
					break;

				if (!f || f(n)) {
					if (c)
						o.push(n);
					else
						return n;
				}

				n = n.parentNode;
			}

			return c ? o : null;
		},

		get : function(e) {
			var n;

			if (e && this.doc && typeof(e) == 'string') {
				n = e;
				e = this.doc.getElementById(e);

				// IE and Opera returns meta elements when they match the specified input ID, but getElementsByName seems to do the trick
				if (e && e.id !== n)
					return this.doc.getElementsByName(n)[1];
			}

			return e;
		},

		getNext : function(node, selector) {
			return this._findSib(node, selector, 'nextSibling');
		},

		getPrev : function(node, selector) {
			return this._findSib(node, selector, 'previousSibling');
		},


		select : function(pa, s) {
			var t = this;

			return tinymce.dom.Sizzle(pa, t.get(s) || t.get(t.settings.root_element) || t.doc, []);
		},

		is : function(n, selector) {
			var i;

			// If it isn't an array then try to do some simple selectors instead of Sizzle for to boost performance
			if (n.length === undefined) {
				// Simple all selector
				if (selector === '*')
					return n.nodeType == 1;

				// Simple selector just elements
				if (simpleSelectorRe.test(selector)) {
					selector = selector.toLowerCase().split(/,/);
					n = n.nodeName.toLowerCase();

					for (i = selector.length - 1; i >= 0; i--) {
						if (selector[i] == n)
							return true;
					}

					return false;
				}
			}

			return tinymce.dom.Sizzle.matches(selector, n.nodeType ? [n] : n).length > 0;
		},


		add : function(p, n, a, h, c) {
			var t = this;

			return this.run(p, function(p) {
				var e, k;

				e = is(n, 'string') ? t.doc.createElement(n) : n;
				t.setAttribs(e, a);

				if (h) {
					if (h.nodeType)
						e.appendChild(h);
					else
						t.setHTML(e, h);
				}

				return !c ? p.appendChild(e) : e;
			});
		},

		create : function(n, a, h) {
			return this.add(this.doc.createElement(n), n, a, h, 1);
		},

		createHTML : function(n, a, h) {
			var o = '', t = this, k;

			o += '<' + n;

			for (k in a) {
				if (a.hasOwnProperty(k))
					o += ' ' + k + '="' + t.encode(a[k]) + '"';
			}

			// A call to tinymce.is doesn't work for some odd reason on IE9 possible bug inside their JS runtime
			if (typeof(h) != "undefined")
				return o + '>' + h + '</' + n + '>';

			return o + ' />';
		},

		remove : function(node, keep_children) {
			return this.run(node, function(node) {
				var child, parent = node.parentNode;

				if (!parent)
					return null;

				if (keep_children) {
					while (child = node.firstChild) {
						// IE 8 will crash if you don't remove completely empty text nodes
						if (!tinymce.isIE || child.nodeType !== 3 || child.nodeValue)
							parent.insertBefore(child, node);
						else
							node.removeChild(child);
					}
				}

				return parent.removeChild(node);
			});
		},

		setStyle : function(n, na, v) {
			var t = this;

			return t.run(n, function(e) {
				var s, i;

				s = e.style;

				// Camelcase it, if needed
				na = na.replace(/-(\D)/g, function(a, b){
					return b.toUpperCase();
				});

				// Default px suffix on these
				if (t.pixelStyles.test(na) && (tinymce.is(v, 'number') || /^[\-0-9\.]+$/.test(v)))
					v += 'px';

				switch (na) {
					case 'opacity':
						// IE specific opacity
						if (isIE) {
							s.filter = v === '' ? '' : "alpha(opacity=" + (v * 100) + ")";

							if (!n.currentStyle || !n.currentStyle.hasLayout)
								s.display = 'inline-block';
						}

						// Fix for older browsers
						s[na] = s['-moz-opacity'] = s['-khtml-opacity'] = v || '';
						break;

					case 'float':
						isIE ? s.styleFloat = v : s.cssFloat = v;
						break;
					
					default:
						s[na] = v || '';
				}

				// Force update of the style data
				if (t.settings.update_styles)
					t.setAttrib(e, 'data-mce-style');
			});
		},

		getStyle : function(n, na, c) {
			n = this.get(n);

			if (!n)
				return;

			// Gecko
			if (this.doc.defaultView && c) {
				// Remove camelcase
				na = na.replace(/[A-Z]/g, function(a){
					return '-' + a;
				});

				try {
					return this.doc.defaultView.getComputedStyle(n, null).getPropertyValue(na);
				} catch (ex) {
					// Old safari might fail
					return null;
				}
			}

			// Camelcase it, if needed
			na = na.replace(/-(\D)/g, function(a, b){
				return b.toUpperCase();
			});

			if (na == 'float')
				na = isIE ? 'styleFloat' : 'cssFloat';

			// IE & Opera
			if (n.currentStyle && c)
				return n.currentStyle[na];

			return n.style ? n.style[na] : undefined;
		},

		setStyles : function(e, o) {
			var t = this, s = t.settings, ol;

			ol = s.update_styles;
			s.update_styles = 0;

			each(o, function(v, n) {
				t.setStyle(e, n, v);
			});

			// Update style info
			s.update_styles = ol;
			if (s.update_styles)
				t.setAttrib(e, s.cssText);
		},

		removeAllAttribs: function(e) {
			return this.run(e, function(e) {
				var i, attrs = e.attributes;
				for (i = attrs.length - 1; i >= 0; i--) {
					e.removeAttributeNode(attrs.item(i));
				}
			});
		},

		setAttrib : function(e, n, v) {
			var t = this;

			// Whats the point
			if (!e || !n)
				return;

			// Strict XML mode
			if (t.settings.strict)
				n = n.toLowerCase();

			return this.run(e, function(e) {
				var s = t.settings;
				if (v !== null) {
					switch (n) {
						case "style":
							if (!is(v, 'string')) {
								each(v, function(v, n) {
									t.setStyle(e, n, v);
								});

								return;
							}

							// No mce_style for elements with these since they might get resized by the user
							if (s.keep_values) {
								if (v && !t._isRes(v))
									e.setAttribute('data-mce-style', v, 2);
								else
									e.removeAttribute('data-mce-style', 2);
							}

							e.style.cssText = v;
							break;

						case "class":
							e.className = v || ''; // Fix IE null bug
							break;

						case "src":
						case "href":
							if (s.keep_values) {
								if (s.url_converter)
									v = s.url_converter.call(s.url_converter_scope || t, v, n, e);

								t.setAttrib(e, 'data-mce-' + n, v, 2);
							}

							break;

						case "shape":
							e.setAttribute('data-mce-style', v);
							break;
					}
				}
				if (is(v) && v !== null && v.length !== 0)
					e.setAttribute(n, '' + v, 2);
				else
					e.removeAttribute(n, 2);
			});
		},

		setAttribs : function(e, o) {
			var t = this;

			return this.run(e, function(e) {
				each(o, function(v, n) {
					t.setAttrib(e, n, v);
				});
			});
		},

		getAttrib : function(e, n, dv) {
			var v, t = this, undef;

			e = t.get(e);

			if (!e || e.nodeType !== 1)
				return dv === undef ? false : dv;

			if (!is(dv))
				dv = '';

			// Try the mce variant for these
			if (/^(src|href|style|coords|shape)$/.test(n)) {
				v = e.getAttribute("data-mce-" + n);

				if (v)
					return v;
			}

			if (isIE && t.props[n]) {
				v = e[t.props[n]];
				v = v && v.nodeValue ? v.nodeValue : v;
			}

			if (!v)
				v = e.getAttribute(n, 2);

			// Check boolean attribs
			if (/^(checked|compact|declare|defer|disabled|ismap|multiple|nohref|noshade|nowrap|readonly|selected)$/.test(n)) {
				if (e[t.props[n]] === true && v === '')
					return n;

				return v ? n : '';
			}

			// Inner input elements will override attributes on form elements
			if (e.nodeName === "FORM" && e.getAttributeNode(n))
				return e.getAttributeNode(n).nodeValue;

			if (n === 'style') {
				v = v || e.style.cssText;

				if (v) {
					v = t.serializeStyle(t.parseStyle(v), e.nodeName);

					if (t.settings.keep_values && !t._isRes(v))
						e.setAttribute('data-mce-style', v);
				}
			}

			// Remove Apple and WebKit stuff
			if (isWebKit && n === "class" && v)
				v = v.replace(/(apple|webkit)\-[a-z\-]+/gi, '');

			// Handle IE issues
			if (isIE) {
				switch (n) {
					case 'rowspan':
					case 'colspan':
						// IE returns 1 as default value
						if (v === 1)
							v = '';

						break;

					case 'size':
						// IE returns +0 as default value for size
						if (v === '+0' || v === 20 || v === 0)
							v = '';

						break;

					case 'width':
					case 'height':
					case 'vspace':
					case 'checked':
					case 'disabled':
					case 'readonly':
						if (v === 0)
							v = '';

						break;

					case 'hspace':
						// IE returns -1 as default value
						if (v === -1)
							v = '';

						break;

					case 'maxlength':
					case 'tabindex':
						// IE returns default value
						if (v === 32768 || v === 2147483647 || v === '32768')
							v = '';

						break;

					case 'multiple':
					case 'compact':
					case 'noshade':
					case 'nowrap':
						if (v === 65535)
							return n;

						return dv;

					case 'shape':
						v = v.toLowerCase();
						break;

					default:
						// IE has odd anonymous function for event attributes
						if (n.indexOf('on') === 0 && v)
							v = tinymce._replace(/^function\s+\w+\(\)\s+\{\s+(.*)\s+\}$/, '$1', '' + v);
				}
			}

			return (v !== undef && v !== null && v !== '') ? '' + v : dv;
		},

		getPos : function(n, ro) {
			var t = this, x = 0, y = 0, e, d = t.doc, r;

			n = t.get(n);
			ro = ro || d.body;

			if (n) {
				// Use getBoundingClientRect if it exists since it's faster than looping offset nodes
				if (n.getBoundingClientRect) {
					n = n.getBoundingClientRect();
					e = t.boxModel ? d.documentElement : d.body;

					// Add scroll offsets from documentElement or body since IE with the wrong box model will use d.body and so do WebKit
					// Also remove the body/documentelement clientTop/clientLeft on IE 6, 7 since they offset the position
					x = n.left + (d.documentElement.scrollLeft || d.body.scrollLeft) - e.clientTop;
					y = n.top + (d.documentElement.scrollTop || d.body.scrollTop) - e.clientLeft;

					return {x : x, y : y};
				}

				r = n;
				while (r && r != ro && r.nodeType) {
					x += r.offsetLeft || 0;
					y += r.offsetTop || 0;
					r = r.offsetParent;
				}

				r = n.parentNode;
				while (r && r != ro && r.nodeType) {
					x -= r.scrollLeft || 0;
					y -= r.scrollTop || 0;
					r = r.parentNode;
				}
			}

			return {x : x, y : y};
		},

		parseStyle : function(st) {
			return this.styles.parse(st);
		},

		serializeStyle : function(o, name) {
			return this.styles.serialize(o, name);
		},

		loadCSS : function(u) {
			var t = this, d = t.doc, head;

			if (!u)
				u = '';

			head = t.select('head')[0];

			each(u.split(','), function(u) {
				var link;

				if (t.files[u])
					return;

				t.files[u] = true;
				link = t.create('link', {rel : 'stylesheet', href : tinymce._addVer(u)});

				// IE 8 has a bug where dynamically loading stylesheets would produce a 1 item remaining bug
				// This fix seems to resolve that issue by realcing the document ones a stylesheet finishes loading
				// It's ugly but it seems to work fine.
				if (isIE && d.documentMode && d.recalc) {
					link.onload = function() {
						if (d.recalc)
							d.recalc();

						link.onload = null;
					};
				}

				head.appendChild(link);
			});
		},

		addClass : function(e, c) {
			return this.run(e, function(e) {
				var o;

				if (!c)
					return 0;

				if (this.hasClass(e, c))
					return e.className;

				o = this.removeClass(e, c);

				return e.className = (o != '' ? (o + ' ') : '') + c;
			});
		},

		removeClass : function(e, c) {
			var t = this, re;

			return t.run(e, function(e) {
				var v;

				if (t.hasClass(e, c)) {
					if (!re)
						re = new RegExp("(^|\\s+)" + c + "(\\s+|$)", "g");

					v = e.className.replace(re, ' ');
					v = tinymce.trim(v != ' ' ? v : '');

					e.className = v;

					// Empty class attr
					if (!v) {
						e.removeAttribute('class');
						e.removeAttribute('className');
					}

					return v;
				}

				return e.className;
			});
		},

		hasClass : function(n, c) {
			n = this.get(n);

			if (!n || !c)
				return false;

			return (' ' + n.className + ' ').indexOf(' ' + c + ' ') !== -1;
		},

		show : function(e) {
			return this.setStyle(e, 'display', 'block');
		},

		hide : function(e) {
			return this.setStyle(e, 'display', 'none');
		},

		isHidden : function(e) {
			e = this.get(e);

			return !e || e.style.display == 'none' || this.getStyle(e, 'display') == 'none';
		},

		uniqueId : function(p) {
			return (!p ? 'mce_' : p) + (this.counter++);
		},

		setHTML : function(element, html) {
			var self = this;

			return self.run(element, function(element) {
				if (isIE) {
					// Remove all child nodes, IE keeps empty text nodes in DOM
					while (element.firstChild)
						element.removeChild(element.firstChild);

					try {
						// IE will remove comments from the beginning
						// unless you padd the contents with something
						element.innerHTML = '<br />' + html;
						element.removeChild(element.firstChild);
					} catch (ex) {
						// IE sometimes produces an unknown runtime error on innerHTML if it's an block element within a block element for example a div inside a p
						// This seems to fix this problem

						// Create new div with HTML contents and a BR infront to keep comments
						element = self.create('div');
						element.innerHTML = '<br />' + html;

						// Add all children from div to target
						each (element.childNodes, function(node, i) {
							// Skip br element
							if (i)
								element.appendChild(node);
						});
					}
				} else
					element.innerHTML = html;

				return html;
			});
		},

		getOuterHTML : function(elm) {
			var doc, self = this;

			elm = self.get(elm);

			if (!elm)
				return null;

			if (elm.nodeType === 1 && self.hasOuterHTML)
				return elm.outerHTML;

			doc = (elm.ownerDocument || self.doc).createElement("body");
			doc.appendChild(elm.cloneNode(true));

			return doc.innerHTML;
		},

		setOuterHTML : function(e, h, d) {
			var t = this;

			function setHTML(e, h, d) {
				var n, tp;

				tp = d.createElement("body");
				tp.innerHTML = h;

				n = tp.lastChild;
				while (n) {
					t.insertAfter(n.cloneNode(true), e);
					n = n.previousSibling;
				}

				t.remove(e);
			};

			return this.run(e, function(e) {
				e = t.get(e);

				// Only set HTML on elements
				if (e.nodeType == 1) {
					d = d || e.ownerDocument || t.doc;

					if (isIE) {
						try {
							// Try outerHTML for IE it sometimes produces an unknown runtime error
							if (isIE && e.nodeType == 1)
								e.outerHTML = h;
							else
								setHTML(e, h, d);
						} catch (ex) {
							// Fix for unknown runtime error
							setHTML(e, h, d);
						}
					} else
						setHTML(e, h, d);
				}
			});
		},

		decode : Entities.decode,

		encode : Entities.encodeAllRaw,

		insertAfter : function(node, reference_node) {
			reference_node = this.get(reference_node);

			return this.run(node, function(node) {
				var parent, nextSibling;

				parent = reference_node.parentNode;
				nextSibling = reference_node.nextSibling;

				if (nextSibling)
					parent.insertBefore(node, nextSibling);
				else
					parent.appendChild(node);

				return node;
			});
		},

		isBlock : function(node) {
			var type = node.nodeType;

			// If it's a node then check the type and use the nodeName
			if (type)
				return !!(type === 1 && blockElementsMap[node.nodeName]);

			return !!blockElementsMap[node];
		},

		replace : function(n, o, k) {
			var t = this;

			if (is(o, 'array'))
				n = n.cloneNode(true);

			return t.run(o, function(o) {
				if (k) {
					each(tinymce.grep(o.childNodes), function(c) {
						n.appendChild(c);
					});
				}

				return o.parentNode.replaceChild(n, o);
			});
		},

		rename : function(elm, name) {
			var t = this, newElm;

			if (elm.nodeName != name.toUpperCase()) {
				// Rename block element
				newElm = t.create(name);

				// Copy attribs to new block
				each(t.getAttribs(elm), function(attr_node) {
					t.setAttrib(newElm, attr_node.nodeName, t.getAttrib(elm, attr_node.nodeName));
				});

				// Replace block
				t.replace(newElm, elm, 1);
			}

			return newElm || elm;
		},

		findCommonAncestor : function(a, b) {
			var ps = a, pe;

			while (ps) {
				pe = b;

				while (pe && ps != pe)
					pe = pe.parentNode;

				if (ps == pe)
					break;

				ps = ps.parentNode;
			}

			if (!ps && a.ownerDocument)
				return a.ownerDocument.documentElement;

			return ps;
		},

		toHex : function(s) {
			var c = /^\s*rgb\s*?\(\s*?([0-9]+)\s*?,\s*?([0-9]+)\s*?,\s*?([0-9]+)\s*?\)\s*$/i.exec(s);

			function hex(s) {
				s = parseInt(s).toString(16);

				return s.length > 1 ? s : '0' + s; // 0 -> 00
			};

			if (c) {
				s = '#' + hex(c[1]) + hex(c[2]) + hex(c[3]);

				return s;
			}

			return s;
		},

		getClasses : function() {
			var t = this, cl = [], i, lo = {}, f = t.settings.class_filter, ov;

			if (t.classes)
				return t.classes;

			function addClasses(s) {
				// IE style imports
				each(s.imports, function(r) {
					addClasses(r);
				});

				each(s.cssRules || s.rules, function(r) {
					// Real type or fake it on IE
					switch (r.type || 1) {
						// Rule
						case 1:
							if (r.selectorText) {
								each(r.selectorText.split(','), function(v) {
									v = v.replace(/^\s*|\s*$|^\s\./g, "");

									// Is internal or it doesn't contain a class
									if (/\.mce/.test(v) || !/\.[\w\-]+$/.test(v))
										return;

									// Remove everything but class name
									ov = v;
									v = tinymce._replace(/.*\.([a-z0-9_\-]+).*/i, '$1', v);

									// Filter classes
									if (f && !(v = f(v, ov)))
										return;

									if (!lo[v]) {
										cl.push({'class' : v});
										lo[v] = 1;
									}
								});
							}
							break;

						// Import
						case 3:
							addClasses(r.styleSheet);
							break;
					}
				});
			};

			try {
				each(t.doc.styleSheets, addClasses);
			} catch (ex) {
				// Ignore
			}

			if (cl.length > 0)
				t.classes = cl;

			return cl;
		},

		run : function(e, f, s) {
			var t = this, o;

			if (t.doc && typeof(e) === 'string')
				e = t.get(e);

			if (!e)
				return false;

			s = s || this;
			if (!e.nodeType && (e.length || e.length === 0)) {
				o = [];

				each(e, function(e, i) {
					if (e) {
						if (typeof(e) == 'string')
							e = t.doc.getElementById(e);

						o.push(f.call(s, e, i));
					}
				});

				return o;
			}

			return f.call(s, e);
		},

		getAttribs : function(n) {
			var o;

			n = this.get(n);

			if (!n)
				return [];

			if (isIE) {
				o = [];

				// Object will throw exception in IE
				if (n.nodeName == 'OBJECT')
					return n.attributes;

				// IE doesn't keep the selected attribute if you clone option elements
				if (n.nodeName === 'OPTION' && this.getAttrib(n, 'selected'))
					o.push({specified : 1, nodeName : 'selected'});

				// It's crazy that this is faster in IE but it's because it returns all attributes all the time
				n.cloneNode(false).outerHTML.replace(/<\/?[\w:\-]+ ?|=[\"][^\"]+\"|=\'[^\']+\'|=[\w\-]+|>/gi, '').replace(/[\w:\-]+/gi, function(a) {
					o.push({specified : 1, nodeName : a});
				});

				return o;
			}

			return n.attributes;
		},

		isEmpty : function(node, elements) {
			var self = this, i, attributes, type, walker, name, parentNode;

			node = node.firstChild;
			if (node) {
				walker = new tinymce.dom.TreeWalker(node);
				elements = elements || self.schema ? self.schema.getNonEmptyElements() : null;

				do {
					type = node.nodeType;

					if (type === 1) {
						// Ignore bogus elements
						if (node.getAttribute('data-mce-bogus'))
							continue;

						// Keep empty elements like <img />
						name = node.nodeName.toLowerCase();
						if (elements && elements[name]) {
							// Ignore single BR elements in blocks like <p><br /></p>
							parentNode = node.parentNode;
							if (name === 'br' && self.isBlock(parentNode) && parentNode.firstChild === node && parentNode.lastChild === node) {
								continue;
							}

							return false;
						}

						// Keep elements with data-bookmark attributes or name attribute like <a name="1"></a>
						attributes = self.getAttribs(node);
						i = node.attributes.length;
						while (i--) {
							name = node.attributes[i].nodeName;
							if (name === "name" || name === 'data-mce-bookmark')
								return false;
						}
					}

					// Keep non whitespace text nodes
					if ((type === 3 && !whiteSpaceRegExp.test(node.nodeValue)))
						return false;
				} while (node = walker.next());
			}

			return true;
		},

		destroy : function(s) {
			var t = this;

			if (t.events)
				t.events.destroy();

			t.win = t.doc = t.root = t.events = null;

			// Manual destroy then remove unload handler
			if (!s)
				tinymce.removeUnload(t.destroy);
		},

		createRng : function() {
			var d = this.doc;

			return d.createRange ? d.createRange() : new tinymce.dom.Range(this);
		},

		nodeIndex : function(node, normalized) {
			var idx = 0, lastNodeType, lastNode, nodeType;

			if (node) {
				for (lastNodeType = node.nodeType, node = node.previousSibling, lastNode = node; node; node = node.previousSibling) {
					nodeType = node.nodeType;

					// Normalize text nodes
					if (normalized && nodeType == 3) {
						if (nodeType == lastNodeType || !node.nodeValue.length)
							continue;
					}
					idx++;
					lastNodeType = nodeType;
				}
			}

			return idx;
		},

		split : function(pe, e, re) {
			var t = this, r = t.createRng(), bef, aft, pa;

			// W3C valid browsers tend to leave empty nodes to the left/right side of the contents, this makes sense
			// but we don't want that in our code since it serves no purpose for the end user
			// For example if this is chopped:
			//   <p>text 1<span><b>CHOP</b></span>text 2</p>
			// would produce:
			//   <p>text 1<span></span></p><b>CHOP</b><p><span></span>text 2</p>
			// this function will then trim of empty edges and produce:
			//   <p>text 1</p><b>CHOP</b><p>text 2</p>
			function trim(node) {
				var i, children = node.childNodes, type = node.nodeType;

				if (type == 1 && node.getAttribute('data-mce-type') == 'bookmark')
					return;

				for (i = children.length - 1; i >= 0; i--)
					trim(children[i]);

				if (type != 9) {
					// Keep non whitespace text nodes
					if (type == 3 && node.nodeValue.length > 0) {
						// If parent element isn't a block or there isn't any useful contents for example "<p>   </p>"
						if (!t.isBlock(node.parentNode) || tinymce.trim(node.nodeValue).length > 0)
							return;
					} else if (type == 1) {
						// If the only child is a bookmark then move it up
						children = node.childNodes;
						if (children.length == 1 && children[0] && children[0].nodeType == 1 && children[0].getAttribute('data-mce-type') == 'bookmark')
							node.parentNode.insertBefore(children[0], node);

						// Keep non empty elements or img, hr etc
						if (children.length || /^(br|hr|input|img)$/i.test(node.nodeName))
							return;
					}

					t.remove(node);
				}

				return node;
			};

			if (pe && e) {
				// Get before chunk
				r.setStart(pe.parentNode, t.nodeIndex(pe));
				r.setEnd(e.parentNode, t.nodeIndex(e));
				bef = r.extractContents();

				// Get after chunk
				r = t.createRng();
				r.setStart(e.parentNode, t.nodeIndex(e) + 1);
				r.setEnd(pe.parentNode, t.nodeIndex(pe) + 1);
				aft = r.extractContents();

				// Insert before chunk
				pa = pe.parentNode;
				pa.insertBefore(trim(bef), pe);

				// Insert middle chunk
				if (re)
					pa.replaceChild(re, e);
				else
					pa.insertBefore(e, pe);

				// Insert after chunk
				pa.insertBefore(trim(aft), pe);
				t.remove(pe);

				return re || e;
			}
		},

		bind : function(target, name, func, scope) {
			var t = this;

			if (!t.events)
				t.events = new tinymce.dom.EventUtils();

			return t.events.add(target, name, func, scope || this);
		},

		unbind : function(target, name, func) {
			var t = this;

			if (!t.events)
				t.events = new tinymce.dom.EventUtils();

			return t.events.remove(target, name, func);
		},


		_findSib : function(node, selector, name) {
			var t = this, f = selector;

			if (node) {
				// If expression make a function of it using is
				if (is(f, 'string')) {
					f = function(node) {
						return t.is(node, selector);
					};
				}

				// Loop all siblings
				for (node = node[name]; node; node = node[name]) {
					if (f(node))
						return node;
				}
			}

			return null;
		},

		_isRes : function(c) {
			// Is live resizble element
			return /^(top|left|bottom|right|width|height)/i.test(c) || /;\s*(top|left|bottom|right|width|height)/i.test(c);
		}

		/*
		walk : function(n, f, s) {
			var d = this.doc, w;

			if (d.createTreeWalker) {
				w = d.createTreeWalker(n, NodeFilter.SHOW_TEXT, null, false);

				while ((n = w.nextNode()) != null)
					f.call(s || this, n);
			} else
				tinymce.walk(n, f, 'childNodes', s);
		}
		*/

		/*
		toRGB : function(s) {
			var c = /^\s*?#([0-9A-F]{2})([0-9A-F]{1,2})([0-9A-F]{2})?\s*?$/.exec(s);

			if (c) {
				// #FFF -> #FFFFFF
				if (!is(c[3]))
					c[3] = c[2] = c[1];

				return "rgb(" + parseInt(c[1], 16) + "," + parseInt(c[2], 16) + "," + parseInt(c[3], 16) + ")";
			}

			return s;
		}
		*/
	});

	tinymce.DOM = new tinymce.dom.DOMUtils(document, {process_html : 0});
})(tinymce);

(function(ns) {
	// Range constructor
	function Range(dom) {
		var t = this,
			doc = dom.doc,
			EXTRACT = 0,
			CLONE = 1,
			DELETE = 2,
			TRUE = true,
			FALSE = false,
			START_OFFSET = 'startOffset',
			START_CONTAINER = 'startContainer',
			END_CONTAINER = 'endContainer',
			END_OFFSET = 'endOffset',
			extend = tinymce.extend,
			nodeIndex = dom.nodeIndex;

		extend(t, {
			// Inital states
			startContainer : doc,
			startOffset : 0,
			endContainer : doc,
			endOffset : 0,
			collapsed : TRUE,
			commonAncestorContainer : doc,

			// Range constants
			START_TO_START : 0,
			START_TO_END : 1,
			END_TO_END : 2,
			END_TO_START : 3,

			// Public methods
			setStart : setStart,
			setEnd : setEnd,
			setStartBefore : setStartBefore,
			setStartAfter : setStartAfter,
			setEndBefore : setEndBefore,
			setEndAfter : setEndAfter,
			collapse : collapse,
			selectNode : selectNode,
			selectNodeContents : selectNodeContents,
			compareBoundaryPoints : compareBoundaryPoints,
			deleteContents : deleteContents,
			extractContents : extractContents,
			cloneContents : cloneContents,
			insertNode : insertNode,
			surroundContents : surroundContents,
			cloneRange : cloneRange
		});

		function setStart(n, o) {
			_setEndPoint(TRUE, n, o);
		};

		function setEnd(n, o) {
			_setEndPoint(FALSE, n, o);
		};

		function setStartBefore(n) {
			setStart(n.parentNode, nodeIndex(n));
		};

		function setStartAfter(n) {
			setStart(n.parentNode, nodeIndex(n) + 1);
		};

		function setEndBefore(n) {
			setEnd(n.parentNode, nodeIndex(n));
		};

		function setEndAfter(n) {
			setEnd(n.parentNode, nodeIndex(n) + 1);
		};

		function collapse(ts) {
			if (ts) {
				t[END_CONTAINER] = t[START_CONTAINER];
				t[END_OFFSET] = t[START_OFFSET];
			} else {
				t[START_CONTAINER] = t[END_CONTAINER];
				t[START_OFFSET] = t[END_OFFSET];
			}

			t.collapsed = TRUE;
		};

		function selectNode(n) {
			setStartBefore(n);
			setEndAfter(n);
		};

		function selectNodeContents(n) {
			setStart(n, 0);
			setEnd(n, n.nodeType === 1 ? n.childNodes.length : n.nodeValue.length);
		};

		function compareBoundaryPoints(h, r) {
			var sc = t[START_CONTAINER], so = t[START_OFFSET], ec = t[END_CONTAINER], eo = t[END_OFFSET],
			rsc = r.startContainer, rso = r.startOffset, rec = r.endContainer, reo = r.endOffset;

			// Check START_TO_START
			if (h === 0)
				return _compareBoundaryPoints(sc, so, rsc, rso);
	
			// Check START_TO_END
			if (h === 1)
				return _compareBoundaryPoints(ec, eo, rsc, rso);
	
			// Check END_TO_END
			if (h === 2)
				return _compareBoundaryPoints(ec, eo, rec, reo);
	
			// Check END_TO_START
			if (h === 3) 
				return _compareBoundaryPoints(sc, so, rec, reo);
		};

		function deleteContents() {
			_traverse(DELETE);
		};

		function extractContents() {
			return _traverse(EXTRACT);
		};

		function cloneContents() {
			return _traverse(CLONE);
		};

		function insertNode(n) {
			var startContainer = this[START_CONTAINER],
				startOffset = this[START_OFFSET], nn, o;

			// Node is TEXT_NODE or CDATA
			if ((startContainer.nodeType === 3 || startContainer.nodeType === 4) && startContainer.nodeValue) {
				if (!startOffset) {
					// At the start of text
					startContainer.parentNode.insertBefore(n, startContainer);
				} else if (startOffset >= startContainer.nodeValue.length) {
					// At the end of text
					dom.insertAfter(n, startContainer);
				} else {
					// Middle, need to split
					nn = startContainer.splitText(startOffset);
					startContainer.parentNode.insertBefore(n, nn);
				}
			} else {
				// Insert element node
				if (startContainer.childNodes.length > 0)
					o = startContainer.childNodes[startOffset];

				if (o)
					startContainer.insertBefore(n, o);
				else
					startContainer.appendChild(n);
			}
		};

		function surroundContents(n) {
			var f = t.extractContents();

			t.insertNode(n);
			n.appendChild(f);
			t.selectNode(n);
		};

		function cloneRange() {
			return extend(new Range(dom), {
				startContainer : t[START_CONTAINER],
				startOffset : t[START_OFFSET],
				endContainer : t[END_CONTAINER],
				endOffset : t[END_OFFSET],
				collapsed : t.collapsed,
				commonAncestorContainer : t.commonAncestorContainer
			});
		};

		// Private methods

		function _getSelectedNode(container, offset) {
			var child;

			if (container.nodeType == 3 /* TEXT_NODE */)
				return container;

			if (offset < 0)
				return container;

			child = container.firstChild;
			while (child && offset > 0) {
				--offset;
				child = child.nextSibling;
			}

			if (child)
				return child;

			return container;
		};

		function _isCollapsed() {
			return (t[START_CONTAINER] == t[END_CONTAINER] && t[START_OFFSET] == t[END_OFFSET]);
		};

		function _compareBoundaryPoints(containerA, offsetA, containerB, offsetB) {
			var c, offsetC, n, cmnRoot, childA, childB;
			
			// In the first case the boundary-points have the same container. A is before B
			// if its offset is less than the offset of B, A is equal to B if its offset is
			// equal to the offset of B, and A is after B if its offset is greater than the
			// offset of B.
			if (containerA == containerB) {
				if (offsetA == offsetB)
					return 0; // equal

				if (offsetA < offsetB)
					return -1; // before

				return 1; // after
			}

			// In the second case a child node C of the container of A is an ancestor
			// container of B. In this case, A is before B if the offset of A is less than or
			// equal to the index of the child node C and A is after B otherwise.
			c = containerB;
			while (c && c.parentNode != containerA)
				c = c.parentNode;

			if (c) {
				offsetC = 0;
				n = containerA.firstChild;

				while (n != c && offsetC < offsetA) {
					offsetC++;
					n = n.nextSibling;
				}

				if (offsetA <= offsetC)
					return -1; // before

				return 1; // after
			}

			// In the third case a child node C of the container of B is an ancestor container
			// of A. In this case, A is before B if the index of the child node C is less than
			// the offset of B and A is after B otherwise.
			c = containerA;
			while (c && c.parentNode != containerB) {
				c = c.parentNode;
			}

			if (c) {
				offsetC = 0;
				n = containerB.firstChild;

				while (n != c && offsetC < offsetB) {
					offsetC++;
					n = n.nextSibling;
				}

				if (offsetC < offsetB)
					return -1; // before

				return 1; // after
			}

			// In the fourth case, none of three other cases hold: the containers of A and B
			// are siblings or descendants of sibling nodes. In this case, A is before B if
			// the container of A is before the container of B in a pre-order traversal of the
			// Ranges' context tree and A is after B otherwise.
			cmnRoot = dom.findCommonAncestor(containerA, containerB);
			childA = containerA;

			while (childA && childA.parentNode != cmnRoot)
				childA = childA.parentNode;

			if (!childA)
				childA = cmnRoot;

			childB = containerB;
			while (childB && childB.parentNode != cmnRoot)
				childB = childB.parentNode;

			if (!childB)
				childB = cmnRoot;

			if (childA == childB)
				return 0; // equal

			n = cmnRoot.firstChild;
			while (n) {
				if (n == childA)
					return -1; // before

				if (n == childB)
					return 1; // after

				n = n.nextSibling;
			}
		};

		function _setEndPoint(st, n, o) {
			var ec, sc;

			if (st) {
				t[START_CONTAINER] = n;
				t[START_OFFSET] = o;
			} else {
				t[END_CONTAINER] = n;
				t[END_OFFSET] = o;
			}

			// If one boundary-point of a Range is set to have a root container
			// other than the current one for the Range, the Range is collapsed to
			// the new position. This enforces the restriction that both boundary-
			// points of a Range must have the same root container.
			ec = t[END_CONTAINER];
			while (ec.parentNode)
				ec = ec.parentNode;

			sc = t[START_CONTAINER];
			while (sc.parentNode)
				sc = sc.parentNode;

			if (sc == ec) {
				// The start position of a Range is guaranteed to never be after the
				// end position. To enforce this restriction, if the start is set to
				// be at a position after the end, the Range is collapsed to that
				// position.
				if (_compareBoundaryPoints(t[START_CONTAINER], t[START_OFFSET], t[END_CONTAINER], t[END_OFFSET]) > 0)
					t.collapse(st);
			} else
				t.collapse(st);

			t.collapsed = _isCollapsed();
			t.commonAncestorContainer = dom.findCommonAncestor(t[START_CONTAINER], t[END_CONTAINER]);
		};

		function _traverse(how) {
			var c, endContainerDepth = 0, startContainerDepth = 0, p, depthDiff, startNode, endNode, sp, ep;

			if (t[START_CONTAINER] == t[END_CONTAINER])
				return _traverseSameContainer(how);

			for (c = t[END_CONTAINER], p = c.parentNode; p; c = p, p = p.parentNode) {
				if (p == t[START_CONTAINER])
					return _traverseCommonStartContainer(c, how);

				++endContainerDepth;
			}

			for (c = t[START_CONTAINER], p = c.parentNode; p; c = p, p = p.parentNode) {
				if (p == t[END_CONTAINER])
					return _traverseCommonEndContainer(c, how);

				++startContainerDepth;
			}

			depthDiff = startContainerDepth - endContainerDepth;

			startNode = t[START_CONTAINER];
			while (depthDiff > 0) {
				startNode = startNode.parentNode;
				depthDiff--;
			}

			endNode = t[END_CONTAINER];
			while (depthDiff < 0) {
				endNode = endNode.parentNode;
				depthDiff++;
			}

			// ascend the ancestor hierarchy until we have a common parent.
			for (sp = startNode.parentNode, ep = endNode.parentNode; sp != ep; sp = sp.parentNode, ep = ep.parentNode) {
				startNode = sp;
				endNode = ep;
			}

			return _traverseCommonAncestors(startNode, endNode, how);
		};

		 function _traverseSameContainer(how) {
			var frag, s, sub, n, cnt, sibling, xferNode;

			if (how != DELETE)
				frag = doc.createDocumentFragment();

			// If selection is empty, just return the fragment
			if (t[START_OFFSET] == t[END_OFFSET])
				return frag;

			// Text node needs special case handling
			if (t[START_CONTAINER].nodeType == 3 /* TEXT_NODE */) {
				// get the substring
				s = t[START_CONTAINER].nodeValue;
				sub = s.substring(t[START_OFFSET], t[END_OFFSET]);

				// set the original text node to its new value
				if (how != CLONE) {
					t[START_CONTAINER].deleteData(t[START_OFFSET], t[END_OFFSET] - t[START_OFFSET]);

					// Nothing is partially selected, so collapse to start point
					t.collapse(TRUE);
				}

				if (how == DELETE)
					return;

				frag.appendChild(doc.createTextNode(sub));
				return frag;
			}

			// Copy nodes between the start/end offsets.
			n = _getSelectedNode(t[START_CONTAINER], t[START_OFFSET]);
			cnt = t[END_OFFSET] - t[START_OFFSET];

			while (cnt > 0) {
				sibling = n.nextSibling;
				xferNode = _traverseFullySelected(n, how);

				if (frag)
					frag.appendChild( xferNode );

				--cnt;
				n = sibling;
			}

			// Nothing is partially selected, so collapse to start point
			if (how != CLONE)
				t.collapse(TRUE);

			return frag;
		};

		function _traverseCommonStartContainer(endAncestor, how) {
			var frag, n, endIdx, cnt, sibling, xferNode;

			if (how != DELETE)
				frag = doc.createDocumentFragment();

			n = _traverseRightBoundary(endAncestor, how);

			if (frag)
				frag.appendChild(n);

			endIdx = nodeIndex(endAncestor);
			cnt = endIdx - t[START_OFFSET];

			if (cnt <= 0) {
				// Collapse to just before the endAncestor, which
				// is partially selected.
				if (how != CLONE) {
					t.setEndBefore(endAncestor);
					t.collapse(FALSE);
				}

				return frag;
			}

			n = endAncestor.previousSibling;
			while (cnt > 0) {
				sibling = n.previousSibling;
				xferNode = _traverseFullySelected(n, how);

				if (frag)
					frag.insertBefore(xferNode, frag.firstChild);

				--cnt;
				n = sibling;
			}

			// Collapse to just before the endAncestor, which
			// is partially selected.
			if (how != CLONE) {
				t.setEndBefore(endAncestor);
				t.collapse(FALSE);
			}

			return frag;
		};

		function _traverseCommonEndContainer(startAncestor, how) {
			var frag, startIdx, n, cnt, sibling, xferNode;

			if (how != DELETE)
				frag = doc.createDocumentFragment();

			n = _traverseLeftBoundary(startAncestor, how);
			if (frag)
				frag.appendChild(n);

			startIdx = nodeIndex(startAncestor);
			++startIdx; // Because we already traversed it

			cnt = t[END_OFFSET] - startIdx;
			n = startAncestor.nextSibling;
			while (cnt > 0) {
				sibling = n.nextSibling;
				xferNode = _traverseFullySelected(n, how);

				if (frag)
					frag.appendChild(xferNode);

				--cnt;
				n = sibling;
			}

			if (how != CLONE) {
				t.setStartAfter(startAncestor);
				t.collapse(TRUE);
			}

			return frag;
		};

		function _traverseCommonAncestors(startAncestor, endAncestor, how) {
			var n, frag, commonParent, startOffset, endOffset, cnt, sibling, nextSibling;

			if (how != DELETE)
				frag = doc.createDocumentFragment();

			n = _traverseLeftBoundary(startAncestor, how);
			if (frag)
				frag.appendChild(n);

			commonParent = startAncestor.parentNode;
			startOffset = nodeIndex(startAncestor);
			endOffset = nodeIndex(endAncestor);
			++startOffset;

			cnt = endOffset - startOffset;
			sibling = startAncestor.nextSibling;

			while (cnt > 0) {
				nextSibling = sibling.nextSibling;
				n = _traverseFullySelected(sibling, how);

				if (frag)
					frag.appendChild(n);

				sibling = nextSibling;
				--cnt;
			}

			n = _traverseRightBoundary(endAncestor, how);

			if (frag)
				frag.appendChild(n);

			if (how != CLONE) {
				t.setStartAfter(startAncestor);
				t.collapse(TRUE);
			}

			return frag;
		};

		function _traverseRightBoundary(root, how) {
			var next = _getSelectedNode(t[END_CONTAINER], t[END_OFFSET] - 1), parent, clonedParent, prevSibling, clonedChild, clonedGrandParent, isFullySelected = next != t[END_CONTAINER];

			if (next == root)
				return _traverseNode(next, isFullySelected, FALSE, how);

			parent = next.parentNode;
			clonedParent = _traverseNode(parent, FALSE, FALSE, how);

			while (parent) {
				while (next) {
					prevSibling = next.previousSibling;
					clonedChild = _traverseNode(next, isFullySelected, FALSE, how);

					if (how != DELETE)
						clonedParent.insertBefore(clonedChild, clonedParent.firstChild);

					isFullySelected = TRUE;
					next = prevSibling;
				}

				if (parent == root)
					return clonedParent;

				next = parent.previousSibling;
				parent = parent.parentNode;

				clonedGrandParent = _traverseNode(parent, FALSE, FALSE, how);

				if (how != DELETE)
					clonedGrandParent.appendChild(clonedParent);

				clonedParent = clonedGrandParent;
			}
		};

		function _traverseLeftBoundary(root, how) {
			var next = _getSelectedNode(t[START_CONTAINER], t[START_OFFSET]), isFullySelected = next != t[START_CONTAINER], parent, clonedParent, nextSibling, clonedChild, clonedGrandParent;

			if (next == root)
				return _traverseNode(next, isFullySelected, TRUE, how);

			parent = next.parentNode;
			clonedParent = _traverseNode(parent, FALSE, TRUE, how);

			while (parent) {
				while (next) {
					nextSibling = next.nextSibling;
					clonedChild = _traverseNode(next, isFullySelected, TRUE, how);

					if (how != DELETE)
						clonedParent.appendChild(clonedChild);

					isFullySelected = TRUE;
					next = nextSibling;
				}

				if (parent == root)
					return clonedParent;

				next = parent.nextSibling;
				parent = parent.parentNode;

				clonedGrandParent = _traverseNode(parent, FALSE, TRUE, how);

				if (how != DELETE)
					clonedGrandParent.appendChild(clonedParent);

				clonedParent = clonedGrandParent;
			}
		};

		function _traverseNode(n, isFullySelected, isLeft, how) {
			var txtValue, newNodeValue, oldNodeValue, offset, newNode;

			if (isFullySelected)
				return _traverseFullySelected(n, how);

			if (n.nodeType == 3 /* TEXT_NODE */) {
				txtValue = n.nodeValue;

				if (isLeft) {
					offset = t[START_OFFSET];
					newNodeValue = txtValue.substring(offset);
					oldNodeValue = txtValue.substring(0, offset);
				} else {
					offset = t[END_OFFSET];
					newNodeValue = txtValue.substring(0, offset);
					oldNodeValue = txtValue.substring(offset);
				}

				if (how != CLONE)
					n.nodeValue = oldNodeValue;

				if (how == DELETE)
					return;

				newNode = n.cloneNode(FALSE);
				newNode.nodeValue = newNodeValue;

				return newNode;
			}

			if (how == DELETE)
				return;

			return n.cloneNode(FALSE);
		};

		function _traverseFullySelected(n, how) {
			if (how != DELETE)
				return how == CLONE ? n.cloneNode(TRUE) : n;

			n.parentNode.removeChild(n);
		};
	};

	ns.Range = Range;
})(tinymce.dom);

(function() {
	function Selection(selection) {
		var self = this, dom = selection.dom, TRUE = true, FALSE = false;

		function getPosition(rng, start) {
			var checkRng, startIndex = 0, endIndex, inside,
				children, child, offset, index, position = -1, parent;

			// Setup test range, collapse it and get the parent
			checkRng = rng.duplicate();
			checkRng.collapse(start);
			parent = checkRng.parentElement();

			// Check if the selection is within the right document
			if (parent.ownerDocument !== selection.dom.doc)
				return;

			// IE will report non editable elements as it's parent so look for an editable one
			while (parent.contentEditable === "false") {
				parent = parent.parentNode;
			}

			// If parent doesn't have any children then return that we are inside the element
			if (!parent.hasChildNodes()) {
				return {node : parent, inside : 1};
			}

			// Setup node list and endIndex
			children = parent.children;
			endIndex = children.length - 1;

			// Perform a binary search for the position
			while (startIndex <= endIndex) {
				index = Math.floor((startIndex + endIndex) / 2);

				// Move selection to node and compare the ranges
				child = children[index];
				checkRng.moveToElementText(child);
				position = checkRng.compareEndPoints(start ? 'StartToStart' : 'EndToEnd', rng);

				// Before/after or an exact match
				if (position > 0) {
					endIndex = index - 1;
				} else if (position < 0) {
					startIndex = index + 1;
				} else {
					return {node : child};
				}
			}

			// Check if child position is before or we didn't find a position
			if (position < 0) {
				// No element child was found use the parent element and the offset inside that
				if (!child) {
					checkRng.moveToElementText(parent);
					checkRng.collapse(true);
					child = parent;
					inside = true;
				} else
					checkRng.collapse(false);

				checkRng.setEndPoint(start ? 'EndToStart' : 'EndToEnd', rng);

				// Fix for edge case: <div style="width: 100px; height:100px;"><table>..</table>ab|c</div>
				if (checkRng.compareEndPoints(start ? 'StartToStart' : 'StartToEnd', rng) > 0) {
					checkRng = rng.duplicate();
					checkRng.collapse(start);

					offset = -1;
					while (parent == checkRng.parentElement()) {
						if (checkRng.move('character', -1) == 0)
							break;

						offset++;
					}
				}

				offset = offset || checkRng.text.replace('\r\n', ' ').length;
			} else {
				// Child position is after the selection endpoint
				checkRng.collapse(true);
				checkRng.setEndPoint(start ? 'StartToStart' : 'StartToEnd', rng);

				// Get the length of the text to find where the endpoint is relative to it's container
				offset = checkRng.text.replace('\r\n', ' ').length;
			}

			return {node : child, position : position, offset : offset, inside : inside};
		};

		// Returns a W3C DOM compatible range object by using the IE Range API
		function getRange() {
			var ieRange = selection.getRng(), domRange = dom.createRng(), element, collapsed, tmpRange, element2, bookmark, fail;

			// If selection is outside the current document just return an empty range
			element = ieRange.item ? ieRange.item(0) : ieRange.parentElement();
			if (element.ownerDocument != dom.doc)
				return domRange;

			collapsed = selection.isCollapsed();

			// Handle control selection
			if (ieRange.item) {
				domRange.setStart(element.parentNode, dom.nodeIndex(element));
				domRange.setEnd(domRange.startContainer, domRange.startOffset + 1);

				return domRange;
			}

			function findEndPoint(start) {
				var endPoint = getPosition(ieRange, start), container, offset, textNodeOffset = 0, sibling, undef, nodeValue;

				container = endPoint.node;
				offset = endPoint.offset;

				if (endPoint.inside && !container.hasChildNodes()) {
					domRange[start ? 'setStart' : 'setEnd'](container, 0);
					return;
				}

				if (offset === undef) {
					domRange[start ? 'setStartBefore' : 'setEndAfter'](container);
					return;
				}

				if (endPoint.position < 0) {
					sibling = endPoint.inside ? container.firstChild : container.nextSibling;

					if (!sibling) {
						domRange[start ? 'setStartAfter' : 'setEndAfter'](container);
						return;
					}

					if (!offset) {
						if (sibling.nodeType == 3)
							domRange[start ? 'setStart' : 'setEnd'](sibling, 0);
						else
							domRange[start ? 'setStartBefore' : 'setEndBefore'](sibling);

						return;
					}

					// Find the text node and offset
					while (sibling) {
						nodeValue = sibling.nodeValue;
						textNodeOffset += nodeValue.length;

						// We are at or passed the position we where looking for
						if (textNodeOffset >= offset) {
							container = sibling;
							textNodeOffset -= offset;
							textNodeOffset = nodeValue.length - textNodeOffset;
							break;
						}

						sibling = sibling.nextSibling;
					}
				} else {
					// Find the text node and offset
					sibling = container.previousSibling;

					if (!sibling)
						return domRange[start ? 'setStartBefore' : 'setEndBefore'](container);

					// If there isn't any text to loop then use the first position
					if (!offset) {
						if (container.nodeType == 3)
							domRange[start ? 'setStart' : 'setEnd'](sibling, container.nodeValue.length);
						else
							domRange[start ? 'setStartAfter' : 'setEndAfter'](sibling);

						return;
					}

					while (sibling) {
						textNodeOffset += sibling.nodeValue.length;

						// We are at or passed the position we where looking for
						if (textNodeOffset >= offset) {
							container = sibling;
							textNodeOffset -= offset;
							break;
						}

						sibling = sibling.previousSibling;
					}
				}

				domRange[start ? 'setStart' : 'setEnd'](container, textNodeOffset);
			};

			try {
				// Find start point
				findEndPoint(true);

				// Find end point if needed
				if (!collapsed)
					findEndPoint();
			} catch (ex) {
				// IE has a nasty bug where text nodes might throw "invalid argument" when you
				// access the nodeValue or other properties of text nodes. This seems to happend when
				// text nodes are split into two nodes by a delete/backspace call. So lets detect it and try to fix it.
				if (ex.number == -2147024809) {
					// Get the current selection
					bookmark = self.getBookmark(2);

					// Get start element
					tmpRange = ieRange.duplicate();
					tmpRange.collapse(true);
					element = tmpRange.parentElement();

					// Get end element
					if (!collapsed) {
						tmpRange = ieRange.duplicate();
						tmpRange.collapse(false);
						element2 = tmpRange.parentElement();
						element2.innerHTML = element2.innerHTML;
					}

					// Remove the broken elements
					element.innerHTML = element.innerHTML;

					// Restore the selection
					self.moveToBookmark(bookmark);

					// Since the range has moved we need to re-get it
					ieRange = selection.getRng();

					// Find start point
					findEndPoint(true);

					// Find end point if needed
					if (!collapsed)
						findEndPoint();
				} else
					throw ex; // Throw other errors
			}

			return domRange;
		};

		this.getBookmark = function(type) {
			var rng = selection.getRng(), start, end, bookmark = {};

			function getIndexes(node) {
				var node, parent, root, children, i, indexes = [];

				parent = node.parentNode;
				root = dom.getRoot().parentNode;

				while (parent != root && parent.nodeType !== 9) {
					children = parent.children;

					i = children.length;
					while (i--) {
						if (node === children[i]) {
							indexes.push(i);
							break;
						}
					}

					node = parent;
					parent = parent.parentNode;
				}

				return indexes;
			};

			function getBookmarkEndPoint(start) {
				var position;

				position = getPosition(rng, start);
				if (position) {
					return {
						position : position.position,
						offset : position.offset,
						indexes : getIndexes(position.node),
						inside : position.inside
					};
				}
			};

			// Non ubstructive bookmark
			if (type === 2) {
				// Handle text selection
				if (!rng.item) {
					bookmark.start = getBookmarkEndPoint(true);

					if (!selection.isCollapsed())
						bookmark.end = getBookmarkEndPoint();
				} else
					bookmark.start = {ctrl : true, indexes : getIndexes(rng.item(0))};
			}

			return bookmark;
		};

		this.moveToBookmark = function(bookmark) {
			var rng, body = dom.doc.body;

			function resolveIndexes(indexes) {
				var node, i, idx, children;

				node = dom.getRoot();
				for (i = indexes.length - 1; i >= 0; i--) {
					children = node.children;
					idx = indexes[i];

					if (idx <= children.length - 1) {
						node = children[idx];
					}
				}

				return node;
			};
			
			function setBookmarkEndPoint(start) {
				var endPoint = bookmark[start ? 'start' : 'end'], moveLeft, moveRng, undef;

				if (endPoint) {
					moveLeft = endPoint.position > 0;

					moveRng = body.createTextRange();
					moveRng.moveToElementText(resolveIndexes(endPoint.indexes));

					offset = endPoint.offset;
					if (offset !== undef) {
						moveRng.collapse(endPoint.inside || moveLeft);
						moveRng.moveStart('character', moveLeft ? -offset : offset);
					} else
						moveRng.collapse(start);

					rng.setEndPoint(start ? 'StartToStart' : 'EndToStart', moveRng);

					if (start)
						rng.collapse(true);
				}
			};

			if (bookmark.start) {
				if (bookmark.start.ctrl) {
					rng = body.createControlRange();
					rng.addElement(resolveIndexes(bookmark.start.indexes));
					rng.select();
				} else {
					rng = body.createTextRange();
					setBookmarkEndPoint(true);
					setBookmarkEndPoint();
					rng.select();
				}
			}
		};

		this.addRange = function(rng) {
			var ieRng, ctrlRng, startContainer, startOffset, endContainer, endOffset, doc = selection.dom.doc, body = doc.body;

			function setEndPoint(start) {
				var container, offset, marker, tmpRng, nodes;

				marker = dom.create('a');
				container = start ? startContainer : endContainer;
				offset = start ? startOffset : endOffset;
				tmpRng = ieRng.duplicate();

				if (container == doc || container == doc.documentElement) {
					container = body;
					offset = 0;
				}

				if (container.nodeType == 3) {
					container.parentNode.insertBefore(marker, container);
					tmpRng.moveToElementText(marker);
					tmpRng.moveStart('character', offset);
					dom.remove(marker);
					ieRng.setEndPoint(start ? 'StartToStart' : 'EndToEnd', tmpRng);
				} else {
					nodes = container.childNodes;

					if (nodes.length) {
						if (offset >= nodes.length) {
							dom.insertAfter(marker, nodes[nodes.length - 1]);
						} else {
							container.insertBefore(marker, nodes[offset]);
						}

						tmpRng.moveToElementText(marker);
					} else {
						// Empty node selection for example <div>|</div>
						marker = doc.createTextNode('\uFEFF');
						container.appendChild(marker);
						tmpRng.moveToElementText(marker.parentNode);
						tmpRng.collapse(TRUE);
					}

					ieRng.setEndPoint(start ? 'StartToStart' : 'EndToEnd', tmpRng);
					dom.remove(marker);
				}
			}

			// Setup some shorter versions
			startContainer = rng.startContainer;
			startOffset = rng.startOffset;
			endContainer = rng.endContainer;
			endOffset = rng.endOffset;
			ieRng = body.createTextRange();

			// If single element selection then try making a control selection out of it
			if (startContainer == endContainer && startContainer.nodeType == 1 && startOffset == endOffset - 1) {
				if (startOffset == endOffset - 1) {
					try {
						ctrlRng = body.createControlRange();
						ctrlRng.addElement(startContainer.childNodes[startOffset]);
						ctrlRng.select();
						return;
					} catch (ex) {
						// Ignore
					}
				}
			}

			// Set start/end point of selection
			setEndPoint(true);
			setEndPoint();

			// Select the new range and scroll it into view
			ieRng.select();
		};

		// Expose range method
		this.getRangeAt = getRange;
	};

	// Expose the selection object
	tinymce.dom.TridentSelection = Selection;
})();


/*
 * Sizzle CSS Selector Engine - v1.0
 *  Copyright 2009, The Dojo Foundation
 *  Released under the MIT, BSD, and GPL Licenses.
 *  More information: http://sizzlejs.com/
 */
(function(){

var chunker = /((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^\[\]]*\]|['"][^'"]*['"]|[^\[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,
	done = 0,
	toString = Object.prototype.toString,
	hasDuplicate = false,
	baseHasDuplicate = true;

// Here we check if the JavaScript engine is using some sort of
// optimization where it does not always call our comparision
// function. If that is the case, discard the hasDuplicate value.
//   Thus far that includes Google Chrome.
[0, 0].sort(function(){
	baseHasDuplicate = false;
	return 0;
});

var Sizzle = function(selector, context, results, seed) {
	results = results || [];
	context = context || document;

	var origContext = context;

	if ( context.nodeType !== 1 && context.nodeType !== 9 ) {
		return [];
	}
	
	if ( !selector || typeof selector !== "string" ) {
		return results;
	}

	var parts = [], m, set, checkSet, extra, prune = true, contextXML = Sizzle.isXML(context),
		soFar = selector, ret, cur, pop, i;
	
	// Reset the position of the chunker regexp (start from head)
	do {
		chunker.exec("");
		m = chunker.exec(soFar);

		if ( m ) {
			soFar = m[3];
		
			parts.push( m[1] );
		
			if ( m[2] ) {
				extra = m[3];
				break;
			}
		}
	} while ( m );

	if ( parts.length > 1 && origPOS.exec( selector ) ) {
		if ( parts.length === 2 && Expr.relative[ parts[0] ] ) {
			set = posProcess( parts[0] + parts[1], context );
		} else {
			set = Expr.relative[ parts[0] ] ?
				[ context ] :
				Sizzle( parts.shift(), context );

			while ( parts.length ) {
				selector = parts.shift();

				if ( Expr.relative[ selector ] ) {
					selector += parts.shift();
				}
				
				set = posProcess( selector, set );
			}
		}
	} else {
		// Take a shortcut and set the context if the root selector is an ID
		// (but not if it'll be faster if the inner selector is an ID)
		if ( !seed && parts.length > 1 && context.nodeType === 9 && !contextXML &&
				Expr.match.ID.test(parts[0]) && !Expr.match.ID.test(parts[parts.length - 1]) ) {
			ret = Sizzle.find( parts.shift(), context, contextXML );
			context = ret.expr ? Sizzle.filter( ret.expr, ret.set )[0] : ret.set[0];
		}

		if ( context ) {
			ret = seed ?
				{ expr: parts.pop(), set: makeArray(seed) } :
				Sizzle.find( parts.pop(), parts.length === 1 && (parts[0] === "~" || parts[0] === "+") && context.parentNode ? context.parentNode : context, contextXML );
			set = ret.expr ? Sizzle.filter( ret.expr, ret.set ) : ret.set;

			if ( parts.length > 0 ) {
				checkSet = makeArray(set);
			} else {
				prune = false;
			}

			while ( parts.length ) {
				cur = parts.pop();
				pop = cur;

				if ( !Expr.relative[ cur ] ) {
					cur = "";
				} else {
					pop = parts.pop();
				}

				if ( pop == null ) {
					pop = context;
				}

				Expr.relative[ cur ]( checkSet, pop, contextXML );
			}
		} else {
			checkSet = parts = [];
		}
	}

	if ( !checkSet ) {
		checkSet = set;
	}

	if ( !checkSet ) {
		Sizzle.error( cur || selector );
	}

	if ( toString.call(checkSet) === "[object Array]" ) {
		if ( !prune ) {
			results.push.apply( results, checkSet );
		} else if ( context && context.nodeType === 1 ) {
			for ( i = 0; checkSet[i] != null; i++ ) {
				if ( checkSet[i] && (checkSet[i] === true || checkSet[i].nodeType === 1 && Sizzle.contains(context, checkSet[i])) ) {
					results.push( set[i] );
				}
			}
		} else {
			for ( i = 0; checkSet[i] != null; i++ ) {
				if ( checkSet[i] && checkSet[i].nodeType === 1 ) {
					results.push( set[i] );
				}
			}
		}
	} else {
		makeArray( checkSet, results );
	}

	if ( extra ) {
		Sizzle( extra, origContext, results, seed );
		Sizzle.uniqueSort( results );
	}

	return results;
};

Sizzle.uniqueSort = function(results){
	if ( sortOrder ) {
		hasDuplicate = baseHasDuplicate;
		results.sort(sortOrder);

		if ( hasDuplicate ) {
			for ( var i = 1; i < results.length; i++ ) {
				if ( results[i] === results[i-1] ) {
					results.splice(i--, 1);
				}
			}
		}
	}

	return results;
};

Sizzle.matches = function(expr, set){
	return Sizzle(expr, null, null, set);
};

Sizzle.find = function(expr, context, isXML){
	var set;

	if ( !expr ) {
		return [];
	}

	for ( var i = 0, l = Expr.order.length; i < l; i++ ) {
		var type = Expr.order[i], match;
		
		if ( (match = Expr.leftMatch[ type ].exec( expr )) ) {
			var left = match[1];
			match.splice(1,1);

			if ( left.substr( left.length - 1 ) !== "\\" ) {
				match[1] = (match[1] || "").replace(/\\/g, "");
				set = Expr.find[ type ]( match, context, isXML );
				if ( set != null ) {
					expr = expr.replace( Expr.match[ type ], "" );
					break;
				}
			}
		}
	}

	if ( !set ) {
		set = context.getElementsByTagName("*");
	}

	return {set: set, expr: expr};
};

Sizzle.filter = function(expr, set, inplace, not){
	var old = expr, result = [], curLoop = set, match, anyFound,
		isXMLFilter = set && set[0] && Sizzle.isXML(set[0]);

	while ( expr && set.length ) {
		for ( var type in Expr.filter ) {
			if ( (match = Expr.leftMatch[ type ].exec( expr )) != null && match[2] ) {
				var filter = Expr.filter[ type ], found, item, left = match[1];
				anyFound = false;

				match.splice(1,1);

				if ( left.substr( left.length - 1 ) === "\\" ) {
					continue;
				}

				if ( curLoop === result ) {
					result = [];
				}

				if ( Expr.preFilter[ type ] ) {
					match = Expr.preFilter[ type ]( match, curLoop, inplace, result, not, isXMLFilter );

					if ( !match ) {
						anyFound = found = true;
					} else if ( match === true ) {
						continue;
					}
				}

				if ( match ) {
					for ( var i = 0; (item = curLoop[i]) != null; i++ ) {
						if ( item ) {
							found = filter( item, match, i, curLoop );
							var pass = not ^ !!found;

							if ( inplace && found != null ) {
								if ( pass ) {
									anyFound = true;
								} else {
									curLoop[i] = false;
								}
							} else if ( pass ) {
								result.push( item );
								anyFound = true;
							}
						}
					}
				}

				if ( found !== undefined ) {
					if ( !inplace ) {
						curLoop = result;
					}

					expr = expr.replace( Expr.match[ type ], "" );

					if ( !anyFound ) {
						return [];
					}

					break;
				}
			}
		}

		// Improper expression
		if ( expr === old ) {
			if ( anyFound == null ) {
				Sizzle.error( expr );
			} else {
				break;
			}
		}

		old = expr;
	}

	return curLoop;
};

Sizzle.error = function( msg ) {
	throw "Syntax error, unrecognized expression: " + msg;
};

var Expr = Sizzle.selectors = {
	order: [ "ID", "NAME", "TAG" ],
	match: {
		ID: /#((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,
		CLASS: /\.((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,
		NAME: /\[name=['"]*((?:[\w\u00c0-\uFFFF\-]|\\.)+)['"]*\]/,
		ATTR: /\[\s*((?:[\w\u00c0-\uFFFF\-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/,
		TAG: /^((?:[\w\u00c0-\uFFFF\*\-]|\\.)+)/,
		CHILD: /:(only|nth|last|first)-child(?:\((even|odd|[\dn+\-]*)\))?/,
		POS: /:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^\-]|$)/,
		PSEUDO: /:((?:[\w\u00c0-\uFFFF\-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/
	},
	leftMatch: {},
	attrMap: {
		"class": "className",
		"for": "htmlFor"
	},
	attrHandle: {
		href: function(elem){
			return elem.getAttribute("href");
		}
	},
	relative: {
		"+": function(checkSet, part){
			var isPartStr = typeof part === "string",
				isTag = isPartStr && !/\W/.test(part),
				isPartStrNotTag = isPartStr && !isTag;

			if ( isTag ) {
				part = part.toLowerCase();
			}

			for ( var i = 0, l = checkSet.length, elem; i < l; i++ ) {
				if ( (elem = checkSet[i]) ) {
					while ( (elem = elem.previousSibling) && elem.nodeType !== 1 ) {}

					checkSet[i] = isPartStrNotTag || elem && elem.nodeName.toLowerCase() === part ?
						elem || false :
						elem === part;
				}
			}

			if ( isPartStrNotTag ) {
				Sizzle.filter( part, checkSet, true );
			}
		},
		">": function(checkSet, part){
			var isPartStr = typeof part === "string",
				elem, i = 0, l = checkSet.length;

			if ( isPartStr && !/\W/.test(part) ) {
				part = part.toLowerCase();

				for ( ; i < l; i++ ) {
					elem = checkSet[i];
					if ( elem ) {
						var parent = elem.parentNode;
						checkSet[i] = parent.nodeName.toLowerCase() === part ? parent : false;
					}
				}
			} else {
				for ( ; i < l; i++ ) {
					elem = checkSet[i];
					if ( elem ) {
						checkSet[i] = isPartStr ?
							elem.parentNode :
							elem.parentNode === part;
					}
				}

				if ( isPartStr ) {
					Sizzle.filter( part, checkSet, true );
				}
			}
		},
		"": function(checkSet, part, isXML){
			var doneName = done++, checkFn = dirCheck, nodeCheck;

			if ( typeof part === "string" && !/\W/.test(part) ) {
				part = part.toLowerCase();
				nodeCheck = part;
				checkFn = dirNodeCheck;
			}

			checkFn("parentNode", part, doneName, checkSet, nodeCheck, isXML);
		},
		"~": function(checkSet, part, isXML){
			var doneName = done++, checkFn = dirCheck, nodeCheck;

			if ( typeof part === "string" && !/\W/.test(part) ) {
				part = part.toLowerCase();
				nodeCheck = part;
				checkFn = dirNodeCheck;
			}

			checkFn("previousSibling", part, doneName, checkSet, nodeCheck, isXML);
		}
	},
	find: {
		ID: function(match, context, isXML){
			if ( typeof context.getElementById !== "undefined" && !isXML ) {
				var m = context.getElementById(match[1]);
				return m ? [m] : [];
			}
		},
		NAME: function(match, context){
			if ( typeof context.getElementsByName !== "undefined" ) {
				var ret = [], results = context.getElementsByName(match[1]);

				for ( var i = 0, l = results.length; i < l; i++ ) {
					if ( results[i].getAttribute("name") === match[1] ) {
						ret.push( results[i] );
					}
				}

				return ret.length === 0 ? null : ret;
			}
		},
		TAG: function(match, context){
			return context.getElementsByTagName(match[1]);
		}
	},
	preFilter: {
		CLASS: function(match, curLoop, inplace, result, not, isXML){
			match = " " + match[1].replace(/\\/g, "") + " ";

			if ( isXML ) {
				return match;
			}

			for ( var i = 0, elem; (elem = curLoop[i]) != null; i++ ) {
				if ( elem ) {
					if ( not ^ (elem.className && (" " + elem.className + " ").replace(/[\t\n]/g, " ").indexOf(match) >= 0) ) {
						if ( !inplace ) {
							result.push( elem );
						}
					} else if ( inplace ) {
						curLoop[i] = false;
					}
				}
			}

			return false;
		},
		ID: function(match){
			return match[1].replace(/\\/g, "");
		},
		TAG: function(match, curLoop){
			return match[1].toLowerCase();
		},
		CHILD: function(match){
			if ( match[1] === "nth" ) {
				// parse equations like 'even', 'odd', '5', '2n', '3n+2', '4n-1', '-n+6'
				var test = /(-?)(\d*)n((?:\+|-)?\d*)/.exec(
					match[2] === "even" && "2n" || match[2] === "odd" && "2n+1" ||
					!/\D/.test( match[2] ) && "0n+" + match[2] || match[2]);

				// calculate the numbers (first)n+(last) including if they are negative
				match[2] = (test[1] + (test[2] || 1)) - 0;
				match[3] = test[3] - 0;
			}

			// TODO: Move to normal caching system
			match[0] = done++;

			return match;
		},
		ATTR: function(match, curLoop, inplace, result, not, isXML){
			var name = match[1].replace(/\\/g, "");
			
			if ( !isXML && Expr.attrMap[name] ) {
				match[1] = Expr.attrMap[name];
			}

			if ( match[2] === "~=" ) {
				match[4] = " " + match[4] + " ";
			}

			return match;
		},
		PSEUDO: function(match, curLoop, inplace, result, not){
			if ( match[1] === "not" ) {
				// If we're dealing with a complex expression, or a simple one
				if ( ( chunker.exec(match[3]) || "" ).length > 1 || /^\w/.test(match[3]) ) {
					match[3] = Sizzle(match[3], null, null, curLoop);
				} else {
					var ret = Sizzle.filter(match[3], curLoop, inplace, true ^ not);
					if ( !inplace ) {
						result.push.apply( result, ret );
					}
					return false;
				}
			} else if ( Expr.match.POS.test( match[0] ) || Expr.match.CHILD.test( match[0] ) ) {
				return true;
			}
			
			return match;
		},
		POS: function(match){
			match.unshift( true );
			return match;
		}
	},
	filters: {
		enabled: function(elem){
			return elem.disabled === false && elem.type !== "hidden";
		},
		disabled: function(elem){
			return elem.disabled === true;
		},
		checked: function(elem){
			return elem.checked === true;
		},
		selected: function(elem){
			// Accessing this property makes selected-by-default
			// options in Safari work properly
			elem.parentNode.selectedIndex;
			return elem.selected === true;
		},
		parent: function(elem){
			return !!elem.firstChild;
		},
		empty: function(elem){
			return !elem.firstChild;
		},
		has: function(elem, i, match){
			return !!Sizzle( match[3], elem ).length;
		},
		header: function(elem){
			return (/h\d/i).test( elem.nodeName );
		},
		text: function(elem){
			return "text" === elem.type;
		},
		radio: function(elem){
			return "radio" === elem.type;
		},
		checkbox: function(elem){
			return "checkbox" === elem.type;
		},
		file: function(elem){
			return "file" === elem.type;
		},
		password: function(elem){
			return "password" === elem.type;
		},
		submit: function(elem){
			return "submit" === elem.type;
		},
		image: function(elem){
			return "image" === elem.type;
		},
		reset: function(elem){
			return "reset" === elem.type;
		},
		button: function(elem){
			return "button" === elem.type || elem.nodeName.toLowerCase() === "button";
		},
		input: function(elem){
			return (/input|select|textarea|button/i).test(elem.nodeName);
		}
	},
	setFilters: {
		first: function(elem, i){
			return i === 0;
		},
		last: function(elem, i, match, array){
			return i === array.length - 1;
		},
		even: function(elem, i){
			return i % 2 === 0;
		},
		odd: function(elem, i){
			return i % 2 === 1;
		},
		lt: function(elem, i, match){
			return i < match[3] - 0;
		},
		gt: function(elem, i, match){
			return i > match[3] - 0;
		},
		nth: function(elem, i, match){
			return match[3] - 0 === i;
		},
		eq: function(elem, i, match){
			return match[3] - 0 === i;
		}
	},
	filter: {
		PSEUDO: function(elem, match, i, array){
			var name = match[1], filter = Expr.filters[ name ];

			if ( filter ) {
				return filter( elem, i, match, array );
			} else if ( name === "contains" ) {
				return (elem.textContent || elem.innerText || Sizzle.getText([ elem ]) || "").indexOf(match[3]) >= 0;
			} else if ( name === "not" ) {
				var not = match[3];

				for ( var j = 0, l = not.length; j < l; j++ ) {
					if ( not[j] === elem ) {
						return false;
					}
				}

				return true;
			} else {
				Sizzle.error( "Syntax error, unrecognized expression: " + name );
			}
		},
		CHILD: function(elem, match){
			var type = match[1], node = elem;
			switch (type) {
				case 'only':
				case 'first':
					while ( (node = node.previousSibling) )	 {
						if ( node.nodeType === 1 ) { 
							return false; 
						}
					}
					if ( type === "first" ) { 
						return true; 
					}
					node = elem;
				case 'last':
					while ( (node = node.nextSibling) )	 {
						if ( node.nodeType === 1 ) { 
							return false; 
						}
					}
					return true;
				case 'nth':
					var first = match[2], last = match[3];

					if ( first === 1 && last === 0 ) {
						return true;
					}
					
					var doneName = match[0],
						parent = elem.parentNode;
	
					if ( parent && (parent.sizcache !== doneName || !elem.nodeIndex) ) {
						var count = 0;
						for ( node = parent.firstChild; node; node = node.nextSibling ) {
							if ( node.nodeType === 1 ) {
								node.nodeIndex = ++count;
							}
						} 
						parent.sizcache = doneName;
					}
					
					var diff = elem.nodeIndex - last;
					if ( first === 0 ) {
						return diff === 0;
					} else {
						return ( diff % first === 0 && diff / first >= 0 );
					}
			}
		},
		ID: function(elem, match){
			return elem.nodeType === 1 && elem.getAttribute("id") === match;
		},
		TAG: function(elem, match){
			return (match === "*" && elem.nodeType === 1) || elem.nodeName.toLowerCase() === match;
		},
		CLASS: function(elem, match){
			return (" " + (elem.className || elem.getAttribute("class")) + " ")
				.indexOf( match ) > -1;
		},
		ATTR: function(elem, match){
			var name = match[1],
				result = Expr.attrHandle[ name ] ?
					Expr.attrHandle[ name ]( elem ) :
					elem[ name ] != null ?
						elem[ name ] :
						elem.getAttribute( name ),
				value = result + "",
				type = match[2],
				check = match[4];

			return result == null ?
				type === "!=" :
				type === "=" ?
				value === check :
				type === "*=" ?
				value.indexOf(check) >= 0 :
				type === "~=" ?
				(" " + value + " ").indexOf(check) >= 0 :
				!check ?
				value && result !== false :
				type === "!=" ?
				value !== check :
				type === "^=" ?
				value.indexOf(check) === 0 :
				type === "$=" ?
				value.substr(value.length - check.length) === check :
				type === "|=" ?
				value === check || value.substr(0, check.length + 1) === check + "-" :
				false;
		},
		POS: function(elem, match, i, array){
			var name = match[2], filter = Expr.setFilters[ name ];

			if ( filter ) {
				return filter( elem, i, match, array );
			}
		}
	}
};

var origPOS = Expr.match.POS,
	fescape = function(all, num){
		return "\\" + (num - 0 + 1);
	};

for ( var type in Expr.match ) {
	Expr.match[ type ] = new RegExp( Expr.match[ type ].source + (/(?![^\[]*\])(?![^\(]*\))/.source) );
	Expr.leftMatch[ type ] = new RegExp( /(^(?:.|\r|\n)*?)/.source + Expr.match[ type ].source.replace(/\\(\d+)/g, fescape) );
}

var makeArray = function(array, results) {
	array = Array.prototype.slice.call( array, 0 );

	if ( results ) {
		results.push.apply( results, array );
		return results;
	}
	
	return array;
};

// Perform a simple check to determine if the browser is capable of
// converting a NodeList to an array using builtin methods.
// Also verifies that the returned array holds DOM nodes
// (which is not the case in the Blackberry browser)
try {
	Array.prototype.slice.call( document.documentElement.childNodes, 0 )[0].nodeType;

// Provide a fallback method if it does not work
} catch(e){
	makeArray = function(array, results) {
		var ret = results || [], i = 0;

		if ( toString.call(array) === "[object Array]" ) {
			Array.prototype.push.apply( ret, array );
		} else {
			if ( typeof array.length === "number" ) {
				for ( var l = array.length; i < l; i++ ) {
					ret.push( array[i] );
				}
			} else {
				for ( ; array[i]; i++ ) {
					ret.push( array[i] );
				}
			}
		}

		return ret;
	};
}

var sortOrder;

if ( document.documentElement.compareDocumentPosition ) {
	sortOrder = function( a, b ) {
		if ( !a.compareDocumentPosition || !b.compareDocumentPosition ) {
			if ( a == b ) {
				hasDuplicate = true;
			}
			return a.compareDocumentPosition ? -1 : 1;
		}

		var ret = a.compareDocumentPosition(b) & 4 ? -1 : a === b ? 0 : 1;
		if ( ret === 0 ) {
			hasDuplicate = true;
		}
		return ret;
	};
} else if ( "sourceIndex" in document.documentElement ) {
	sortOrder = function( a, b ) {
		if ( !a.sourceIndex || !b.sourceIndex ) {
			if ( a == b ) {
				hasDuplicate = true;
			}
			return a.sourceIndex ? -1 : 1;
		}

		var ret = a.sourceIndex - b.sourceIndex;
		if ( ret === 0 ) {
			hasDuplicate = true;
		}
		return ret;
	};
} else if ( document.createRange ) {
	sortOrder = function( a, b ) {
		if ( !a.ownerDocument || !b.ownerDocument ) {
			if ( a == b ) {
				hasDuplicate = true;
			}
			return a.ownerDocument ? -1 : 1;
		}

		var aRange = a.ownerDocument.createRange(), bRange = b.ownerDocument.createRange();
		aRange.setStart(a, 0);
		aRange.setEnd(a, 0);
		bRange.setStart(b, 0);
		bRange.setEnd(b, 0);
		var ret = aRange.compareBoundaryPoints(Range.START_TO_END, bRange);
		if ( ret === 0 ) {
			hasDuplicate = true;
		}
		return ret;
	};
}

// Utility function for retreiving the text value of an array of DOM nodes
Sizzle.getText = function( elems ) {
	var ret = "", elem;

	for ( var i = 0; elems[i]; i++ ) {
		elem = elems[i];

		// Get the text from text nodes and CDATA nodes
		if ( elem.nodeType === 3 || elem.nodeType === 4 ) {
			ret += elem.nodeValue;

		// Traverse everything else, except comment nodes
		} else if ( elem.nodeType !== 8 ) {
			ret += Sizzle.getText( elem.childNodes );
		}
	}

	return ret;
};

// Check to see if the browser returns elements by name when
// querying by getElementById (and provide a workaround)
(function(){
	// We're going to inject a fake input element with a specified name
	var form = document.createElement("div"),
		id = "script" + (new Date()).getTime();
	form.innerHTML = "<a name='" + id + "'/>";

	// Inject it into the root element, check its status, and remove it quickly
	var root = document.documentElement;
	root.insertBefore( form, root.firstChild );

	// The workaround has to do additional checks after a getElementById
	// Which slows things down for other browsers (hence the branching)
	if ( document.getElementById( id ) ) {
		Expr.find.ID = function(match, context, isXML){
			if ( typeof context.getElementById !== "undefined" && !isXML ) {
				var m = context.getElementById(match[1]);
				return m ? m.id === match[1] || typeof m.getAttributeNode !== "undefined" && m.getAttributeNode("id").nodeValue === match[1] ? [m] : undefined : [];
			}
		};

		Expr.filter.ID = function(elem, match){
			var node = typeof elem.getAttributeNode !== "undefined" && elem.getAttributeNode("id");
			return elem.nodeType === 1 && node && node.nodeValue === match;
		};
	}

	root.removeChild( form );
	root = form = null; // release memory in IE
})();

(function(){
	// Check to see if the browser returns only elements
	// when doing getElementsByTagName("*")

	// Create a fake element
	var div = document.createElement("div");
	div.appendChild( document.createComment("") );

	// Make sure no comments are found
	if ( div.getElementsByTagName("*").length > 0 ) {
		Expr.find.TAG = function(match, context){
			var results = context.getElementsByTagName(match[1]);

			// Filter out possible comments
			if ( match[1] === "*" ) {
				var tmp = [];

				for ( var i = 0; results[i]; i++ ) {
					if ( results[i].nodeType === 1 ) {
						tmp.push( results[i] );
					}
				}

				results = tmp;
			}

			return results;
		};
	}

	// Check to see if an attribute returns normalized href attributes
	div.innerHTML = "<a href='#'></a>";
	if ( div.firstChild && typeof div.firstChild.getAttribute !== "undefined" &&
			div.firstChild.getAttribute("href") !== "#" ) {
		Expr.attrHandle.href = function(elem){
			return elem.getAttribute("href", 2);
		};
	}

	div = null; // release memory in IE
})();

if ( document.querySelectorAll ) {
	(function(){
		var oldSizzle = Sizzle, div = document.createElement("div");
		div.innerHTML = "<p class='TEST'></p>";

		// Safari can't handle uppercase or unicode characters when
		// in quirks mode.
		if ( div.querySelectorAll && div.querySelectorAll(".TEST").length === 0 ) {
			return;
		}
	
		Sizzle = function(query, context, extra, seed){
			context = context || document;

			// Only use querySelectorAll on non-XML documents
			// (ID selectors don't work in non-HTML documents)
			if ( !seed && context.nodeType === 9 && !Sizzle.isXML(context) ) {
				try {
					return makeArray( context.querySelectorAll(query), extra );
				} catch(e){}
			}
		
			return oldSizzle(query, context, extra, seed);
		};

		for ( var prop in oldSizzle ) {
			Sizzle[ prop ] = oldSizzle[ prop ];
		}

		div = null; // release memory in IE
	})();
}

(function(){
	var div = document.createElement("div");

	div.innerHTML = "<div class='test e'></div><div class='test'></div>";

	// Opera can't find a second classname (in 9.6)
	// Also, make sure that getElementsByClassName actually exists
	if ( !div.getElementsByClassName || div.getElementsByClassName("e").length === 0 ) {
		return;
	}

	// Safari caches class attributes, doesn't catch changes (in 3.2)
	div.lastChild.className = "e";

	if ( div.getElementsByClassName("e").length === 1 ) {
		return;
	}
	
	Expr.order.splice(1, 0, "CLASS");
	Expr.find.CLASS = function(match, context, isXML) {
		if ( typeof context.getElementsByClassName !== "undefined" && !isXML ) {
			return context.getElementsByClassName(match[1]);
		}
	};

	div = null; // release memory in IE
})();

function dirNodeCheck( dir, cur, doneName, checkSet, nodeCheck, isXML ) {
	for ( var i = 0, l = checkSet.length; i < l; i++ ) {
		var elem = checkSet[i];
		if ( elem ) {
			elem = elem[dir];
			var match = false;

			while ( elem ) {
				if ( elem.sizcache === doneName ) {
					match = checkSet[elem.sizset];
					break;
				}

				if ( elem.nodeType === 1 && !isXML ){
					elem.sizcache = doneName;
					elem.sizset = i;
				}

				if ( elem.nodeName.toLowerCase() === cur ) {
					match = elem;
					break;
				}

				elem = elem[dir];
			}

			checkSet[i] = match;
		}
	}
}

function dirCheck( dir, cur, doneName, checkSet, nodeCheck, isXML ) {
	for ( var i = 0, l = checkSet.length; i < l; i++ ) {
		var elem = checkSet[i];
		if ( elem ) {
			elem = elem[dir];
			var match = false;

			while ( elem ) {
				if ( elem.sizcache === doneName ) {
					match = checkSet[elem.sizset];
					break;
				}

				if ( elem.nodeType === 1 ) {
					if ( !isXML ) {
						elem.sizcache = doneName;
						elem.sizset = i;
					}
					if ( typeof cur !== "string" ) {
						if ( elem === cur ) {
							match = true;
							break;
						}

					} else if ( Sizzle.filter( cur, [elem] ).length > 0 ) {
						match = elem;
						break;
					}
				}

				elem = elem[dir];
			}

			checkSet[i] = match;
		}
	}
}

Sizzle.contains = document.compareDocumentPosition ? function(a, b){
	return !!(a.compareDocumentPosition(b) & 16);
} : function(a, b){
	return a !== b && (a.contains ? a.contains(b) : true);
};

Sizzle.isXML = function(elem){
	// documentElement is verified for cases where it doesn't yet exist
	// (such as loading iframes in IE - #4833) 
	var documentElement = (elem ? elem.ownerDocument || elem : 0).documentElement;
	return documentElement ? documentElement.nodeName !== "HTML" : false;
};

var posProcess = function(selector, context){
	var tmpSet = [], later = "", match,
		root = context.nodeType ? [context] : context;

	// Position selectors must be done after the filter
	// And so must :not(positional) so we move all PSEUDOs to the end
	while ( (match = Expr.match.PSEUDO.exec( selector )) ) {
		later += match[0];
		selector = selector.replace( Expr.match.PSEUDO, "" );
	}

	selector = Expr.relative[selector] ? selector + "*" : selector;

	for ( var i = 0, l = root.length; i < l; i++ ) {
		Sizzle( selector, root[i], tmpSet );
	}

	return Sizzle.filter( later, tmpSet );
};

// EXPOSE

window.tinymce.dom.Sizzle = Sizzle;

})();


(function(tinymce) {
	// Shorten names
	var each = tinymce.each, DOM = tinymce.DOM, isIE = tinymce.isIE, isWebKit = tinymce.isWebKit, Event;

	tinymce.create('tinymce.dom.EventUtils', {
		EventUtils : function() {
			this.inits = [];
			this.events = [];
		},

		add : function(o, n, f, s) {
			var cb, t = this, el = t.events, r;

			if (n instanceof Array) {
				r = [];

				each(n, function(n) {
					r.push(t.add(o, n, f, s));
				});

				return r;
			}

			// Handle array
			if (o && o.hasOwnProperty && o instanceof Array) {
				r = [];

				each(o, function(o) {
					o = DOM.get(o);
					r.push(t.add(o, n, f, s));
				});

				return r;
			}

			o = DOM.get(o);

			if (!o)
				return;

			// Setup event callback
			cb = function(e) {
				// Is all events disabled
				if (t.disabled)
					return;

				e = e || window.event;

				// Patch in target, preventDefault and stopPropagation in IE it's W3C valid
				if (e && isIE) {
					if (!e.target)
						e.target = e.srcElement;

					// Patch in preventDefault, stopPropagation methods for W3C compatibility
					tinymce.extend(e, t._stoppers);
				}

				if (!s)
					return f(e);

				return f.call(s, e);
			};

			if (n == 'unload') {
				tinymce.unloads.unshift({func : cb});
				return cb;
			}

			if (n == 'init') {
				if (t.domLoaded)
					cb();
				else
					t.inits.push(cb);

				return cb;
			}

			// Store away listener reference
			el.push({
				obj : o,
				name : n,
				func : f,
				cfunc : cb,
				scope : s
			});

			t._add(o, n, cb);

			return f;
		},

		remove : function(o, n, f) {
			var t = this, a = t.events, s = false, r;

			// Handle array
			if (o && o.hasOwnProperty && o instanceof Array) {
				r = [];

				each(o, function(o) {
					o = DOM.get(o);
					r.push(t.remove(o, n, f));
				});

				return r;
			}

			o = DOM.get(o);

			each(a, function(e, i) {
				if (e.obj == o && e.name == n && (!f || (e.func == f || e.cfunc == f))) {
					a.splice(i, 1);
					t._remove(o, n, e.cfunc);
					s = true;
					return false;
				}
			});

			return s;
		},

		clear : function(o) {
			var t = this, a = t.events, i, e;

			if (o) {
				o = DOM.get(o);

				for (i = a.length - 1; i >= 0; i--) {
					e = a[i];

					if (e.obj === o) {
						t._remove(e.obj, e.name, e.cfunc);
						e.obj = e.cfunc = null;
						a.splice(i, 1);
					}
				}
			}
		},

		cancel : function(e) {
			if (!e)
				return false;

			this.stop(e);

			return this.prevent(e);
		},

		stop : function(e) {
			if (e.stopPropagation)
				e.stopPropagation();
			else
				e.cancelBubble = true;

			return false;
		},

		prevent : function(e) {
			if (e.preventDefault)
				e.preventDefault();
			else
				e.returnValue = false;

			return false;
		},

		destroy : function() {
			var t = this;

			each(t.events, function(e, i) {
				t._remove(e.obj, e.name, e.cfunc);
				e.obj = e.cfunc = null;
			});

			t.events = [];
			t = null;
		},

		_add : function(o, n, f) {
			if (o.attachEvent)
				o.attachEvent('on' + n, f);
			else if (o.addEventListener)
				o.addEventListener(n, f, false);
			else
				o['on' + n] = f;
		},

		_remove : function(o, n, f) {
			if (o) {
				try {
					if (o.detachEvent)
						o.detachEvent('on' + n, f);
					else if (o.removeEventListener)
						o.removeEventListener(n, f, false);
					else
						o['on' + n] = null;
				} catch (ex) {
					// Might fail with permission denined on IE so we just ignore that
				}
			}
		},

		_pageInit : function(win) {
			var t = this;

			// Keep it from running more than once
			if (t.domLoaded)
				return;

			t.domLoaded = true;

			each(t.inits, function(c) {
				c();
			});

			t.inits = [];
		},

		_wait : function(win) {
			var t = this, doc = win.document;

			// No need since the document is already loaded
			if (win.tinyMCE_GZ && tinyMCE_GZ.loaded) {
				t.domLoaded = 1;
				return;
			}

			// Use IE method
			if (doc.attachEvent) {
				doc.attachEvent("onreadystatechange", function() {
					if (doc.readyState === "complete") {
						doc.detachEvent("onreadystatechange", arguments.callee);
						t._pageInit(win);
					}
				});

				if (doc.documentElement.doScroll && win == win.top) {
					(function() {
						if (t.domLoaded)
							return;

						try {
							// If IE is used, use the trick by Diego Perini licensed under MIT by request to the author.
							// http://javascript.nwbox.com/IEContentLoaded/
							doc.documentElement.doScroll("left");
						} catch (ex) {
							setTimeout(arguments.callee, 0);
							return;
						}

						t._pageInit(win);
					})();
				}
			} else if (doc.addEventListener) {
				t._add(win, 'DOMContentLoaded', function() {
					t._pageInit(win);
				});
			}

			t._add(win, 'load', function() {
				t._pageInit(win);
			});
		},

		_stoppers : {
			preventDefault : function() {
				this.returnValue = false;
			},

			stopPropagation : function() {
				this.cancelBubble = true;
			}
		}
	});

	Event = tinymce.dom.Event = new tinymce.dom.EventUtils();

	// Dispatch DOM content loaded event for IE and Safari
	Event._wait(window);

	tinymce.addUnload(function() {
		Event.destroy();
	});
})(tinymce);

(function(tinymce) {
	tinymce.dom.Element = function(id, settings) {
		var t = this, dom, el;

		t.settings = settings = settings || {};
		t.id = id;
		t.dom = dom = settings.dom || tinymce.DOM;

		// Only IE leaks DOM references, this is a lot faster
		if (!tinymce.isIE)
			el = dom.get(t.id);

		tinymce.each(
				('getPos,getRect,getParent,add,setStyle,getStyle,setStyles,' + 
				'setAttrib,setAttribs,getAttrib,addClass,removeClass,' + 
				'hasClass,getOuterHTML,setOuterHTML,remove,show,hide,' + 
				'isHidden,setHTML,get').split(/,/)
			, function(k) {
				t[k] = function() {
					var a = [id], i;

					for (i = 0; i < arguments.length; i++)
						a.push(arguments[i]);

					a = dom[k].apply(dom, a);
					t.update(k);

					return a;
				};
		});

		tinymce.extend(t, {
			on : function(n, f, s) {
				return tinymce.dom.Event.add(t.id, n, f, s);
			},

			getXY : function() {
				return {
					x : parseInt(t.getStyle('left')),
					y : parseInt(t.getStyle('top'))
				};
			},

			getSize : function() {
				var n = dom.get(t.id);

				return {
					w : parseInt(t.getStyle('width') || n.clientWidth),
					h : parseInt(t.getStyle('height') || n.clientHeight)
				};
			},

			moveTo : function(x, y) {
				t.setStyles({left : x, top : y});
			},

			moveBy : function(x, y) {
				var p = t.getXY();

				t.moveTo(p.x + x, p.y + y);
			},

			resizeTo : function(w, h) {
				t.setStyles({width : w, height : h});
			},

			resizeBy : function(w, h) {
				var s = t.getSize();

				t.resizeTo(s.w + w, s.h + h);
			},

			update : function(k) {
				var b;

				if (tinymce.isIE6 && settings.blocker) {
					k = k || '';

					// Ignore getters
					if (k.indexOf('get') === 0 || k.indexOf('has') === 0 || k.indexOf('is') === 0)
						return;

					// Remove blocker on remove
					if (k == 'remove') {
						dom.remove(t.blocker);
						return;
					}

					if (!t.blocker) {
						t.blocker = dom.uniqueId();
						b = dom.add(settings.container || dom.getRoot(), 'iframe', {id : t.blocker, style : 'position:absolute;', frameBorder : 0, src : 'javascript:""'});
						dom.setStyle(b, 'opacity', 0);
					} else
						b = dom.get(t.blocker);

					dom.setStyles(b, {
						left : t.getStyle('left', 1),
						top : t.getStyle('top', 1),
						width : t.getStyle('width', 1),
						height : t.getStyle('height', 1),
						display : t.getStyle('display', 1),
						zIndex : parseInt(t.getStyle('zIndex', 1) || 0) - 1
					});
				}
			}
		});
	};
})(tinymce);

(function(tinymce) {
	function trimNl(s) {
		return s.replace(/[\n\r]+/g, '');
	};

	// Shorten names
	var is = tinymce.is, isIE = tinymce.isIE, each = tinymce.each;

	tinymce.create('tinymce.dom.Selection', {
		Selection : function(dom, win, serializer) {
			var t = this;

			t.dom = dom;
			t.win = win;
			t.serializer = serializer;

			// Add events
			each([
				'onBeforeSetContent',

				'onBeforeGetContent',

				'onSetContent',

				'onGetContent'
			], function(e) {
				t[e] = new tinymce.util.Dispatcher(t);
			});

			// No W3C Range support
			if (!t.win.getSelection)
				t.tridentSel = new tinymce.dom.TridentSelection(t);

			if (tinymce.isIE && dom.boxModel)
				this._fixIESelection();

			// Prevent leaks
			tinymce.addUnload(t.destroy, t);
		},

		setCursorLocation: function(node, offset) {
			var t = this; var r = t.dom.createRng();
			r.setStart(node, offset);
			r.setEnd(node, offset);
			t.setRng(r);
			t.collapse(false);
		},
		getContent : function(s) {
			var t = this, r = t.getRng(), e = t.dom.create("body"), se = t.getSel(), wb, wa, n;

			s = s || {};
			wb = wa = '';
			s.get = true;
			s.format = s.format || 'html';
			s.forced_root_block = '';
			t.onBeforeGetContent.dispatch(t, s);

			if (s.format == 'text')
				return t.isCollapsed() ? '' : (r.text || (se.toString ? se.toString() : ''));

			if (r.cloneContents) {
				n = r.cloneContents();

				if (n)
					e.appendChild(n);
			} else if (is(r.item) || is(r.htmlText)) {
				// IE will produce invalid markup if elements are present that
				// it doesn't understand like custom elements or HTML5 elements.
				// Adding a BR in front of the contents and then remoiving it seems to fix it though.
				e.innerHTML = '<br>' + (r.item ? r.item(0).outerHTML : r.htmlText);
				e.removeChild(e.firstChild);
			} else
				e.innerHTML = r.toString();

			// Keep whitespace before and after
			if (/^\s/.test(e.innerHTML))
				wb = ' ';

			if (/\s+$/.test(e.innerHTML))
				wa = ' ';

			s.getInner = true;

			s.content = t.isCollapsed() ? '' : wb + t.serializer.serialize(e, s) + wa;
			t.onGetContent.dispatch(t, s);

			return s.content;
		},

		setContent : function(content, args) {
			var self = this, rng = self.getRng(), caretNode, doc = self.win.document, frag, temp;

			args = args || {format : 'html'};
			args.set = true;
			content = args.content = content;

			// Dispatch before set content event
			if (!args.no_events)
				self.onBeforeSetContent.dispatch(self, args);

			content = args.content;

			if (rng.insertNode) {
				// Make caret marker since insertNode places the caret in the beginning of text after insert
				content += '<span id="__caret">_</span>';

				// Delete and insert new node
				if (rng.startContainer == doc && rng.endContainer == doc) {
					// WebKit will fail if the body is empty since the range is then invalid and it can't insert contents
					doc.body.innerHTML = content;
				} else {
					rng.deleteContents();

					if (doc.body.childNodes.length == 0) {
						doc.body.innerHTML = content;
					} else {
						// createContextualFragment doesn't exists in IE 9 DOMRanges
						if (rng.createContextualFragment) {
							rng.insertNode(rng.createContextualFragment(content));
						} else {
							// Fake createContextualFragment call in IE 9
							frag = doc.createDocumentFragment();
							temp = doc.createElement('div');

							frag.appendChild(temp);
							temp.outerHTML = content;

							rng.insertNode(frag);
						}
					}
				}

				// Move to caret marker
				caretNode = self.dom.get('__caret');

				// Make sure we wrap it compleatly, Opera fails with a simple select call
				rng = doc.createRange();
				rng.setStartBefore(caretNode);
				rng.setEndBefore(caretNode);
				self.setRng(rng);

				// Remove the caret position
				self.dom.remove('__caret');

				try {
					self.setRng(rng);
				} catch (ex) {
					// Might fail on Opera for some odd reason
				}
			} else {
				if (rng.item) {
					// Delete content and get caret text selection
					doc.execCommand('Delete', false, null);
					rng = self.getRng();
				}

				// Explorer removes spaces from the beginning of pasted contents
				if (/^\s+/.test(content)) {
					rng.pasteHTML('<span id="__mce_tmp">_</span>' + content);
					self.dom.remove('__mce_tmp');
				} else
					rng.pasteHTML(content);
			}

			// Dispatch set content event
			if (!args.no_events)
				self.onSetContent.dispatch(self, args);
		},

		getStart : function() {
			var rng = this.getRng(), startElement, parentElement, checkRng, node;

			if (rng.duplicate || rng.item) {
				// Control selection, return first item
				if (rng.item)
					return rng.item(0);

				// Get start element
				checkRng = rng.duplicate();
				checkRng.collapse(1);
				startElement = checkRng.parentElement();

				// Check if range parent is inside the start element, then return the inner parent element
				// This will fix issues when a single element is selected, IE would otherwise return the wrong start element
				parentElement = node = rng.parentElement();
				while (node = node.parentNode) {
					if (node == startElement) {
						startElement = parentElement;
						break;
					}
				}

				return startElement;
			} else {
				startElement = rng.startContainer;

				if (startElement.nodeType == 1 && startElement.hasChildNodes())
					startElement = startElement.childNodes[Math.min(startElement.childNodes.length - 1, rng.startOffset)];

				if (startElement && startElement.nodeType == 3)
					return startElement.parentNode;

				return startElement;
			}
		},

		getEnd : function() {
			var t = this, r = t.getRng(), e, eo;

			if (r.duplicate || r.item) {
				if (r.item)
					return r.item(0);

				r = r.duplicate();
				r.collapse(0);
				e = r.parentElement();

				if (e && e.nodeName == 'BODY')
					return e.lastChild || e;

				return e;
			} else {
				e = r.endContainer;
				eo = r.endOffset;

				if (e.nodeType == 1 && e.hasChildNodes())
					e = e.childNodes[eo > 0 ? eo - 1 : eo];

				if (e && e.nodeType == 3)
					return e.parentNode;

				return e;
			}
		},

		getBookmark : function(type, normalized) {
			var t = this, dom = t.dom, rng, rng2, id, collapsed, name, element, index, chr = '\uFEFF', styles;

			function findIndex(name, element) {
				var index = 0;

				each(dom.select(name), function(node, i) {
					if (node == element)
						index = i;
				});

				return index;
			};

			if (type == 2) {
				function getLocation() {
					var rng = t.getRng(true), root = dom.getRoot(), bookmark = {};

					function getPoint(rng, start) {
						var container = rng[start ? 'startContainer' : 'endContainer'],
							offset = rng[start ? 'startOffset' : 'endOffset'], point = [], node, childNodes, after = 0;

						if (container.nodeType == 3) {
							if (normalized) {
								for (node = container.previousSibling; node && node.nodeType == 3; node = node.previousSibling)
									offset += node.nodeValue.length;
							}

							point.push(offset);
						} else {
							childNodes = container.childNodes;

							if (offset >= childNodes.length && childNodes.length) {
								after = 1;
								offset = Math.max(0, childNodes.length - 1);
							}

							point.push(t.dom.nodeIndex(childNodes[offset], normalized) + after);
						}

						for (; container && container != root; container = container.parentNode)
							point.push(t.dom.nodeIndex(container, normalized));

						return point;
					};

					bookmark.start = getPoint(rng, true);

					if (!t.isCollapsed())
						bookmark.end = getPoint(rng);

					return bookmark;
				};

				if (t.tridentSel)
					return t.tridentSel.getBookmark(type);

				return getLocation();
			}

			// Handle simple range
			if (type)
				return {rng : t.getRng()};

			rng = t.getRng();
			id = dom.uniqueId();
			collapsed = tinyMCE.activeEditor.selection.isCollapsed();
			styles = 'overflow:hidden;line-height:0px';

			// Explorer method
			if (rng.duplicate || rng.item) {
				// Text selection
				if (!rng.item) {
					rng2 = rng.duplicate();

					try {
						// Insert start marker
						rng.collapse();
						rng.pasteHTML('<span data-mce-type="bookmark" id="' + id + '_start" style="' + styles + '">' + chr + '</span>');

						// Insert end marker
						if (!collapsed) {
							rng2.collapse(false);

							// Detect the empty space after block elements in IE and move the end back one character <p></p>] becomes <p>]</p>
							rng.moveToElementText(rng2.parentElement());
							if (rng.compareEndPoints('StartToEnd', rng2) == 0)
								rng2.move('character', -1);

							rng2.pasteHTML('<span data-mce-type="bookmark" id="' + id + '_end" style="' + styles + '">' + chr + '</span>');
						}
					} catch (ex) {
						// IE might throw unspecified error so lets ignore it
						return null;
					}
				} else {
					// Control selection
					element = rng.item(0);
					name = element.nodeName;

					return {name : name, index : findIndex(name, element)};
				}
			} else {
				element = t.getNode();
				name = element.nodeName;
				if (name == 'IMG')
					return {name : name, index : findIndex(name, element)};

				// W3C method
				rng2 = rng.cloneRange();

				// Insert end marker
				if (!collapsed) {
					rng2.collapse(false);
					rng2.insertNode(dom.create('span', {'data-mce-type' : "bookmark", id : id + '_end', style : styles}, chr));
				}

				rng.collapse(true);
				rng.insertNode(dom.create('span', {'data-mce-type' : "bookmark", id : id + '_start', style : styles}, chr));
			}

			t.moveToBookmark({id : id, keep : 1});

			return {id : id};
		},

		moveToBookmark : function(bookmark) {
			var t = this, dom = t.dom, marker1, marker2, rng, root, startContainer, endContainer, startOffset, endOffset;

			if (bookmark) {
				if (bookmark.start) {
					rng = dom.createRng();
					root = dom.getRoot();

					function setEndPoint(start) {
						var point = bookmark[start ? 'start' : 'end'], i, node, offset, children;

						if (point) {
							offset = point[0];

							// Find container node
							for (node = root, i = point.length - 1; i >= 1; i--) {
								children = node.childNodes;

								if (point[i] > children.length - 1)
									return;

								node = children[point[i]];
							}

							// Move text offset to best suitable location
							if (node.nodeType === 3)
								offset = Math.min(point[0], node.nodeValue.length);

							// Move element offset to best suitable location
							if (node.nodeType === 1)
								offset = Math.min(point[0], node.childNodes.length);

							// Set offset within container node
							if (start)
								rng.setStart(node, offset);
							else
								rng.setEnd(node, offset);
						}

						return true;
					};

					if (t.tridentSel)
						return t.tridentSel.moveToBookmark(bookmark);

					if (setEndPoint(true) && setEndPoint()) {
						t.setRng(rng);
					}
				} else if (bookmark.id) {
					function restoreEndPoint(suffix) {
						var marker = dom.get(bookmark.id + '_' + suffix), node, idx, next, prev, keep = bookmark.keep;

						if (marker) {
							node = marker.parentNode;

							if (suffix == 'start') {
								if (!keep) {
									idx = dom.nodeIndex(marker);
								} else {
									node = marker.firstChild;
									idx = 1;
								}

								startContainer = endContainer = node;
								startOffset = endOffset = idx;
							} else {
								if (!keep) {
									idx = dom.nodeIndex(marker);
								} else {
									node = marker.firstChild;
									idx = 1;
								}

								endContainer = node;
								endOffset = idx;
							}

							if (!keep) {
								prev = marker.previousSibling;
								next = marker.nextSibling;

								// Remove all marker text nodes
								each(tinymce.grep(marker.childNodes), function(node) {
									if (node.nodeType == 3)
										node.nodeValue = node.nodeValue.replace(/\uFEFF/g, '');
								});

								// Remove marker but keep children if for example contents where inserted into the marker
								// Also remove duplicated instances of the marker for example by a split operation or by WebKit auto split on paste feature
								while (marker = dom.get(bookmark.id + '_' + suffix))
									dom.remove(marker, 1);

								// If siblings are text nodes then merge them unless it's Opera since it some how removes the node
								// and we are sniffing since adding a lot of detection code for a browser with 3% of the market isn't worth the effort. Sorry, Opera but it's just a fact
								if (prev && next && prev.nodeType == next.nodeType && prev.nodeType == 3 && !tinymce.isOpera) {
									idx = prev.nodeValue.length;
									prev.appendData(next.nodeValue);
									dom.remove(next);

									if (suffix == 'start') {
										startContainer = endContainer = prev;
										startOffset = endOffset = idx;
									} else {
										endContainer = prev;
										endOffset = idx;
									}
								}
							}
						}
					};

					function addBogus(node) {
						// Adds a bogus BR element for empty block elements or just a space on IE since it renders BR elements incorrectly
						if (dom.isBlock(node) && !node.innerHTML)
							node.innerHTML = !isIE ? '<br data-mce-bogus="1" />' : ' ';

						return node;
					};

					// Restore start/end points
					restoreEndPoint('start');
					restoreEndPoint('end');

					if (startContainer) {
						rng = dom.createRng();
						rng.setStart(addBogus(startContainer), startOffset);
						rng.setEnd(addBogus(endContainer), endOffset);
						t.setRng(rng);
					}
				} else if (bookmark.name) {
					t.select(dom.select(bookmark.name)[bookmark.index]);
				} else if (bookmark.rng)
					t.setRng(bookmark.rng);
			}
		},

		select : function(node, content) {
			var t = this, dom = t.dom, rng = dom.createRng(), idx;

			if (node) {
				idx = dom.nodeIndex(node);
				rng.setStart(node.parentNode, idx);
				rng.setEnd(node.parentNode, idx + 1);

				// Find first/last text node or BR element
				if (content) {
					function setPoint(node, start) {
						var walker = new tinymce.dom.TreeWalker(node, node);

						do {
							// Text node
							if (node.nodeType == 3 && tinymce.trim(node.nodeValue).length != 0) {
								if (start)
									rng.setStart(node, 0);
								else
									rng.setEnd(node, node.nodeValue.length);

								return;
							}

							// BR element
							if (node.nodeName == 'BR') {
								if (start)
									rng.setStartBefore(node);
								else
									rng.setEndBefore(node);

								return;
							}
						} while (node = (start ? walker.next() : walker.prev()));
					};

					setPoint(node, 1);
					setPoint(node);
				}

				t.setRng(rng);
			}

			return node;
		},

		isCollapsed : function() {
			var t = this, r = t.getRng(), s = t.getSel();

			if (!r || r.item)
				return false;

			if (r.compareEndPoints)
				return r.compareEndPoints('StartToEnd', r) === 0;

			return !s || r.collapsed;
		},

		collapse : function(to_start) {
			var self = this, rng = self.getRng(), node;

			// Control range on IE
			if (rng.item) {
				node = rng.item(0);
				rng = self.win.document.body.createTextRange();
				rng.moveToElementText(node);
			}

			rng.collapse(!!to_start);
			self.setRng(rng);
		},

		getSel : function() {
			var t = this, w = this.win;

			return w.getSelection ? w.getSelection() : w.document.selection;
		},

		getRng : function(w3c) {
			var t = this, s, r, elm, doc = t.win.document;

			// Found tridentSel object then we need to use that one
			if (w3c && t.tridentSel)
				return t.tridentSel.getRangeAt(0);

			try {
				if (s = t.getSel())
					r = s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : doc.createRange());
			} catch (ex) {
				// IE throws unspecified error here if TinyMCE is placed in a frame/iframe
			}

			// We have W3C ranges and it's IE then fake control selection since IE9 doesn't handle that correctly yet
			if (tinymce.isIE && r && r.setStart && doc.selection.createRange().item) {
				elm = doc.selection.createRange().item(0);
				r = doc.createRange();
				r.setStartBefore(elm);
				r.setEndAfter(elm);
			}

			// No range found then create an empty one
			// This can occur when the editor is placed in a hidden container element on Gecko
			// Or on IE when there was an exception
			if (!r)
				r = doc.createRange ? doc.createRange() : doc.body.createTextRange();

			if (t.selectedRange && t.explicitRange) {
				if (r.compareBoundaryPoints(r.START_TO_START, t.selectedRange) === 0 && r.compareBoundaryPoints(r.END_TO_END, t.selectedRange) === 0) {
					// Safari, Opera and Chrome only ever select text which causes the range to change.
					// This lets us use the originally set range if the selection hasn't been changed by the user.
					r = t.explicitRange;
				} else {
					t.selectedRange = null;
					t.explicitRange = null;
				}
			}

			return r;
		},

		setRng : function(r) {
			var s, t = this;
			
			if (!t.tridentSel) {
				s = t.getSel();

				if (s) {
					t.explicitRange = r;

					try {
						s.removeAllRanges();
					} catch (ex) {
						// IE9 might throw errors here don't know why
					}

					s.addRange(r);
					t.selectedRange = s.getRangeAt(0);
				}
			} else {
				// Is W3C Range
				if (r.cloneRange) {
					t.tridentSel.addRange(r);
					return;
				}

				// Is IE specific range
				try {
					r.select();
				} catch (ex) {
					// Needed for some odd IE bug #1843306
				}
			}
		},

		setNode : function(n) {
			var t = this;

			t.setContent(t.dom.getOuterHTML(n));

			return n;
		},

		getNode : function() {
			var t = this, rng = t.getRng(), sel = t.getSel(), elm, start = rng.startContainer, end = rng.endContainer;

			// Range maybe lost after the editor is made visible again
			if (!rng)
				return t.dom.getRoot();

			if (rng.setStart) {
				elm = rng.commonAncestorContainer;

				// Handle selection a image or other control like element such as anchors
				if (!rng.collapsed) {
					if (rng.startContainer == rng.endContainer) {
						if (rng.endOffset - rng.startOffset < 2) {
							if (rng.startContainer.hasChildNodes())
								elm = rng.startContainer.childNodes[rng.startOffset];
						}
					}

					// If the anchor node is a element instead of a text node then return this element
					//if (tinymce.isWebKit && sel.anchorNode && sel.anchorNode.nodeType == 1) 
					//	return sel.anchorNode.childNodes[sel.anchorOffset];

					// Handle cases where the selection is immediately wrapped around a node and return that node instead of it's parent.
					// This happens when you double click an underlined word in FireFox.
					if (start.nodeType === 3 && end.nodeType === 3) {
						function skipEmptyTextNodes(n, forwards) {
							var orig = n;
							while (n && n.nodeType === 3 && n.length === 0) {
								n = forwards ? n.nextSibling : n.previousSibling;
							}
							return n || orig;
						}
						if (start.length === rng.startOffset) {
							start = skipEmptyTextNodes(start.nextSibling, true);
						} else {
							start = start.parentNode;
						}
						if (rng.endOffset === 0) {
							end = skipEmptyTextNodes(end.previousSibling, false);
						} else {
							end = end.parentNode;
						}

						if (start && start === end)
							return start;
					}
				}

				if (elm && elm.nodeType == 3)
					return elm.parentNode;

				return elm;
			}

			return rng.item ? rng.item(0) : rng.parentElement();
		},

		getSelectedBlocks : function(st, en) {
			var t = this, dom = t.dom, sb, eb, n, bl = [];

			sb = dom.getParent(st || t.getStart(), dom.isBlock);
			eb = dom.getParent(en || t.getEnd(), dom.isBlock);

			if (sb)
				bl.push(sb);

			if (sb && eb && sb != eb) {
				n = sb;

				var walker = new tinymce.dom.TreeWalker(sb, dom.getRoot());
				while ((n = walker.next()) && n != eb) {
					if (dom.isBlock(n))
						bl.push(n);
				}
			}

			if (eb && sb != eb)
				bl.push(eb);

			return bl;
		},

		normalize : function() {
			var self = this, rng, normalized;

			// Normalize only on non IE browsers for now
			if (tinymce.isIE)
				return;

			function normalizeEndPoint(start) {
				var container, offset, walker, dom = self.dom, body = dom.getRoot(), node;

				container = rng[(start ? 'start' : 'end') + 'Container'];
				offset = rng[(start ? 'start' : 'end') + 'Offset'];

				// If the container is a document move it to the body element
				if (container.nodeType === 9) {
					container = container.body;
					offset = 0;
				}

				// If the container is body try move it into the closest text node or position
				// TODO: Add more logic here to handle element selection cases
				if (container === body) {
					// Resolve the index
					if (container.hasChildNodes()) {
						container = container.childNodes[Math.min(!start && offset > 0 ? offset - 1 : offset, container.childNodes.length - 1)];
						offset = 0;

						// Don't walk into elements that doesn't have any child nodes like a IMG
						if (container.hasChildNodes()) {
							// Walk the DOM to find a text node to place the caret at or a BR
							node = container;
							walker = new tinymce.dom.TreeWalker(container, body);
							do {
								// Found a text node use that position
								if (node.nodeType === 3) {
									offset = start ? 0 : node.nodeValue.length - 1;
									container = node;
									break;
								}

								// Found a BR element that we can place the caret before
								if (node.nodeName === 'BR') {
									offset = dom.nodeIndex(node);
									container = node.parentNode;
									break;
								}
							} while (node = (start ? walker.next() : walker.prev()));

							normalized = true;
						}
					}
				}

				// Set endpoint if it was normalized
				if (normalized)
					rng['set' + (start ? 'Start' : 'End')](container, offset);
			};

			rng = self.getRng();

			// Normalize the end points
			normalizeEndPoint(true);
			
			if (rng.collapsed)
				normalizeEndPoint();

			// Set the selection if it was normalized
			if (normalized) {
				//console.log(self.dom.dumpRng(rng));
				self.setRng(rng);
			}
		},

		destroy : function(s) {
			var t = this;

			t.win = null;

			// Manual destroy then remove unload handler
			if (!s)
				tinymce.removeUnload(t.destroy);
		},

		// IE has an issue where you can't select/move the caret by clicking outside the body if the document is in standards mode
		_fixIESelection : function() {
			var dom = this.dom, doc = dom.doc, body = doc.body, started, startRng, htmlElm;

			// Make HTML element unselectable since we are going to handle selection by hand
			doc.documentElement.unselectable = true;

			// Return range from point or null if it failed
			function rngFromPoint(x, y) {
				var rng = body.createTextRange();

				try {
					rng.moveToPoint(x, y);
				} catch (ex) {
					// IE sometimes throws and exception, so lets just ignore it
					rng = null;
				}

				return rng;
			};

			// Fires while the selection is changing
			function selectionChange(e) {
				var pointRng;

				// Check if the button is down or not
				if (e.button) {
					// Create range from mouse position
					pointRng = rngFromPoint(e.x, e.y);

					if (pointRng) {
						// Check if pointRange is before/after selection then change the endPoint
						if (pointRng.compareEndPoints('StartToStart', startRng) > 0)
							pointRng.setEndPoint('StartToStart', startRng);
						else
							pointRng.setEndPoint('EndToEnd', startRng);

						pointRng.select();
					}
				} else
					endSelection();
			}

			// Removes listeners
			function endSelection() {
				var rng = doc.selection.createRange();

				// If the range is collapsed then use the last start range
				if (startRng && !rng.item && rng.compareEndPoints('StartToEnd', rng) === 0)
					startRng.select();

				dom.unbind(doc, 'mouseup', endSelection);
				dom.unbind(doc, 'mousemove', selectionChange);
				startRng = started = 0;
			};

			// Detect when user selects outside BODY
			dom.bind(doc, ['mousedown', 'contextmenu'], function(e) {
				if (e.target.nodeName === 'HTML') {
					if (started)
						endSelection();

					// Detect vertical scrollbar, since IE will fire a mousedown on the scrollbar and have target set as HTML
					htmlElm = doc.documentElement;
					if (htmlElm.scrollHeight > htmlElm.clientHeight)
						return;

					started = 1;
					// Setup start position
					startRng = rngFromPoint(e.x, e.y);
					if (startRng) {
						// Listen for selection change events
						dom.bind(doc, 'mouseup', endSelection);
						dom.bind(doc, 'mousemove', selectionChange);

						dom.win.focus();
						startRng.select();
					}
				}
			});
		}
	});
})(tinymce);

(function(tinymce) {
	tinymce.dom.Serializer = function(settings, dom, schema) {
		var onPreProcess, onPostProcess, isIE = tinymce.isIE, each = tinymce.each, htmlParser;

		// Support the old apply_source_formatting option
		if (!settings.apply_source_formatting)
			settings.indent = false;

		settings.remove_trailing_brs = true;

		// Default DOM and Schema if they are undefined
		dom = dom || tinymce.DOM;
		schema = schema || new tinymce.html.Schema(settings);
		settings.entity_encoding = settings.entity_encoding || 'named';

		onPreProcess = new tinymce.util.Dispatcher(self);

		onPostProcess = new tinymce.util.Dispatcher(self);

		htmlParser = new tinymce.html.DomParser(settings, schema);

		// Convert move data-mce-src, data-mce-href and data-mce-style into nodes or process them if needed
		htmlParser.addAttributeFilter('src,href,style', function(nodes, name) {
			var i = nodes.length, node, value, internalName = 'data-mce-' + name, urlConverter = settings.url_converter, urlConverterScope = settings.url_converter_scope, undef;

			while (i--) {
				node = nodes[i];

				value = node.attributes.map[internalName];
				if (value !== undef) {
					// Set external name to internal value and remove internal
					node.attr(name, value.length > 0 ? value : null);
					node.attr(internalName, null);
				} else {
					// No internal attribute found then convert the value we have in the DOM
					value = node.attributes.map[name];

					if (name === "style")
						value = dom.serializeStyle(dom.parseStyle(value), node.name);
					else if (urlConverter)
						value = urlConverter.call(urlConverterScope, value, name, node.name);

					node.attr(name, value.length > 0 ? value : null);
				}
			}
		});

		// Remove internal classes mceItem<..>
		htmlParser.addAttributeFilter('class', function(nodes, name) {
			var i = nodes.length, node, value;

			while (i--) {
				node = nodes[i];
				value = node.attr('class').replace(/\s*mce(Item\w+|Selected)\s*/g, '');
				node.attr('class', value.length > 0 ? value : null);
			}
		});

		// Remove bookmark elements
		htmlParser.addAttributeFilter('data-mce-type', function(nodes, name, args) {
			var i = nodes.length, node;

			while (i--) {
				node = nodes[i];

				if (node.attributes.map['data-mce-type'] === 'bookmark' && !args.cleanup)
					node.remove();
			}
		});

		// Force script into CDATA sections and remove the mce- prefix also add comments around styles
		htmlParser.addNodeFilter('script,style', function(nodes, name) {
			var i = nodes.length, node, value;

			function trim(value) {
				return value.replace(/(<!--\[CDATA\[|\]\]-->)/g, '\n')
						.replace(/^[\r\n]*|[\r\n]*$/g, '')
						.replace(/^\s*(\/\/\s*<!--|\/\/\s*<!\[CDATA\[|<!--|<!\[CDATA\[)[\r\n]*/g, '')
						.replace(/\s*(\/\/\s*\]\]>|\/\/\s*-->|\]\]>|-->|\]\]-->)\s*$/g, '');
			};

			while (i--) {
				node = nodes[i];
				value = node.firstChild ? node.firstChild.value : '';

				if (name === "script") {
					// Remove mce- prefix from script elements
					node.attr('type', (node.attr('type') || 'text/javascript').replace(/^mce\-/, ''));

					if (value.length > 0)
						node.firstChild.value = '// <![CDATA[\n' + trim(value) + '\n// ]]>';
				} else {
					if (value.length > 0)
						node.firstChild.value = '<!--\n' + trim(value) + '\n-->';
				}
			}
		});

		// Convert comments to cdata and handle protected comments
		htmlParser.addNodeFilter('#comment', function(nodes, name) {
			var i = nodes.length, node;

			while (i--) {
				node = nodes[i];

				if (node.value.indexOf('[CDATA[') === 0) {
					node.name = '#cdata';
					node.type = 4;
					node.value = node.value.replace(/^\[CDATA\[|\]\]$/g, '');
				} else if (node.value.indexOf('mce:protected ') === 0) {
					node.name = "#text";
					node.type = 3;
					node.raw = true;
					node.value = unescape(node.value).substr(14);
				}
			}
		});

		htmlParser.addNodeFilter('xml:namespace,input', function(nodes, name) {
			var i = nodes.length, node;

			while (i--) {
				node = nodes[i];
				if (node.type === 7)
					node.remove();
				else if (node.type === 1) {
					if (name === "input" && !("type" in node.attributes.map))
						node.attr('type', 'text');
				}
			}
		});

		// Fix list elements, TODO: Replace this later
		if (settings.fix_list_elements) {
			htmlParser.addNodeFilter('ul,ol', function(nodes, name) {
				var i = nodes.length, node, parentNode;

				while (i--) {
					node = nodes[i];
					parentNode = node.parent;

					if (parentNode.name === 'ul' || parentNode.name === 'ol') {
						if (node.prev && node.prev.name === 'li') {
							node.prev.append(node);
						}
					}
				}
			});
		}

		// Remove internal data attributes
		htmlParser.addAttributeFilter('data-mce-src,data-mce-href,data-mce-style', function(nodes, name) {
			var i = nodes.length;

			while (i--) {
				nodes[i].attr(name, null);
			}
		});

		// Return public methods
		return {
			schema : schema,

			addNodeFilter : htmlParser.addNodeFilter,

			addAttributeFilter : htmlParser.addAttributeFilter,

			onPreProcess : onPreProcess,

			onPostProcess : onPostProcess,

			serialize : function(node, args) {
				var impl, doc, oldDoc, htmlSerializer, content;

				// Explorer won't clone contents of script and style and the
				// selected index of select elements are cleared on a clone operation.
				if (isIE && dom.select('script,style,select,map').length > 0) {
					content = node.innerHTML;
					node = node.cloneNode(false);
					dom.setHTML(node, content);
				} else
					node = node.cloneNode(true);

				// Nodes needs to be attached to something in WebKit/Opera
				// Older builds of Opera crashes if you attach the node to an document created dynamically
				// and since we can't feature detect a crash we need to sniff the acutal build number
				// This fix will make DOM ranges and make Sizzle happy!
				impl = node.ownerDocument.implementation;
				if (impl.createHTMLDocument) {
					// Create an empty HTML document
					doc = impl.createHTMLDocument("");

					// Add the element or it's children if it's a body element to the new document
					each(node.nodeName == 'BODY' ? node.childNodes : [node], function(node) {
						doc.body.appendChild(doc.importNode(node, true));
					});

					// Grab first child or body element for serialization
					if (node.nodeName != 'BODY')
						node = doc.body.firstChild;
					else
						node = doc.body;

					// set the new document in DOMUtils so createElement etc works
					oldDoc = dom.doc;
					dom.doc = doc;
				}

				args = args || {};
				args.format = args.format || 'html';

				// Pre process
				if (!args.no_events) {
					args.node = node;
					onPreProcess.dispatch(self, args);
				}

				// Setup serializer
				htmlSerializer = new tinymce.html.Serializer(settings, schema);

				// Parse and serialize HTML
				args.content = htmlSerializer.serialize(
					htmlParser.parse(args.getInner ? node.innerHTML : tinymce.trim(dom.getOuterHTML(node), args), args)
				);

				// Replace all BOM characters for now until we can find a better solution
				if (!args.cleanup)
					args.content = args.content.replace(/\uFEFF|\u200B/g, '');

				// Post process
				if (!args.no_events)
					onPostProcess.dispatch(self, args);

				// Restore the old document if it was changed
				if (oldDoc)
					dom.doc = oldDoc;

				args.node = null;

				return args.content;
			},

			addRules : function(rules) {
				schema.addValidElements(rules);
			},

			setRules : function(rules) {
				schema.setValidElements(rules);
			}
		};
	};
})(tinymce);
(function(tinymce) {
	tinymce.dom.ScriptLoader = function(settings) {
		var QUEUED = 0,
			LOADING = 1,
			LOADED = 2,
			states = {},
			queue = [],
			scriptLoadedCallbacks = {},
			queueLoadedCallbacks = [],
			loading = 0,
			undefined;

		function loadScript(url, callback) {
			var t = this, dom = tinymce.DOM, elm, uri, loc, id;

			// Execute callback when script is loaded
			function done() {
				dom.remove(id);

				if (elm)
					elm.onreadystatechange = elm.onload = elm = null;

				callback();
			};
			
			function error() {
				// Report the error so it's easier for people to spot loading errors
				if (typeof(console) !== "undefined" && console.log)
					console.log("Failed to load: " + url);

				// We can't mark it as done if there is a load error since
				// A) We don't want to produce 404 errors on the server and
				// B) the onerror event won't fire on all browsers.
				// done();
			};

			id = dom.uniqueId();

			if (tinymce.isIE6) {
				uri = new tinymce.util.URI(url);
				loc = location;

				// If script is from same domain and we
				// use IE 6 then use XHR since it's more reliable
				if (uri.host == loc.hostname && uri.port == loc.port && (uri.protocol + ':') == loc.protocol && uri.protocol.toLowerCase() != 'file') {
					tinymce.util.XHR.send({
						url : tinymce._addVer(uri.getURI()),
						success : function(content) {
							// Create new temp script element
							var script = dom.create('script', {
								type : 'text/javascript'
							});

							// Evaluate script in global scope
							script.text = content;
							document.getElementsByTagName('head')[0].appendChild(script);
							dom.remove(script);

							done();
						},
						
						error : error
					});

					return;
				}
			}

			// Create new script element
			elm = dom.create('script', {
				id : id,
				type : 'text/javascript',
				src : tinymce._addVer(url)
			});

			// Add onload listener for non IE browsers since IE9
			// fires onload event before the script is parsed and executed
			if (!tinymce.isIE)
				elm.onload = done;

			// Add onerror event will get fired on some browsers but not all of them
			elm.onerror = error;

			// Opera 9.60 doesn't seem to fire the onreadystate event at correctly
			if (!tinymce.isOpera) {
				elm.onreadystatechange = function() {
					var state = elm.readyState;

					// Loaded state is passed on IE 6 however there
					// are known issues with this method but we can't use
					// XHR in a cross domain loading
					if (state == 'complete' || state == 'loaded')
						done();
				};
			}

			// Most browsers support this feature so we report errors
			// for those at least to help users track their missing plugins etc
			// todo: Removed since it produced error if the document is unloaded by navigating away, re-add it as an option
			/*elm.onerror = function() {
				alert('Failed to load: ' + url);
			};*/

			// Add script to document
			(document.getElementsByTagName('head')[0] || document.body).appendChild(elm);
		};

		this.isDone = function(url) {
			return states[url] == LOADED;
		};

		this.markDone = function(url) {
			states[url] = LOADED;
		};

		this.add = this.load = function(url, callback, scope) {
			var item, state = states[url];

			// Add url to load queue
			if (state == undefined) {
				queue.push(url);
				states[url] = QUEUED;
			}

			if (callback) {
				// Store away callback for later execution
				if (!scriptLoadedCallbacks[url])
					scriptLoadedCallbacks[url] = [];

				scriptLoadedCallbacks[url].push({
					func : callback,
					scope : scope || this
				});
			}
		};

		this.loadQueue = function(callback, scope) {
			this.loadScripts(queue, callback, scope);
		};

		this.loadScripts = function(scripts, callback, scope) {
			var loadScripts;

			function execScriptLoadedCallbacks(url) {
				// Execute URL callback functions
				tinymce.each(scriptLoadedCallbacks[url], function(callback) {
					callback.func.call(callback.scope);
				});

				scriptLoadedCallbacks[url] = undefined;
			};

			queueLoadedCallbacks.push({
				func : callback,
				scope : scope || this
			});

			loadScripts = function() {
				var loadingScripts = tinymce.grep(scripts);

				// Current scripts has been handled
				scripts.length = 0;

				// Load scripts that needs to be loaded
				tinymce.each(loadingScripts, function(url) {
					// Script is already loaded then execute script callbacks directly
					if (states[url] == LOADED) {
						execScriptLoadedCallbacks(url);
						return;
					}

					// Is script not loading then start loading it
					if (states[url] != LOADING) {
						states[url] = LOADING;
						loading++;

						loadScript(url, function() {
							states[url] = LOADED;
							loading--;

							execScriptLoadedCallbacks(url);

							// Load more scripts if they where added by the recently loaded script
							loadScripts();
						});
					}
				});

				// No scripts are currently loading then execute all pending queue loaded callbacks
				if (!loading) {
					tinymce.each(queueLoadedCallbacks, function(callback) {
						callback.func.call(callback.scope);
					});

					queueLoadedCallbacks.length = 0;
				}
			};

			loadScripts();
		};
	};

	// Global script loader
	tinymce.ScriptLoader = new tinymce.dom.ScriptLoader();
})(tinymce);

tinymce.dom.TreeWalker = function(start_node, root_node) {
	var node = start_node;

	function findSibling(node, start_name, sibling_name, shallow) {
		var sibling, parent;

		if (node) {
			// Walk into nodes if it has a start
			if (!shallow && node[start_name])
				return node[start_name];

			// Return the sibling if it has one
			if (node != root_node) {
				sibling = node[sibling_name];
				if (sibling)
					return sibling;

				// Walk up the parents to look for siblings
				for (parent = node.parentNode; parent && parent != root_node; parent = parent.parentNode) {
					sibling = parent[sibling_name];
					if (sibling)
						return sibling;
				}
			}
		}
	};

	this.current = function() {
		return node;
	};

	this.next = function(shallow) {
		return (node = findSibling(node, 'firstChild', 'nextSibling', shallow));
	};

	this.prev = function(shallow) {
		return (node = findSibling(node, 'lastChild', 'previousSibling', shallow));
	};
};

(function(tinymce) {
	tinymce.dom.RangeUtils = function(dom) {
		var INVISIBLE_CHAR = '\uFEFF';

		this.walk = function(rng, callback) {
			var startContainer = rng.startContainer,
				startOffset = rng.startOffset,
				endContainer = rng.endContainer,
				endOffset = rng.endOffset,
				ancestor, startPoint,
				endPoint, node, parent, siblings, nodes;

			// Handle table cell selection the table plugin enables
			// you to fake select table cells and perform formatting actions on them
			nodes = dom.select('td.mceSelected,th.mceSelected');
			if (nodes.length > 0) {
				tinymce.each(nodes, function(node) {
					callback([node]);
				});

				return;
			}

			function exclude(nodes) {
				var node;

				// First node is excluded
				node = nodes[0];
				if (node.nodeType === 3 && node === startContainer && startOffset >= node.nodeValue.length) {
					nodes.splice(0, 1);
				}

				// Last node is excluded
				node = nodes[nodes.length - 1];
				if (endOffset === 0 && nodes.length > 0 && node === endContainer && node.nodeType === 3) {
					nodes.splice(nodes.length - 1, 1);
				}

				return nodes;
			};

			function collectSiblings(node, name, end_node) {
				var siblings = [];

				for (; node && node != end_node; node = node[name])
					siblings.push(node);

				return siblings;
			};

			function findEndPoint(node, root) {
				do {
					if (node.parentNode == root)
						return node;

					node = node.parentNode;
				} while(node);
			};

			function walkBoundary(start_node, end_node, next) {
				var siblingName = next ? 'nextSibling' : 'previousSibling';

				for (node = start_node, parent = node.parentNode; node && node != end_node; node = parent) {
					parent = node.parentNode;
					siblings = collectSiblings(node == start_node ? node : node[siblingName], siblingName);

					if (siblings.length) {
						if (!next)
							siblings.reverse();

						callback(exclude(siblings));
					}
				}
			};

			// If index based start position then resolve it
			if (startContainer.nodeType == 1 && startContainer.hasChildNodes())
				startContainer = startContainer.childNodes[startOffset];

			// If index based end position then resolve it
			if (endContainer.nodeType == 1 && endContainer.hasChildNodes())
				endContainer = endContainer.childNodes[Math.min(endOffset - 1, endContainer.childNodes.length - 1)];

			// Same container
			if (startContainer == endContainer)
				return callback(exclude([startContainer]));

			// Find common ancestor and end points
			ancestor = dom.findCommonAncestor(startContainer, endContainer);
				
			// Process left side
			for (node = startContainer; node; node = node.parentNode) {
				if (node === endContainer)
					return walkBoundary(startContainer, ancestor, true);

				if (node === ancestor)
					break;
			}

			// Process right side
			for (node = endContainer; node; node = node.parentNode) {
				if (node === startContainer)
					return walkBoundary(endContainer, ancestor);

				if (node === ancestor)
					break;
			}

			// Find start/end point
			startPoint = findEndPoint(startContainer, ancestor) || startContainer;
			endPoint = findEndPoint(endContainer, ancestor) || endContainer;

			// Walk left leaf
			walkBoundary(startContainer, startPoint, true);

			// Walk the middle from start to end point
			siblings = collectSiblings(
				startPoint == startContainer ? startPoint : startPoint.nextSibling,
				'nextSibling',
				endPoint == endContainer ? endPoint.nextSibling : endPoint
			);

			if (siblings.length)
				callback(exclude(siblings));

			// Walk right leaf
			walkBoundary(endContainer, endPoint);
		};

		this.split = function(rng) {
			var startContainer = rng.startContainer,
				startOffset = rng.startOffset,
				endContainer = rng.endContainer,
				endOffset = rng.endOffset;

			function splitText(node, offset) {
				return node.splitText(offset);
			};

			// Handle single text node
			if (startContainer == endContainer && startContainer.nodeType == 3) {
				if (startOffset > 0 && startOffset < startContainer.nodeValue.length) {
					endContainer = splitText(startContainer, startOffset);
					startContainer = endContainer.previousSibling;

					if (endOffset > startOffset) {
						endOffset = endOffset - startOffset;
						startContainer = endContainer = splitText(endContainer, endOffset).previousSibling;
						endOffset = endContainer.nodeValue.length;
						startOffset = 0;
					} else {
						endOffset = 0;
					}
				}
			} else {
				// Split startContainer text node if needed
				if (startContainer.nodeType == 3 && startOffset > 0 && startOffset < startContainer.nodeValue.length) {
					startContainer = splitText(startContainer, startOffset);
					startOffset = 0;
				}

				// Split endContainer text node if needed
				if (endContainer.nodeType == 3 && endOffset > 0 && endOffset < endContainer.nodeValue.length) {
					endContainer = splitText(endContainer, endOffset).previousSibling;
					endOffset = endContainer.nodeValue.length;
				}
			}

			return {
				startContainer : startContainer,
				startOffset : startOffset,
				endContainer : endContainer,
				endOffset : endOffset
			};
		};

	};

	tinymce.dom.RangeUtils.compareRanges = function(rng1, rng2) {
		if (rng1 && rng2) {
			// Compare native IE ranges
			if (rng1.item || rng1.duplicate) {
				// Both are control ranges and the selected element matches
				if (rng1.item && rng2.item && rng1.item(0) === rng2.item(0))
					return true;

				// Both are text ranges and the range matches
				if (rng1.isEqual && rng2.isEqual && rng2.isEqual(rng1))
					return true;
			} else {
				// Compare w3c ranges
				return rng1.startContainer == rng2.startContainer && rng1.startOffset == rng2.startOffset;
			}
		}

		return false;
	};
})(tinymce);

(function(tinymce) {
	var Event = tinymce.dom.Event, each = tinymce.each;

	tinymce.create('tinymce.ui.KeyboardNavigation', {
		KeyboardNavigation: function(settings, dom) {
			var t = this, root = settings.root, items = settings.items,
					enableUpDown = settings.enableUpDown, enableLeftRight = settings.enableLeftRight || !settings.enableUpDown,
					excludeFromTabOrder = settings.excludeFromTabOrder,
					itemFocussed, itemBlurred, rootKeydown, rootFocussed, focussedId;

			dom = dom || tinymce.DOM;

			itemFocussed = function(evt) {
				focussedId = evt.target.id;
			};
			
			itemBlurred = function(evt) {
				dom.setAttrib(evt.target.id, 'tabindex', '-1');
			};
			
			rootFocussed = function(evt) {
				var item = dom.get(focussedId);
				dom.setAttrib(item, 'tabindex', '0');
				item.focus();
			};
			
			t.focus = function() {
				dom.get(focussedId).focus();
			};

			t.destroy = function() {
				each(items, function(item) {
					dom.unbind(dom.get(item.id), 'focus', itemFocussed);
					dom.unbind(dom.get(item.id), 'blur', itemBlurred);
				});

				dom.unbind(dom.get(root), 'focus', rootFocussed);
				dom.unbind(dom.get(root), 'keydown', rootKeydown);

				items = dom = root = t.focus = itemFocussed = itemBlurred = rootKeydown = rootFocussed = null;
				t.destroy = function() {};
			};
			
			t.moveFocus = function(dir, evt) {
				var idx = -1, controls = t.controls, newFocus;

				if (!focussedId)
					return;

				each(items, function(item, index) {
					if (item.id === focussedId) {
						idx = index;
						return false;
					}
				});

				idx += dir;
				if (idx < 0) {
					idx = items.length - 1;
				} else if (idx >= items.length) {
					idx = 0;
				}
				
				newFocus = items[idx];
				dom.setAttrib(focussedId, 'tabindex', '-1');
				dom.setAttrib(newFocus.id, 'tabindex', '0');
				dom.get(newFocus.id).focus();

				if (settings.actOnFocus) {
					settings.onAction(newFocus.id);
				}

				if (evt)
					Event.cancel(evt);
			};
			
			rootKeydown = function(evt) {
				var DOM_VK_LEFT = 37, DOM_VK_RIGHT = 39, DOM_VK_UP = 38, DOM_VK_DOWN = 40, DOM_VK_ESCAPE = 27, DOM_VK_ENTER = 14, DOM_VK_RETURN = 13, DOM_VK_SPACE = 32;
				
				switch (evt.keyCode) {
					case DOM_VK_LEFT:
						if (enableLeftRight) t.moveFocus(-1);
						break;
	
					case DOM_VK_RIGHT:
						if (enableLeftRight) t.moveFocus(1);
						break;
	
					case DOM_VK_UP:
						if (enableUpDown) t.moveFocus(-1);
						break;

					case DOM_VK_DOWN:
						if (enableUpDown) t.moveFocus(1);
						break;

					case DOM_VK_ESCAPE:
						if (settings.onCancel) {
							settings.onCancel();
							Event.cancel(evt);
						}
						break;

					case DOM_VK_ENTER:
					case DOM_VK_RETURN:
					case DOM_VK_SPACE:
						if (settings.onAction) {
							settings.onAction(focussedId);
							Event.cancel(evt);
						}
						break;
				}
			};

			// Set up state and listeners for each item.
			each(items, function(item, idx) {
				var tabindex;

				if (!item.id) {
					item.id = dom.uniqueId('_mce_item_');
				}

				if (excludeFromTabOrder) {
					dom.bind(item.id, 'blur', itemBlurred);
					tabindex = '-1';
				} else {
					tabindex = (idx === 0 ? '0' : '-1');
				}

				dom.setAttrib(item.id, 'tabindex', tabindex);
				dom.bind(dom.get(item.id), 'focus', itemFocussed);
			});
			
			// Setup initial state for root element.
			if (items[0]){
				focussedId = items[0].id;
			}

			dom.setAttrib(root, 'tabindex', '-1');
			
			// Setup listeners for root element.
			dom.bind(dom.get(root), 'focus', rootFocussed);
			dom.bind(dom.get(root), 'keydown', rootKeydown);
		}
	});
})(tinymce);

(function(tinymce) {
	// Shorten class names
	var DOM = tinymce.DOM, is = tinymce.is;

	tinymce.create('tinymce.ui.Control', {
		Control : function(id, s, editor) {
			this.id = id;
			this.settings = s = s || {};
			this.rendered = false;
			this.onRender = new tinymce.util.Dispatcher(this);
			this.classPrefix = '';
			this.scope = s.scope || this;
			this.disabled = 0;
			this.active = 0;
			this.editor = editor;
		},
		
		setAriaProperty : function(property, value) {
			var element = DOM.get(this.id + '_aria') || DOM.get(this.id);
			if (element) {
				DOM.setAttrib(element, 'aria-' + property, !!value);
			}
		},
		
		focus : function() {
			DOM.get(this.id).focus();
		},

		setDisabled : function(s) {
			if (s != this.disabled) {
				this.setAriaProperty('disabled', s);

				this.setState('Disabled', s);
				this.setState('Enabled', !s);
				this.disabled = s;
			}
		},

		isDisabled : function() {
			return this.disabled;
		},

		setActive : function(s) {
			if (s != this.active) {
				this.setState('Active', s);
				this.active = s;
				this.setAriaProperty('pressed', s);
			}
		},

		isActive : function() {
			return this.active;
		},

		setState : function(c, s) {
			var n = DOM.get(this.id);

			c = this.classPrefix + c;

			if (s)
				DOM.addClass(n, c);
			else
				DOM.removeClass(n, c);
		},

		isRendered : function() {
			return this.rendered;
		},

		renderHTML : function() {
		},

		renderTo : function(n) {
			DOM.setHTML(n, this.renderHTML());
		},

		postRender : function() {
			var t = this, b;

			// Set pending states
			if (is(t.disabled)) {
				b = t.disabled;
				t.disabled = -1;
				t.setDisabled(b);
			}

			if (is(t.active)) {
				b = t.active;
				t.active = -1;
				t.setActive(b);
			}
		},

		remove : function() {
			DOM.remove(this.id);
			this.destroy();
		},

		destroy : function() {
			tinymce.dom.Event.clear(this.id);
		}
	});
})(tinymce);
tinymce.create('tinymce.ui.Container:tinymce.ui.Control', {
	Container : function(id, s, editor) {
		this.parent(id, s, editor);

		this.controls = [];

		this.lookup = {};
	},

	add : function(c) {
		this.lookup[c.id] = c;
		this.controls.push(c);

		return c;
	},

	get : function(n) {
		return this.lookup[n];
	}
});


tinymce.create('tinymce.ui.Separator:tinymce.ui.Control', {
	Separator : function(id, s) {
		this.parent(id, s);
		this.classPrefix = 'mceSeparator';
		this.setDisabled(true);
	},

	renderHTML : function() {
		return tinymce.DOM.createHTML('span', {'class' : this.classPrefix, role : 'separator', 'aria-orientation' : 'vertical', tabindex : '-1'});
	}
});

(function(tinymce) {
	var is = tinymce.is, DOM = tinymce.DOM, each = tinymce.each, walk = tinymce.walk;

	tinymce.create('tinymce.ui.MenuItem:tinymce.ui.Control', {
		MenuItem : function(id, s) {
			this.parent(id, s);
			this.classPrefix = 'mceMenuItem';
		},

		setSelected : function(s) {
			this.setState('Selected', s);
			this.setAriaProperty('checked', !!s);
			this.selected = s;
		},

		isSelected : function() {
			return this.selected;
		},

		postRender : function() {
			var t = this;
			
			t.parent();

			// Set pending state
			if (is(t.selected))
				t.setSelected(t.selected);
		}
	});
})(tinymce);

(function(tinymce) {
	var is = tinymce.is, DOM = tinymce.DOM, each = tinymce.each, walk = tinymce.walk;

	tinymce.create('tinymce.ui.Menu:tinymce.ui.MenuItem', {
		Menu : function(id, s) {
			var t = this;

			t.parent(id, s);
			t.items = {};
			t.collapsed = false;
			t.menuCount = 0;
			t.onAddItem = new tinymce.util.Dispatcher(this);
		},

		expand : function(d) {
			var t = this;

			if (d) {
				walk(t, function(o) {
					if (o.expand)
						o.expand();
				}, 'items', t);
			}

			t.collapsed = false;
		},

		collapse : function(d) {
			var t = this;

			if (d) {
				walk(t, function(o) {
					if (o.collapse)
						o.collapse();
				}, 'items', t);
			}

			t.collapsed = true;
		},

		isCollapsed : function() {
			return this.collapsed;
		},

		add : function(o) {
			if (!o.settings)
				o = new tinymce.ui.MenuItem(o.id || DOM.uniqueId(), o);

			this.onAddItem.dispatch(this, o);

			return this.items[o.id] = o;
		},

		addSeparator : function() {
			return this.add({separator : true});
		},

		addMenu : function(o) {
			if (!o.collapse)
				o = this.createMenu(o);

			this.menuCount++;

			return this.add(o);
		},

		hasMenus : function() {
			return this.menuCount !== 0;
		},

		remove : function(o) {
			delete this.items[o.id];
		},

		removeAll : function() {
			var t = this;

			walk(t, function(o) {
				if (o.removeAll)
					o.removeAll();
				else
					o.remove();

				o.destroy();
			}, 'items', t);

			t.items = {};
		},

		createMenu : function(o) {
			var m = new tinymce.ui.Menu(o.id || DOM.uniqueId(), o);

			m.onAddItem.add(this.onAddItem.dispatch, this.onAddItem);

			return m;
		}
	});
})(tinymce);
(function(tinymce) {
	var is = tinymce.is, DOM = tinymce.DOM, each = tinymce.each, Event = tinymce.dom.Event, Element = tinymce.dom.Element;

	tinymce.create('tinymce.ui.DropMenu:tinymce.ui.Menu', {
		DropMenu : function(id, s) {
			s = s || {};
			s.container = s.container || DOM.doc.body;
			s.offset_x = s.offset_x || 0;
			s.offset_y = s.offset_y || 0;
			s.vp_offset_x = s.vp_offset_x || 0;
			s.vp_offset_y = s.vp_offset_y || 0;

			if (is(s.icons) && !s.icons)
				s['class'] += ' mceNoIcons';

			this.parent(id, s);
			this.onShowMenu = new tinymce.util.Dispatcher(this);
			this.onHideMenu = new tinymce.util.Dispatcher(this);
			this.classPrefix = 'mceMenu';
		},

		createMenu : function(s) {
			var t = this, cs = t.settings, m;

			s.container = s.container || cs.container;
			s.parent = t;
			s.constrain = s.constrain || cs.constrain;
			s['class'] = s['class'] || cs['class'];
			s.vp_offset_x = s.vp_offset_x || cs.vp_offset_x;
			s.vp_offset_y = s.vp_offset_y || cs.vp_offset_y;
			s.keyboard_focus = cs.keyboard_focus;
			m = new tinymce.ui.DropMenu(s.id || DOM.uniqueId(), s);

			m.onAddItem.add(t.onAddItem.dispatch, t.onAddItem);

			return m;
		},
		
		focus : function() {
			var t = this;
			if (t.keyboardNav) {
				t.keyboardNav.focus();
			}
		},

		update : function() {
			var t = this, s = t.settings, tb = DOM.get('menu_' + t.id + '_tbl'), co = DOM.get('menu_' + t.id + '_co'), tw, th;

			tw = s.max_width ? Math.min(tb.clientWidth, s.max_width) : tb.clientWidth;
			th = s.max_height ? Math.min(tb.clientHeight, s.max_height) : tb.clientHeight;

			if (!DOM.boxModel)
				t.element.setStyles({width : tw + 2, height : th + 2});
			else
				t.element.setStyles({width : tw, height : th});

			if (s.max_width)
				DOM.setStyle(co, 'width', tw);

			if (s.max_height) {
				DOM.setStyle(co, 'height', th);

				if (tb.clientHeight < s.max_height)
					DOM.setStyle(co, 'overflow', 'hidden');
			}
		},

		showMenu : function(x, y, px) {
			var t = this, s = t.settings, co, vp = DOM.getViewPort(), w, h, mx, my, ot = 2, dm, tb, cp = t.classPrefix;

			t.collapse(1);

			if (t.isMenuVisible)
				return;

			if (!t.rendered) {
				co = DOM.add(t.settings.container, t.renderNode());

				each(t.items, function(o) {
					o.postRender();
				});

				t.element = new Element('menu_' + t.id, {blocker : 1, container : s.container});
			} else
				co = DOM.get('menu_' + t.id);

			// Move layer out of sight unless it's Opera since it scrolls to top of page due to an bug
			if (!tinymce.isOpera)
				DOM.setStyles(co, {left : -0xFFFF , top : -0xFFFF});

			DOM.show(co);
			t.update();

			x += s.offset_x || 0;
			y += s.offset_y || 0;
			vp.w -= 4;
			vp.h -= 4;

			// Move inside viewport if not submenu
			if (s.constrain) {
				w = co.clientWidth - ot;
				h = co.clientHeight - ot;
				mx = vp.x + vp.w;
				my = vp.y + vp.h;

				if ((x + s.vp_offset_x + w) > mx)
					x = px ? px - w : Math.max(0, (mx - s.vp_offset_x) - w);

				if ((y + s.vp_offset_y + h) > my)
					y = Math.max(0, (my - s.vp_offset_y) - h);
			}

			DOM.setStyles(co, {left : x , top : y});
			t.element.update();

			t.isMenuVisible = 1;
			t.mouseClickFunc = Event.add(co, 'click', function(e) {
				var m;

				e = e.target;

				if (e && (e = DOM.getParent(e, 'tr')) && !DOM.hasClass(e, cp + 'ItemSub')) {
					m = t.items[e.id];

					if (m.isDisabled())
						return;

					dm = t;

					while (dm) {
						if (dm.hideMenu)
							dm.hideMenu();

						dm = dm.settings.parent;
					}

					if (m.settings.onclick)
						m.settings.onclick(e);

					return Event.cancel(e); // Cancel to fix onbeforeunload problem
				}
			});

			if (t.hasMenus()) {
				t.mouseOverFunc = Event.add(co, 'mouseover', function(e) {
					var m, r, mi;

					e = e.target;
					if (e && (e = DOM.getParent(e, 'tr'))) {
						m = t.items[e.id];

						if (t.lastMenu)
							t.lastMenu.collapse(1);

						if (m.isDisabled())
							return;

						if (e && DOM.hasClass(e, cp + 'ItemSub')) {
							//p = DOM.getPos(s.container);
							r = DOM.getRect(e);
							m.showMenu((r.x + r.w - ot), r.y - ot, r.x);
							t.lastMenu = m;
							DOM.addClass(DOM.get(m.id).firstChild, cp + 'ItemActive');
						}
					}
				});
			}
			
			Event.add(co, 'keydown', t._keyHandler, t);

			t.onShowMenu.dispatch(t);

			if (s.keyboard_focus) { 
				t._setupKeyboardNav(); 
			}
		},

		hideMenu : function(c) {
			var t = this, co = DOM.get('menu_' + t.id), e;

			if (!t.isMenuVisible)
				return;

			if (t.keyboardNav) t.keyboardNav.destroy();
			Event.remove(co, 'mouseover', t.mouseOverFunc);
			Event.remove(co, 'click', t.mouseClickFunc);
			Event.remove(co, 'keydown', t._keyHandler);
			DOM.hide(co);
			t.isMenuVisible = 0;

			if (!c)
				t.collapse(1);

			if (t.element)
				t.element.hide();

			if (e = DOM.get(t.id))
				DOM.removeClass(e.firstChild, t.classPrefix + 'ItemActive');

			t.onHideMenu.dispatch(t);
		},

		add : function(o) {
			var t = this, co;

			o = t.parent(o);

			if (t.isRendered && (co = DOM.get('menu_' + t.id)))
				t._add(DOM.select('tbody', co)[0], o);

			return o;
		},

		collapse : function(d) {
			this.parent(d);
			this.hideMenu(1);
		},

		remove : function(o) {
			DOM.remove(o.id);
			this.destroy();

			return this.parent(o);
		},

		destroy : function() {
			var t = this, co = DOM.get('menu_' + t.id);

			if (t.keyboardNav) t.keyboardNav.destroy();
			Event.remove(co, 'mouseover', t.mouseOverFunc);
			Event.remove(DOM.select('a', co), 'focus', t.mouseOverFunc);
			Event.remove(co, 'click', t.mouseClickFunc);
			Event.remove(co, 'keydown', t._keyHandler);

			if (t.element)
				t.element.remove();

			DOM.remove(co);
		},

		renderNode : function() {
			var t = this, s = t.settings, n, tb, co, w;

			w = DOM.create('div', {role: 'listbox', id : 'menu_' + t.id, 'class' : s['class'], 'style' : 'position:absolute;left:0;top:0;z-index:200000;outline:0'});
			if (t.settings.parent) {
				DOM.setAttrib(w, 'aria-parent', 'menu_' + t.settings.parent.id);
			}
			co = DOM.add(w, 'div', {role: 'presentation', id : 'menu_' + t.id + '_co', 'class' : t.classPrefix + (s['class'] ? ' ' + s['class'] : '')});
			t.element = new Element('menu_' + t.id, {blocker : 1, container : s.container});

			if (s.menu_line)
				DOM.add(co, 'span', {'class' : t.classPrefix + 'Line'});

//			n = DOM.add(co, 'div', {id : 'menu_' + t.id + '_co', 'class' : 'mceMenuContainer'});
			n = DOM.add(co, 'table', {role: 'presentation', id : 'menu_' + t.id + '_tbl', border : 0, cellPadding : 0, cellSpacing : 0});
			tb = DOM.add(n, 'tbody');

			each(t.items, function(o) {
				t._add(tb, o);
			});

			t.rendered = true;

			return w;
		},

		// Internal functions
		_setupKeyboardNav : function(){
			var contextMenu, menuItems, t=this; 
			contextMenu = DOM.select('#menu_' + t.id)[0];
			menuItems = DOM.select('a[role=option]', 'menu_' + t.id);
			menuItems.splice(0,0,contextMenu);
			t.keyboardNav = new tinymce.ui.KeyboardNavigation({
				root: 'menu_' + t.id,
				items: menuItems,
				onCancel: function() {
					t.hideMenu();
				},
				enableUpDown: true
			});
			contextMenu.focus();
		},

		_keyHandler : function(evt) {
			var t = this, e;
			switch (evt.keyCode) {
				case 37: // Left
					if (t.settings.parent) {
						t.hideMenu();
						t.settings.parent.focus();
						Event.cancel(evt);
					}
					break;
				case 39: // Right
					if (t.mouseOverFunc)
						t.mouseOverFunc(evt);
					break;
			}
		},

		_add : function(tb, o) {
			var n, s = o.settings, a, ro, it, cp = this.classPrefix, ic;

			if (s.separator) {
				ro = DOM.add(tb, 'tr', {id : o.id, 'class' : cp + 'ItemSeparator'});
				DOM.add(ro, 'td', {'class' : cp + 'ItemSeparator'});

				if (n = ro.previousSibling)
					DOM.addClass(n, 'mceLast');

				return;
			}

			n = ro = DOM.add(tb, 'tr', {id : o.id, 'class' : cp + 'Item ' + cp + 'ItemEnabled'});
			n = it = DOM.add(n, s.titleItem ? 'th' : 'td');
			n = a = DOM.add(n, 'a', {id: o.id + '_aria',  role: s.titleItem ? 'presentation' : 'option', href : 'javascript:;', onclick : "return false;", onmousedown : 'return false;'});

			if (s.parent) {
				DOM.setAttrib(a, 'aria-haspopup', 'true');
				DOM.setAttrib(a, 'aria-owns', 'menu_' + o.id);
			}

			DOM.addClass(it, s['class']);
//			n = DOM.add(n, 'span', {'class' : 'item'});

			ic = DOM.add(n, 'span', {'class' : 'mceIcon' + (s.icon ? ' mce_' + s.icon : '')});

			if (s.icon_src)
				DOM.add(ic, 'img', {src : s.icon_src});

			n = DOM.add(n, s.element || 'span', {'class' : 'mceText', title : o.settings.title}, o.settings.title);

			if (o.settings.style)
				DOM.setAttrib(n, 'style', o.settings.style);

			if (tb.childNodes.length == 1)
				DOM.addClass(ro, 'mceFirst');

			if ((n = ro.previousSibling) && DOM.hasClass(n, cp + 'ItemSeparator'))
				DOM.addClass(ro, 'mceFirst');

			if (o.collapse)
				DOM.addClass(ro, cp + 'ItemSub');

			if (n = ro.previousSibling)
				DOM.removeClass(n, 'mceLast');

			DOM.addClass(ro, 'mceLast');
		}
	});
})(tinymce);
(function(tinymce) {
	var DOM = tinymce.DOM;

	tinymce.create('tinymce.ui.Button:tinymce.ui.Control', {
		Button : function(id, s, ed) {
			this.parent(id, s, ed);
			this.classPrefix = 'mceButton';
		},

		renderHTML : function() {
			var cp = this.classPrefix, s = this.settings, h, l;

			l = DOM.encode(s.label || '');
			h = '<a role="button" id="' + this.id + '" href="javascript:;" class="' + cp + ' ' + cp + 'Enabled ' + s['class'] + (l ? ' ' + cp + 'Labeled' : '') +'" onmousedown="return false;" onclick="return false;" aria-labelledby="' + this.id + '_voice" title="' + DOM.encode(s.title) + '">';
			if (s.image && !(this.editor  &&this.editor.forcedHighContrastMode) )
				h += '<img class="mceIcon" src="' + s.image + '" alt="' + DOM.encode(s.title) + '" />' + l;
			else
				h += '<span class="mceIcon ' + s['class'] + '"></span>' + (l ? '<span class="' + cp + 'Label">' + l + '</span>' : '');

			h += '<span class="mceVoiceLabel mceIconOnly" style="display: none;" id="' + this.id + '_voice">' + s.title + '</span>'; 
			h += '</a>';
			return h;
		},

		postRender : function() {
			var t = this, s = t.settings;

			tinymce.dom.Event.add(t.id, 'click', function(e) {
				if (!t.isDisabled())
					return s.onclick.call(s.scope, e);
			});
		}
	});
})(tinymce);

(function(tinymce) {
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, each = tinymce.each, Dispatcher = tinymce.util.Dispatcher;

	tinymce.create('tinymce.ui.ListBox:tinymce.ui.Control', {
		ListBox : function(id, s, ed) {
			var t = this;

			t.parent(id, s, ed);

			t.items = [];

			t.onChange = new Dispatcher(t);

			t.onPostRender = new Dispatcher(t);

			t.onAdd = new Dispatcher(t);

			t.onRenderMenu = new tinymce.util.Dispatcher(this);

			t.classPrefix = 'mceListBox';
		},

		select : function(va) {
			var t = this, fv, f;

			if (va == undefined)
				return t.selectByIndex(-1);

			// Is string or number make function selector
			if (va && va.call)
				f = va;
			else {
				f = function(v) {
					return v == va;
				};
			}

			// Do we need to do something?
			if (va != t.selectedValue) {
				// Find item
				each(t.items, function(o, i) {
					if (f(o.value)) {
						fv = 1;
						t.selectByIndex(i);
						return false;
					}
				});

				if (!fv)
					t.selectByIndex(-1);
			}
		},

		selectByIndex : function(idx) {
			var t = this, e, o, label;

			if (idx != t.selectedIndex) {
				e = DOM.get(t.id + '_text');
				label = DOM.get(t.id + '_voiceDesc');
				o = t.items[idx];

				if (o) {
					t.selectedValue = o.value;
					t.selectedIndex = idx;
					DOM.setHTML(e, DOM.encode(o.title));
					DOM.setHTML(label, t.settings.title + " - " + o.title);
					DOM.removeClass(e, 'mceTitle');
					DOM.setAttrib(t.id, 'aria-valuenow', o.title);
				} else {
					DOM.setHTML(e, DOM.encode(t.settings.title));
					DOM.setHTML(label, DOM.encode(t.settings.title));
					DOM.addClass(e, 'mceTitle');
					t.selectedValue = t.selectedIndex = null;
					DOM.setAttrib(t.id, 'aria-valuenow', t.settings.title);
				}
				e = 0;
			}
		},

		add : function(n, v, o) {
			var t = this;

			o = o || {};
			o = tinymce.extend(o, {
				title : n,
				value : v
			});

			t.items.push(o);
			t.onAdd.dispatch(t, o);
		},

		getLength : function() {
			return this.items.length;
		},

		renderHTML : function() {
			var h = '', t = this, s = t.settings, cp = t.classPrefix;

			h = '<span role="listbox" aria-haspopup="true" aria-labelledby="' + t.id +'_voiceDesc" aria-describedby="' + t.id + '_voiceDesc"><table role="presentation" tabindex="0" id="' + t.id + '" cellpadding="0" cellspacing="0" class="' + cp + ' ' + cp + 'Enabled' + (s['class'] ? (' ' + s['class']) : '') + '"><tbody><tr>';
			h += '<td>' + DOM.createHTML('span', {id: t.id + '_voiceDesc', 'class': 'voiceLabel', style:'display:none;'}, t.settings.title); 
			h += DOM.createHTML('a', {id : t.id + '_text', tabindex : -1, href : 'javascript:;', 'class' : 'mceText', onclick : "return false;", onmousedown : 'return false;'}, DOM.encode(t.settings.title)) + '</td>';
			h += '<td>' + DOM.createHTML('a', {id : t.id + '_open', tabindex : -1, href : 'javascript:;', 'class' : 'mceOpen', onclick : "return false;", onmousedown : 'return false;'}, '<span><span style="display:none;" class="mceIconOnly" aria-hidden="true">\u25BC</span></span>') + '</td>';
			h += '</tr></tbody></table></span>';

			return h;
		},

		showMenu : function() {
			var t = this, p2, e = DOM.get(this.id), m;

			if (t.isDisabled() || t.items.length == 0)
				return;

			if (t.menu && t.menu.isMenuVisible)
				return t.hideMenu();

			if (!t.isMenuRendered) {
				t.renderMenu();
				t.isMenuRendered = true;
			}

			p2 = DOM.getPos(e);

			m = t.menu;
			m.settings.offset_x = p2.x;
			m.settings.offset_y = p2.y;
			m.settings.keyboard_focus = !tinymce.isOpera; // Opera is buggy when it comes to auto focus

			// Select in menu
			if (t.oldID)
				m.items[t.oldID].setSelected(0);

			each(t.items, function(o) {
				if (o.value === t.selectedValue) {
					m.items[o.id].setSelected(1);
					t.oldID = o.id;
				}
			});

			m.showMenu(0, e.clientHeight);

			Event.add(DOM.doc, 'mousedown', t.hideMenu, t);
			DOM.addClass(t.id, t.classPrefix + 'Selected');

			//DOM.get(t.id + '_text').focus();
		},

		hideMenu : function(e) {
			var t = this;

			if (t.menu && t.menu.isMenuVisible) {
				DOM.removeClass(t.id, t.classPrefix + 'Selected');

				// Prevent double toogles by canceling the mouse click event to the button
				if (e && e.type == "mousedown" && (e.target.id == t.id + '_text' || e.target.id == t.id + '_open'))
					return;

				if (!e || !DOM.getParent(e.target, '.mceMenu')) {
					DOM.removeClass(t.id, t.classPrefix + 'Selected');
					Event.remove(DOM.doc, 'mousedown', t.hideMenu, t);
					t.menu.hideMenu();
				}
			}
		},

		renderMenu : function() {
			var t = this, m;

			m = t.settings.control_manager.createDropMenu(t.id + '_menu', {
				menu_line : 1,
				'class' : t.classPrefix + 'Menu mceNoIcons',
				max_width : 150,
				max_height : 150
			});

			m.onHideMenu.add(function() {
				t.hideMenu();
				t.focus();
			});

			m.add({
				title : t.settings.title,
				'class' : 'mceMenuItemTitle',
				onclick : function() {
					if (t.settings.onselect('') !== false)
						t.select(''); // Must be runned after
				}
			});

			each(t.items, function(o) {
				// No value then treat it as a title
				if (o.value === undefined) {
					m.add({
						title : o.title,
						role : "option",
						'class' : 'mceMenuItemTitle',
						onclick : function() {
							if (t.settings.onselect('') !== false)
								t.select(''); // Must be runned after
						}
					});
				} else {
					o.id = DOM.uniqueId();
					o.role= "option";
					o.onclick = function() {
						if (t.settings.onselect(o.value) !== false)
							t.select(o.value); // Must be runned after
					};

					m.add(o);
				}
			});

			t.onRenderMenu.dispatch(t, m);
			t.menu = m;
		},

		postRender : function() {
			var t = this, cp = t.classPrefix;

			Event.add(t.id, 'click', t.showMenu, t);
			Event.add(t.id, 'keydown', function(evt) {
				if (evt.keyCode == 32) { // Space
					t.showMenu(evt);
					Event.cancel(evt);
				}
			});
			Event.add(t.id, 'focus', function() {
				if (!t._focused) {
					t.keyDownHandler = Event.add(t.id, 'keydown', function(e) {
						if (e.keyCode == 40) {
							t.showMenu();
							Event.cancel(e);
						}
					});
					t.keyPressHandler = Event.add(t.id, 'keypress', function(e) {
						var v;
						if (e.keyCode == 13) {
							// Fake select on enter
							v = t.selectedValue;
							t.selectedValue = null; // Needs to be null to fake change
							Event.cancel(e);
							t.settings.onselect(v);
						}
					});
				}

				t._focused = 1;
			});
			Event.add(t.id, 'blur', function() {
				Event.remove(t.id, 'keydown', t.keyDownHandler);
				Event.remove(t.id, 'keypress', t.keyPressHandler);
				t._focused = 0;
			});

			// Old IE doesn't have hover on all elements
			if (tinymce.isIE6 || !DOM.boxModel) {
				Event.add(t.id, 'mouseover', function() {
					if (!DOM.hasClass(t.id, cp + 'Disabled'))
						DOM.addClass(t.id, cp + 'Hover');
				});

				Event.add(t.id, 'mouseout', function() {
					if (!DOM.hasClass(t.id, cp + 'Disabled'))
						DOM.removeClass(t.id, cp + 'Hover');
				});
			}

			t.onPostRender.dispatch(t, DOM.get(t.id));
		},

		destroy : function() {
			this.parent();

			Event.clear(this.id + '_text');
			Event.clear(this.id + '_open');
		}
	});
})(tinymce);

(function(tinymce) {
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, each = tinymce.each, Dispatcher = tinymce.util.Dispatcher;

	tinymce.create('tinymce.ui.NativeListBox:tinymce.ui.ListBox', {
		NativeListBox : function(id, s) {
			this.parent(id, s);
			this.classPrefix = 'mceNativeListBox';
		},

		setDisabled : function(s) {
			DOM.get(this.id).disabled = s;
			this.setAriaProperty('disabled', s);
		},

		isDisabled : function() {
			return DOM.get(this.id).disabled;
		},

		select : function(va) {
			var t = this, fv, f;

			if (va == undefined)
				return t.selectByIndex(-1);

			// Is string or number make function selector
			if (va && va.call)
				f = va;
			else {
				f = function(v) {
					return v == va;
				};
			}

			// Do we need to do something?
			if (va != t.selectedValue) {
				// Find item
				each(t.items, function(o, i) {
					if (f(o.value)) {
						fv = 1;
						t.selectByIndex(i);
						return false;
					}
				});

				if (!fv)
					t.selectByIndex(-1);
			}
		},

		selectByIndex : function(idx) {
			DOM.get(this.id).selectedIndex = idx + 1;
			this.selectedValue = this.items[idx] ? this.items[idx].value : null;
		},

		add : function(n, v, a) {
			var o, t = this;

			a = a || {};
			a.value = v;

			if (t.isRendered())
				DOM.add(DOM.get(this.id), 'option', a, n);

			o = {
				title : n,
				value : v,
				attribs : a
			};

			t.items.push(o);
			t.onAdd.dispatch(t, o);
		},

		getLength : function() {
			return this.items.length;
		},

		renderHTML : function() {
			var h, t = this;

			h = DOM.createHTML('option', {value : ''}, '-- ' + t.settings.title + ' --');

			each(t.items, function(it) {
				h += DOM.createHTML('option', {value : it.value}, it.title);
			});

			h = DOM.createHTML('select', {id : t.id, 'class' : 'mceNativeListBox', 'aria-labelledby': t.id + '_aria'}, h);
			h += DOM.createHTML('span', {id : t.id + '_aria', 'style': 'display: none'}, t.settings.title);
			return h;
		},

		postRender : function() {
			var t = this, ch, changeListenerAdded = true;

			t.rendered = true;

			function onChange(e) {
				var v = t.items[e.target.selectedIndex - 1];

				if (v && (v = v.value)) {
					t.onChange.dispatch(t, v);

					if (t.settings.onselect)
						t.settings.onselect(v);
				}
			};

			Event.add(t.id, 'change', onChange);

			// Accessibility keyhandler
			Event.add(t.id, 'keydown', function(e) {
				var bf;

				Event.remove(t.id, 'change', ch);
				changeListenerAdded = false;

				bf = Event.add(t.id, 'blur', function() {
					if (changeListenerAdded) return;
					changeListenerAdded = true;
					Event.add(t.id, 'change', onChange);
					Event.remove(t.id, 'blur', bf);
				});

				//prevent default left and right keys on chrome - so that the keyboard navigation is used.
				if (tinymce.isWebKit && (e.keyCode==37 ||e.keyCode==39)) {
					return Event.prevent(e);
				}
				
				if (e.keyCode == 13 || e.keyCode == 32) {
					onChange(e);
					return Event.cancel(e);
				}
			});

			t.onPostRender.dispatch(t, DOM.get(t.id));
		}
	});
})(tinymce);

(function(tinymce) {
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, each = tinymce.each;

	tinymce.create('tinymce.ui.MenuButton:tinymce.ui.Button', {
		MenuButton : function(id, s, ed) {
			this.parent(id, s, ed);

			this.onRenderMenu = new tinymce.util.Dispatcher(this);

			s.menu_container = s.menu_container || DOM.doc.body;
		},

		showMenu : function() {
			var t = this, p1, p2, e = DOM.get(t.id), m;

			if (t.isDisabled())
				return;

			if (!t.isMenuRendered) {
				t.renderMenu();
				t.isMenuRendered = true;
			}

			if (t.isMenuVisible)
				return t.hideMenu();

			p1 = DOM.getPos(t.settings.menu_container);
			p2 = DOM.getPos(e);

			m = t.menu;
			m.settings.offset_x = p2.x;
			m.settings.offset_y = p2.y;
			m.settings.vp_offset_x = p2.x;
			m.settings.vp_offset_y = p2.y;
			m.settings.keyboard_focus = t._focused;
			m.showMenu(0, e.clientHeight);

			Event.add(DOM.doc, 'mousedown', t.hideMenu, t);
			t.setState('Selected', 1);

			t.isMenuVisible = 1;
		},

		renderMenu : function() {
			var t = this, m;

			m = t.settings.control_manager.createDropMenu(t.id + '_menu', {
				menu_line : 1,
				'class' : this.classPrefix + 'Menu',
				icons : t.settings.icons
			});

			m.onHideMenu.add(function() {
				t.hideMenu();
				t.focus();
			});

			t.onRenderMenu.dispatch(t, m);
			t.menu = m;
		},

		hideMenu : function(e) {
			var t = this;

			// Prevent double toogles by canceling the mouse click event to the button
			if (e && e.type == "mousedown" && DOM.getParent(e.target, function(e) {return e.id === t.id || e.id === t.id + '_open';}))
				return;

			if (!e || !DOM.getParent(e.target, '.mceMenu')) {
				t.setState('Selected', 0);
				Event.remove(DOM.doc, 'mousedown', t.hideMenu, t);
				if (t.menu)
					t.menu.hideMenu();
			}

			t.isMenuVisible = 0;
		},

		postRender : function() {
			var t = this, s = t.settings;

			Event.add(t.id, 'click', function() {
				if (!t.isDisabled()) {
					if (s.onclick)
						s.onclick(t.value);

					t.showMenu();
				}
			});
		}
	});
})(tinymce);

(function(tinymce) {
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, each = tinymce.each;

	tinymce.create('tinymce.ui.SplitButton:tinymce.ui.MenuButton', {
		SplitButton : function(id, s, ed) {
			this.parent(id, s, ed);
			this.classPrefix = 'mceSplitButton';
		},

		renderHTML : function() {
			var h, t = this, s = t.settings, h1;

			h = '<tbody><tr>';

			if (s.image)
				h1 = DOM.createHTML('img ', {src : s.image, role: 'presentation', 'class' : 'mceAction ' + s['class']});
			else
				h1 = DOM.createHTML('span', {'class' : 'mceAction ' + s['class']}, '');

			h1 += DOM.createHTML('span', {'class': 'mceVoiceLabel mceIconOnly', id: t.id + '_voice', style: 'display:none;'}, s.title);
			h += '<td >' + DOM.createHTML('a', {role: 'button', id : t.id + '_action', tabindex: '-1', href : 'javascript:;', 'class' : 'mceAction ' + s['class'], onclick : "return false;", onmousedown : 'return false;', title : s.title}, h1) + '</td>';
	
			h1 = DOM.createHTML('span', {'class' : 'mceOpen ' + s['class']}, '<span style="display:none;" class="mceIconOnly" aria-hidden="true">\u25BC</span>');
			h += '<td >' + DOM.createHTML('a', {role: 'button', id : t.id + '_open', tabindex: '-1', href : 'javascript:;', 'class' : 'mceOpen ' + s['class'], onclick : "return false;", onmousedown : 'return false;', title : s.title}, h1) + '</td>';

			h += '</tr></tbody>';
			h = DOM.createHTML('table', { role: 'presentation',   'class' : 'mceSplitButton mceSplitButtonEnabled ' + s['class'], cellpadding : '0', cellspacing : '0', title : s.title}, h);
			return DOM.createHTML('div', {id : t.id, role: 'button', tabindex: '0', 'aria-labelledby': t.id + '_voice', 'aria-haspopup': 'true'}, h);
		},

		postRender : function() {
			var t = this, s = t.settings, activate;

			if (s.onclick) {
				activate = function(evt) {
					if (!t.isDisabled()) {
						s.onclick(t.value);
						Event.cancel(evt);
					}
				};
				Event.add(t.id + '_action', 'click', activate);
				Event.add(t.id, ['click', 'keydown'], function(evt) {
					var DOM_VK_SPACE = 32, DOM_VK_ENTER = 14, DOM_VK_RETURN = 13, DOM_VK_UP = 38, DOM_VK_DOWN = 40;
					if ((evt.keyCode === 32 || evt.keyCode === 13 || evt.keyCode === 14) && !evt.altKey && !evt.ctrlKey && !evt.metaKey) {
						activate();
						Event.cancel(evt);
					} else if (evt.type === 'click' || evt.keyCode === DOM_VK_DOWN) {
						t.showMenu();
						Event.cancel(evt);
					}
				});
			}

			Event.add(t.id + '_open', 'click', function (evt) {
				t.showMenu();
				Event.cancel(evt);
			});
			Event.add([t.id, t.id + '_open'], 'focus', function() {t._focused = 1;});
			Event.add([t.id, t.id + '_open'], 'blur', function() {t._focused = 0;});

			// Old IE doesn't have hover on all elements
			if (tinymce.isIE6 || !DOM.boxModel) {
				Event.add(t.id, 'mouseover', function() {
					if (!DOM.hasClass(t.id, 'mceSplitButtonDisabled'))
						DOM.addClass(t.id, 'mceSplitButtonHover');
				});

				Event.add(t.id, 'mouseout', function() {
					if (!DOM.hasClass(t.id, 'mceSplitButtonDisabled'))
						DOM.removeClass(t.id, 'mceSplitButtonHover');
				});
			}
		},

		destroy : function() {
			this.parent();

			Event.clear(this.id + '_action');
			Event.clear(this.id + '_open');
			Event.clear(this.id);
		}
	});
})(tinymce);

(function(tinymce) {
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, is = tinymce.is, each = tinymce.each;

	tinymce.create('tinymce.ui.ColorSplitButton:tinymce.ui.SplitButton', {
		ColorSplitButton : function(id, s, ed) {
			var t = this;

			t.parent(id, s, ed);

			t.settings = s = tinymce.extend({
				colors : '000000,993300,333300,003300,003366,000080,333399,333333,800000,FF6600,808000,008000,008080,0000FF,666699,808080,FF0000,FF9900,99CC00,339966,33CCCC,3366FF,800080,999999,FF00FF,FFCC00,FFFF00,00FF00,00FFFF,00CCFF,993366,C0C0C0,FF99CC,FFCC99,FFFF99,CCFFCC,CCFFFF,99CCFF,CC99FF,FFFFFF',
				grid_width : 8,
				default_color : '#888888'
			}, t.settings);

			t.onShowMenu = new tinymce.util.Dispatcher(t);

			t.onHideMenu = new tinymce.util.Dispatcher(t);

			t.value = s.default_color;
		},

		showMenu : function() {
			var t = this, r, p, e, p2;

			if (t.isDisabled())
				return;

			if (!t.isMenuRendered) {
				t.renderMenu();
				t.isMenuRendered = true;
			}

			if (t.isMenuVisible)
				return t.hideMenu();

			e = DOM.get(t.id);
			DOM.show(t.id + '_menu');
			DOM.addClass(e, 'mceSplitButtonSelected');
			p2 = DOM.getPos(e);
			DOM.setStyles(t.id + '_menu', {
				left : p2.x,
				top : p2.y + e.clientHeight,
				zIndex : 200000
			});
			e = 0;

			Event.add(DOM.doc, 'mousedown', t.hideMenu, t);
			t.onShowMenu.dispatch(t);

			if (t._focused) {
				t._keyHandler = Event.add(t.id + '_menu', 'keydown', function(e) {
					if (e.keyCode == 27)
						t.hideMenu();
				});

				DOM.select('a', t.id + '_menu')[0].focus(); // Select first link
			}

			t.isMenuVisible = 1;
		},

		hideMenu : function(e) {
			var t = this;

			if (t.isMenuVisible) {
				// Prevent double toogles by canceling the mouse click event to the button
				if (e && e.type == "mousedown" && DOM.getParent(e.target, function(e) {return e.id === t.id + '_open';}))
					return;

				if (!e || !DOM.getParent(e.target, '.mceSplitButtonMenu')) {
					DOM.removeClass(t.id, 'mceSplitButtonSelected');
					Event.remove(DOM.doc, 'mousedown', t.hideMenu, t);
					Event.remove(t.id + '_menu', 'keydown', t._keyHandler);
					DOM.hide(t.id + '_menu');
				}

				t.isMenuVisible = 0;
				t.onHideMenu.dispatch();
			}
		},

		renderMenu : function() {
			var t = this, m, i = 0, s = t.settings, n, tb, tr, w, context;

			w = DOM.add(s.menu_container, 'div', {role: 'listbox', id : t.id + '_menu', 'class' : s['menu_class'] + ' ' + s['class'], style : 'position:absolute;left:0;top:-1000px;'});
			m = DOM.add(w, 'div', {'class' : s['class'] + ' mceSplitButtonMenu'});
			DOM.add(m, 'span', {'class' : 'mceMenuLine'});

			n = DOM.add(m, 'table', {role: 'presentation', 'class' : 'mceColorSplitMenu'});
			tb = DOM.add(n, 'tbody');

			// Generate color grid
			i = 0;
			each(is(s.colors, 'array') ? s.colors : s.colors.split(','), function(c) {
				c = c.replace(/^#/, '');

				if (!i--) {
					tr = DOM.add(tb, 'tr');
					i = s.grid_width - 1;
				}

				n = DOM.add(tr, 'td');
				n = DOM.add(n, 'a', {
					role : 'option',
					href : 'javascript:;',
					style : {
						backgroundColor : '#' + c
					},
					'title': t.editor.getLang('colors.' + c, c),
					'data-mce-color' : '#' + c
				});

				if (t.editor.forcedHighContrastMode) {
					n = DOM.add(n, 'canvas', { width: 16, height: 16, 'aria-hidden': 'true' });
					if (n.getContext && (context = n.getContext("2d"))) {
						context.fillStyle = '#' + c;
						context.fillRect(0, 0, 16, 16);
					} else {
						// No point leaving a canvas element around if it's not supported for drawing on anyway.
						DOM.remove(n);
					}
				}
			});

			if (s.more_colors_func) {
				n = DOM.add(tb, 'tr');
				n = DOM.add(n, 'td', {colspan : s.grid_width, 'class' : 'mceMoreColors'});
				n = DOM.add(n, 'a', {role: 'option', id : t.id + '_more', href : 'javascript:;', onclick : 'return false;', 'class' : 'mceMoreColors'}, s.more_colors_title);

				Event.add(n, 'click', function(e) {
					s.more_colors_func.call(s.more_colors_scope || this);
					return Event.cancel(e); // Cancel to fix onbeforeunload problem
				});
			}

			DOM.addClass(m, 'mceColorSplitMenu');
			
			new tinymce.ui.KeyboardNavigation({
				root: t.id + '_menu',
				items: DOM.select('a', t.id + '_menu'),
				onCancel: function() {
					t.hideMenu();
					t.focus();
				}
			});

			// Prevent IE from scrolling and hindering click to occur #4019
			Event.add(t.id + '_menu', 'mousedown', function(e) {return Event.cancel(e);});

			Event.add(t.id + '_menu', 'click', function(e) {
				var c;

				e = DOM.getParent(e.target, 'a', tb);

				if (e && e.nodeName.toLowerCase() == 'a' && (c = e.getAttribute('data-mce-color')))
					t.setColor(c);

				return Event.cancel(e); // Prevent IE auto save warning
			});

			return w;
		},

		setColor : function(c) {
			this.displayColor(c);
			this.hideMenu();
			this.settings.onselect(c);
		},
		
		displayColor : function(c) {
			var t = this;

			DOM.setStyle(t.id + '_preview', 'backgroundColor', c);

			t.value = c;
		},

		postRender : function() {
			var t = this, id = t.id;

			t.parent();
			DOM.add(id + '_action', 'div', {id : id + '_preview', 'class' : 'mceColorPreview'});
			DOM.setStyle(t.id + '_preview', 'backgroundColor', t.value);
		},

		destroy : function() {
			this.parent();

			Event.clear(this.id + '_menu');
			Event.clear(this.id + '_more');
			DOM.remove(this.id + '_menu');
		}
	});
})(tinymce);

(function(tinymce) {
// Shorten class names
var dom = tinymce.DOM, each = tinymce.each, Event = tinymce.dom.Event;
tinymce.create('tinymce.ui.ToolbarGroup:tinymce.ui.Container', {
	renderHTML : function() {
		var t = this, h = [], controls = t.controls, each = tinymce.each, settings = t.settings;

		h.push('<div id="' + t.id + '" role="group" aria-labelledby="' + t.id + '_voice">');
		//TODO: ACC test this out - adding a role = application for getting the landmarks working well.
		h.push("<span role='application'>");
		h.push('<span id="' + t.id + '_voice" class="mceVoiceLabel" style="display:none;">' + dom.encode(settings.name) + '</span>');
		each(controls, function(toolbar) {
			h.push(toolbar.renderHTML());
		});
		h.push("</span>");
		h.push('</div>');

		return h.join('');
	},
	
	focus : function() {
		var t = this;
		dom.get(t.id).focus();
	},
	
	postRender : function() {
		var t = this, items = [];

		each(t.controls, function(toolbar) {
			each (toolbar.controls, function(control) {
				if (control.id) {
					items.push(control);
				}
			});
		});

		t.keyNav = new tinymce.ui.KeyboardNavigation({
			root: t.id,
			items: items,
			onCancel: function() {
				//Move focus if webkit so that navigation back will read the item.
				if (tinymce.isWebKit) {
					dom.get(t.editor.id+"_ifr").focus();
				}
				t.editor.focus();
			},
			excludeFromTabOrder: !t.settings.tab_focus_toolbar
		});
	},
	
	destroy : function() {
		var self = this;

		self.parent();
		self.keyNav.destroy();
		Event.clear(self.id);
	}
});
})(tinymce);

(function(tinymce) {
// Shorten class names
var dom = tinymce.DOM, each = tinymce.each;
tinymce.create('tinymce.ui.Toolbar:tinymce.ui.Container', {
	renderHTML : function() {
		var t = this, h = '', c, co, s = t.settings, i, pr, nx, cl;

		cl = t.controls;
		for (i=0; i<cl.length; i++) {
			// Get current control, prev control, next control and if the control is a list box or not
			co = cl[i];
			pr = cl[i - 1];
			nx = cl[i + 1];

			// Add toolbar start
			if (i === 0) {
				c = 'mceToolbarStart';

				if (co.Button)
					c += ' mceToolbarStartButton';
				else if (co.SplitButton)
					c += ' mceToolbarStartSplitButton';
				else if (co.ListBox)
					c += ' mceToolbarStartListBox';

				h += dom.createHTML('td', {'class' : c}, dom.createHTML('span', null, '<!-- IE -->'));
			}

			// Add toolbar end before list box and after the previous button
			// This is to fix the o2k7 editor skins
			if (pr && co.ListBox) {
				if (pr.Button || pr.SplitButton)
					h += dom.createHTML('td', {'class' : 'mceToolbarEnd'}, dom.createHTML('span', null, '<!-- IE -->'));
			}

			// Render control HTML

			// IE 8 quick fix, needed to propertly generate a hit area for anchors
			if (dom.stdMode)
				h += '<td style="position: relative">' + co.renderHTML() + '</td>';
			else
				h += '<td>' + co.renderHTML() + '</td>';

			// Add toolbar start after list box and before the next button
			// This is to fix the o2k7 editor skins
			if (nx && co.ListBox) {
				if (nx.Button || nx.SplitButton)
					h += dom.createHTML('td', {'class' : 'mceToolbarStart'}, dom.createHTML('span', null, '<!-- IE -->'));
			}
		}

		c = 'mceToolbarEnd';

		if (co.Button)
			c += ' mceToolbarEndButton';
		else if (co.SplitButton)
			c += ' mceToolbarEndSplitButton';
		else if (co.ListBox)
			c += ' mceToolbarEndListBox';

		h += dom.createHTML('td', {'class' : c}, dom.createHTML('span', null, '<!-- IE -->'));

		return dom.createHTML('table', {id : t.id, 'class' : 'mceToolbar' + (s['class'] ? ' ' + s['class'] : ''), cellpadding : '0', cellspacing : '0', align : t.settings.align || '', role: 'presentation', tabindex: '-1'}, '<tbody><tr>' + h + '</tr></tbody>');
	}
});
})(tinymce);

(function(tinymce) {
	var Dispatcher = tinymce.util.Dispatcher, each = tinymce.each;

	tinymce.create('tinymce.AddOnManager', {
		AddOnManager : function() {
			var self = this;

			self.items = [];
			self.urls = {};
			self.lookup = {};
			self.onAdd = new Dispatcher(self);
		},

		get : function(n) {
			if (this.lookup[n]) {
				return this.lookup[n].instance;
			} else {
				return undefined;
			}
		},

		dependencies : function(n) {
			var result;
			if (this.lookup[n]) {
				result = this.lookup[n].dependencies;
			}
			return result || [];
		},

		requireLangPack : function(n) {
			var s = tinymce.settings;

			if (s && s.language && s.language_load !== false)
				tinymce.ScriptLoader.add(this.urls[n] + '/langs/' + s.language + '.js');
		},

		add : function(id, o, dependencies) {
			this.items.push(o);
			this.lookup[id] = {instance:o, dependencies:dependencies};
			this.onAdd.dispatch(this, id, o);

			return o;
		},
		createUrl: function(baseUrl, dep) {
			if (typeof dep === "object") {
				return dep
			} else {
				return {prefix: baseUrl.prefix, resource: dep, suffix: baseUrl.suffix};
			}
		},

		addComponents: function(pluginName, scripts) {
			var pluginUrl = this.urls[pluginName];
			tinymce.each(scripts, function(script){
				tinymce.ScriptLoader.add(pluginUrl+"/"+script);	
			});
		},

		load : function(n, u, cb, s) {
			var t = this, url = u;

			function loadDependencies() {
				var dependencies = t.dependencies(n);
				tinymce.each(dependencies, function(dep) {
					var newUrl = t.createUrl(u, dep);
					t.load(newUrl.resource, newUrl, undefined, undefined);
				});
				if (cb) {
					if (s) {
						cb.call(s);
					} else {
						cb.call(tinymce.ScriptLoader);
					}
				}
			}

			if (t.urls[n])
				return;
			if (typeof u === "object")
				url = u.prefix + u.resource + u.suffix;

			if (url.indexOf('/') != 0 && url.indexOf('://') == -1)
				url = tinymce.baseURL + '/' + url;

			t.urls[n] = url.substring(0, url.lastIndexOf('/'));

			if (t.lookup[n]) {
				loadDependencies();
			} else {
				tinymce.ScriptLoader.add(url, loadDependencies, s);
			}
		}
	});

	// Create plugin and theme managers
	tinymce.PluginManager = new tinymce.AddOnManager();
	tinymce.ThemeManager = new tinymce.AddOnManager();
}(tinymce));

(function(tinymce) {
	// Shorten names
	var each = tinymce.each, extend = tinymce.extend,
		DOM = tinymce.DOM, Event = tinymce.dom.Event,
		ThemeManager = tinymce.ThemeManager, PluginManager = tinymce.PluginManager,
		explode = tinymce.explode,
		Dispatcher = tinymce.util.Dispatcher, undefined, instanceCounter = 0;

	// Setup some URLs where the editor API is located and where the document is
	tinymce.documentBaseURL = window.location.href.replace(/[\?#].*$/, '').replace(/[\/\\][^\/]+$/, '');
	if (!/[\/\\]$/.test(tinymce.documentBaseURL))
		tinymce.documentBaseURL += '/';

	tinymce.baseURL = new tinymce.util.URI(tinymce.documentBaseURL).toAbsolute(tinymce.baseURL);

	tinymce.baseURI = new tinymce.util.URI(tinymce.baseURL);

	// Add before unload listener
	// This was required since IE was leaking memory if you added and removed beforeunload listeners
	// with attachEvent/detatchEvent so this only adds one listener and instances can the attach to the onBeforeUnload event
	tinymce.onBeforeUnload = new Dispatcher(tinymce);

	// Must be on window or IE will leak if the editor is placed in frame or iframe
	Event.add(window, 'beforeunload', function(e) {
		tinymce.onBeforeUnload.dispatch(tinymce, e);
	});

	tinymce.onAddEditor = new Dispatcher(tinymce);

	tinymce.onRemoveEditor = new Dispatcher(tinymce);

	tinymce.EditorManager = extend(tinymce, {
		editors : [],

		i18n : {},

		activeEditor : null,

		init : function(s) {
			var t = this, pl, sl = tinymce.ScriptLoader, e, el = [], ed;

			function execCallback(se, n, s) {
				var f = se[n];

				if (!f)
					return;

				if (tinymce.is(f, 'string')) {
					s = f.replace(/\.\w+$/, '');
					s = s ? tinymce.resolve(s) : 0;
					f = tinymce.resolve(f);
				}

				return f.apply(s || this, Array.prototype.slice.call(arguments, 2));
			};

			s = extend({
				theme : "simple",
				language : "en"
			}, s);

			t.settings = s;

			// Legacy call
			Event.add(document, 'init', function() {
				var l, co;

				execCallback(s, 'onpageload');

				switch (s.mode) {
					case "exact":
						l = s.elements || '';

						if(l.length > 0) {
							each(explode(l), function(v) {
								if (DOM.get(v)) {
									ed = new tinymce.Editor(v, s);
									el.push(ed);
									ed.render(1);
								} else {
									each(document.forms, function(f) {
										each(f.elements, function(e) {
											if (e.name === v) {
												v = 'mce_editor_' + instanceCounter++;
												DOM.setAttrib(e, 'id', v);

												ed = new tinymce.Editor(v, s);
												el.push(ed);
												ed.render(1);
											}
										});
									});
								}
							});
						}
						break;

					case "textareas":
					case "specific_textareas":
						function hasClass(n, c) {
							return c.constructor === RegExp ? c.test(n.className) : DOM.hasClass(n, c);
						};

						each(DOM.select('textarea'), function(v) {
							if (s.editor_deselector && hasClass(v, s.editor_deselector))
								return;

							if (!s.editor_selector || hasClass(v, s.editor_selector)) {
								// Can we use the name
								e = DOM.get(v.name);
								if (!v.id && !e)
									v.id = v.name;

								// Generate unique name if missing or already exists
								if (!v.id || t.get(v.id))
									v.id = DOM.uniqueId();

								ed = new tinymce.Editor(v.id, s);
								el.push(ed);
								ed.render(1);
							}
						});
						break;
				}

				// Call onInit when all editors are initialized
				if (s.oninit) {
					l = co = 0;

					each(el, function(ed) {
						co++;

						if (!ed.initialized) {
							// Wait for it
							ed.onInit.add(function() {
								l++;

								// All done
								if (l == co)
									execCallback(s, 'oninit');
							});
						} else
							l++;

						// All done
						if (l == co)
							execCallback(s, 'oninit');					
					});
				}
			});
		},

		get : function(id) {
			if (id === undefined)
				return this.editors;

			return this.editors[id];
		},

		getInstanceById : function(id) {
			return this.get(id);
		},

		add : function(editor) {
			var self = this, editors = self.editors;

			// Add named and index editor instance
			editors[editor.id] = editor;
			editors.push(editor);

			self._setActive(editor);
			self.onAddEditor.dispatch(self, editor);


			return editor;
		},

		remove : function(editor) {
			var t = this, i, editors = t.editors;

			// Not in the collection
			if (!editors[editor.id])
				return null;

			delete editors[editor.id];

			for (i = 0; i < editors.length; i++) {
				if (editors[i] == editor) {
					editors.splice(i, 1);
					break;
				}
			}

			// Select another editor since the active one was removed
			if (t.activeEditor == editor)
				t._setActive(editors[0]);

			editor.destroy();
			t.onRemoveEditor.dispatch(t, editor);

			return editor;
		},

		execCommand : function(c, u, v) {
			var t = this, ed = t.get(v), w;

			// Manager commands
			switch (c) {
				case "mceFocus":
					ed.focus();
					return true;

				case "mceAddEditor":
				case "mceAddControl":
					if (!t.get(v))
						new tinymce.Editor(v, t.settings).render();

					return true;

				case "mceAddFrameControl":
					w = v.window;

					// Add tinyMCE global instance and tinymce namespace to specified window
					w.tinyMCE = tinyMCE;
					w.tinymce = tinymce;

					tinymce.DOM.doc = w.document;
					tinymce.DOM.win = w;

					ed = new tinymce.Editor(v.element_id, v);
					ed.render();

					// Fix IE memory leaks
					if (tinymce.isIE) {
						function clr() {
							ed.destroy();
							w.detachEvent('onunload', clr);
							w = w.tinyMCE = w.tinymce = null; // IE leak
						};

						w.attachEvent('onunload', clr);
					}

					v.page_window = null;

					return true;

				case "mceRemoveEditor":
				case "mceRemoveControl":
					if (ed)
						ed.remove();

					return true;

				case 'mceToggleEditor':
					if (!ed) {
						t.execCommand('mceAddControl', 0, v);
						return true;
					}

					if (ed.isHidden())
						ed.show();
					else
						ed.hide();

					return true;
			}

			// Run command on active editor
			if (t.activeEditor)
				return t.activeEditor.execCommand(c, u, v);

			return false;
		},

		execInstanceCommand : function(id, c, u, v) {
			var ed = this.get(id);

			if (ed)
				return ed.execCommand(c, u, v);

			return false;
		},

		triggerSave : function() {
			each(this.editors, function(e) {
				e.save();
			});
		},

		addI18n : function(p, o) {
			var lo, i18n = this.i18n;

			if (!tinymce.is(p, 'string')) {
				each(p, function(o, lc) {
					each(o, function(o, g) {
						each(o, function(o, k) {
							if (g === 'common')
								i18n[lc + '.' + k] = o;
							else
								i18n[lc + '.' + g + '.' + k] = o;
						});
					});
				});
			} else {
				each(o, function(o, k) {
					i18n[p + '.' + k] = o;
				});
			}
		},

		// Private methods

		_setActive : function(editor) {
			this.selectedInstance = this.activeEditor = editor;
		}
	});
})(tinymce);

(function(tinymce) {
	// Shorten these names
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, extend = tinymce.extend,
		Dispatcher = tinymce.util.Dispatcher, each = tinymce.each, isGecko = tinymce.isGecko,
		isIE = tinymce.isIE, isWebKit = tinymce.isWebKit, is = tinymce.is,
		ThemeManager = tinymce.ThemeManager, PluginManager = tinymce.PluginManager,
		inArray = tinymce.inArray, grep = tinymce.grep, explode = tinymce.explode;

	tinymce.create('tinymce.Editor', {
		Editor : function(id, s) {
			var t = this;

			t.id = t.editorId = id;

			t.execCommands = {};
			t.queryStateCommands = {};
			t.queryValueCommands = {};

			t.isNotDirty = false;

			t.plugins = {};

			// Add events to the editor
			each([
				'onPreInit',

				'onBeforeRenderUI',

				'onPostRender',

				'onInit',

				'onRemove',

				'onActivate',

				'onDeactivate',

				'onClick',

				'onEvent',

				'onMouseUp',

				'onMouseDown',

				'onDblClick',

				'onKeyDown',

				'onKeyUp',

				'onKeyPress',

				'onContextMenu',

				'onSubmit',

				'onReset',

				'onPaste',

				'onPreProcess',

				'onPostProcess',

				'onBeforeSetContent',

				'onBeforeGetContent',

				'onSetContent',

				'onGetContent',

				'onLoadContent',

				'onSaveContent',

				'onNodeChange',

				'onChange',

				'onBeforeExecCommand',

				'onExecCommand',

				'onUndo',

				'onRedo',

				'onVisualAid',

				'onSetProgressState'
			], function(e) {
				t[e] = new Dispatcher(t);
			});

			t.settings = s = extend({
				id : id,
				language : 'en',
				docs_language : 'en',
				theme : 'simple',
				skin : 'default',
				delta_width : 0,
				delta_height : 0,
				popup_css : '',
				plugins : '',
				document_base_url : tinymce.documentBaseURL,
				add_form_submit_trigger : 1,
				submit_patch : 1,
				add_unload_trigger : 1,
				convert_urls : 1,
				relative_urls : 1,
				remove_script_host : 1,
				table_inline_editing : 0,
				object_resizing : 1,
				cleanup : 1,
				accessibility_focus : 1,
				custom_shortcuts : 1,
				custom_undo_redo_keyboard_shortcuts : 1,
				custom_undo_redo_restore_selection : 1,
				custom_undo_redo : 1,
				doctype : tinymce.isIE6 ? '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' : '<!DOCTYPE>', // Use old doctype on IE 6 to avoid horizontal scroll
				visual_table_class : 'mceItemTable',
				visual : 1,
				font_size_style_values : 'xx-small,x-small,small,medium,large,x-large,xx-large',
				font_size_legacy_values : 'xx-small,small,medium,large,x-large,xx-large,300%', // See: http://www.w3.org/TR/CSS2/fonts.html#propdef-font-size
				apply_source_formatting : 1,
				directionality : 'ltr',
				forced_root_block : 'p',
				hidden_input : 1,
				padd_empty_editor : 1,
				render_ui : 1,
				init_theme : 1,
				force_p_newlines : 1,
				indentation : '30px',
				keep_styles : 1,
				fix_table_elements : 1,
				inline_styles : 1,
				convert_fonts_to_spans : true,
				indent : 'simple',
				indent_before : 'p,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,ul,li,area,table,thead,tfoot,tbody,tr',
				indent_after : 'p,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,ul,li,area,table,thead,tfoot,tbody,tr',
				validate : true,
				entity_encoding : 'named',
				url_converter : t.convertURL,
				url_converter_scope : t,
				ie7_compat : true
			}, s);

			t.documentBaseURI = new tinymce.util.URI(s.document_base_url || tinymce.documentBaseURL, {
				base_uri : tinyMCE.baseURI
			});

			t.baseURI = tinymce.baseURI;

			t.contentCSS = [];

			// Call setup
			t.execCallback('setup', t);
		},

		render : function(nst) {
			var t = this, s = t.settings, id = t.id, sl = tinymce.ScriptLoader;

			// Page is not loaded yet, wait for it
			if (!Event.domLoaded) {
				Event.add(document, 'init', function() {
					t.render();
				});
				return;
			}

			tinyMCE.settings = s;

			// Element not found, then skip initialization
			if (!t.getElement())
				return;

			// Is a iPad/iPhone and not on iOS5, then skip initialization. We need to sniff 
			// here since the browser says it has contentEditable support but there is no visible
			// caret We will remove this check ones Apple implements full contentEditable support
			if (tinymce.isIDevice && !tinymce.isIOS5)
				return;

			// Add hidden input for non input elements inside form elements
			if (!/TEXTAREA|INPUT/i.test(t.getElement().nodeName) && s.hidden_input && DOM.getParent(id, 'form'))
				DOM.insertAfter(DOM.create('input', {type : 'hidden', name : id}), id);

			if (tinymce.WindowManager)
				t.windowManager = new tinymce.WindowManager(t);

			if (s.encoding == 'xml') {
				t.onGetContent.add(function(ed, o) {
					if (o.save)
						o.content = DOM.encode(o.content);
				});
			}

			if (s.add_form_submit_trigger) {
				t.onSubmit.addToTop(function() {
					if (t.initialized) {
						t.save();
						t.isNotDirty = 1;
					}
				});
			}

			if (s.add_unload_trigger) {
				t._beforeUnload = tinyMCE.onBeforeUnload.add(function() {
					if (t.initialized && !t.destroyed && !t.isHidden())
						t.save({format : 'raw', no_events : true});
				});
			}

			tinymce.addUnload(t.destroy, t);

			if (s.submit_patch) {
				t.onBeforeRenderUI.add(function() {
					var n = t.getElement().form;

					if (!n)
						return;

					// Already patched
					if (n._mceOldSubmit)
						return;

					// Check page uses id="submit" or name="submit" for it's submit button
					if (!n.submit.nodeType && !n.submit.length) {
						t.formElement = n;
						n._mceOldSubmit = n.submit;
						n.submit = function() {
							// Save all instances
							tinymce.triggerSave();
							t.isNotDirty = 1;

							return t.formElement._mceOldSubmit(t.formElement);
						};
					}

					n = null;
				});
			}

			// Load scripts
			function loadScripts() {
				if (s.language && s.language_load !== false)
					sl.add(tinymce.baseURL + '/langs/' + s.language + '.js');

				if (s.theme && s.theme.charAt(0) != '-' && !ThemeManager.urls[s.theme])
					ThemeManager.load(s.theme, 'themes/' + s.theme + '/editor_template' + tinymce.suffix + '.js');

				each(explode(s.plugins), function(p) {
					if (p &&!PluginManager.urls[p]) {
						if (p.charAt(0) == '-') {
							p = p.substr(1, p.length);
							var dependencies = PluginManager.dependencies(p);
							each(dependencies, function(dep) {
								var defaultSettings = {prefix:'plugins/', resource: dep, suffix:'/editor_plugin' + tinymce.suffix + '.js'};
								var dep = PluginManager.createUrl(defaultSettings, dep);
								PluginManager.load(dep.resource, dep);
								
							});
						} else {
							// Skip safari plugin, since it is removed as of 3.3b1
							if (p == 'safari') {
								return;
							}
							PluginManager.load(p, {prefix:'plugins/', resource: p, suffix:'/editor_plugin' + tinymce.suffix + '.js'});
						}
					}
				});

				// Init when que is loaded
				sl.loadQueue(function() {
					if (!t.removed)
						t.init();
				});
			};

			loadScripts();
		},

		init : function() {
			var n, t = this, s = t.settings, w, h, e = t.getElement(), o, ti, u, bi, bc, re, i, initializedPlugins = [];

			tinymce.add(t);

			s.aria_label = s.aria_label || DOM.getAttrib(e, 'aria-label', t.getLang('aria.rich_text_area'));

			if (s.theme) {
				s.theme = s.theme.replace(/-/, '');
				o = ThemeManager.get(s.theme);
				t.theme = new o();

				if (t.theme.init && s.init_theme)
					t.theme.init(t, ThemeManager.urls[s.theme] || tinymce.documentBaseURL.replace(/\/$/, ''));
			}
			function initPlugin(p) {
				var c = PluginManager.get(p), u = PluginManager.urls[p] || tinymce.documentBaseURL.replace(/\/$/, ''), po;
				if (c && tinymce.inArray(initializedPlugins,p) === -1) {
					each(PluginManager.dependencies(p), function(dep){
						initPlugin(dep);
					});
					po = new c(t, u);

					t.plugins[p] = po;

					if (po.init) {
						po.init(t, u);
						initializedPlugins.push(p);
					}
				}
			}
			
			// Create all plugins
			each(explode(s.plugins.replace(/\-/g, '')), initPlugin);

			// Setup popup CSS path(s)
			if (s.popup_css !== false) {
				if (s.popup_css)
					s.popup_css = t.documentBaseURI.toAbsolute(s.popup_css);
				else
					s.popup_css = t.baseURI.toAbsolute("themes/" + s.theme + "/skins/" + s.skin + "/dialog.css");
			}

			if (s.popup_css_add)
				s.popup_css += ',' + t.documentBaseURI.toAbsolute(s.popup_css_add);

			t.controlManager = new tinymce.ControlManager(t);

			if (s.custom_undo_redo) {
				t.onBeforeExecCommand.add(function(ed, cmd, ui, val, a) {
					if (cmd != 'Undo' && cmd != 'Redo' && cmd != 'mceRepaint' && (!a || !a.skip_undo))
						t.undoManager.beforeChange();
				});

				t.onExecCommand.add(function(ed, cmd, ui, val, a) {
					if (cmd != 'Undo' && cmd != 'Redo' && cmd != 'mceRepaint' && (!a || !a.skip_undo))
						t.undoManager.add();
				});
			}

			t.onExecCommand.add(function(ed, c) {
				// Don't refresh the select lists until caret move
				if (!/^(FontName|FontSize)$/.test(c))
					t.nodeChanged();
			});

			// Remove ghost selections on images and tables in Gecko
			if (isGecko) {
				function repaint(a, o) {
					if (!o || !o.initial)
						t.execCommand('mceRepaint');
				};

				t.onUndo.add(repaint);
				t.onRedo.add(repaint);
				t.onSetContent.add(repaint);
			}

			// Enables users to override the control factory
			t.onBeforeRenderUI.dispatch(t, t.controlManager);

			// Measure box
			if (s.render_ui) {
				w = s.width || e.style.width || e.offsetWidth;
				h = s.height || e.style.height || e.offsetHeight;
				t.orgDisplay = e.style.display;
				re = /^[0-9\.]+(|px)$/i;

				if (re.test('' + w))
					w = Math.max(parseInt(w) + (o.deltaWidth || 0), 100);

				if (re.test('' + h))
					h = Math.max(parseInt(h) + (o.deltaHeight || 0), 100);

				// Render UI
				o = t.theme.renderUI({
					targetNode : e,
					width : w,
					height : h,
					deltaWidth : s.delta_width,
					deltaHeight : s.delta_height
				});

				t.editorContainer = o.editorContainer;
			}


			// User specified a document.domain value
			if (document.domain && location.hostname != document.domain)
				tinymce.relaxedDomain = document.domain;

			// Resize editor
			DOM.setStyles(o.sizeContainer || o.editorContainer, {
				width : w,
				height : h
			});

			// Load specified content CSS last
			if (s.content_css) {
				tinymce.each(explode(s.content_css), function(u) {
					t.contentCSS.push(t.documentBaseURI.toAbsolute(u));
				});
			}

			h = (o.iframeHeight || h) + (typeof(h) == 'number' ? (o.deltaHeight || 0) : '');
			if (h < 100)
				h = 100;

			t.iframeHTML = s.doctype + '<html><head xmlns="http://www.w3.org/1999/xhtml">';

			// We only need to override paths if we have to
			// IE has a bug where it remove site absolute urls to relative ones if this is specified
			if (s.document_base_url != tinymce.documentBaseURL)
				t.iframeHTML += '<base href="' + t.documentBaseURI.getURI() + '" />';

			// IE8 doesn't support carets behind images setting ie7_compat would force IE8+ to run in IE7 compat mode.
			if (s.ie7_compat)
				t.iframeHTML += '<meta http-equiv="X-UA-Compatible" content="IE=7" />';
			else
				t.iframeHTML += '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';

			t.iframeHTML += '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';

			// Load the CSS by injecting them into the HTML this will reduce "flicker"
			for (i = 0; i < t.contentCSS.length; i++) {
				t.iframeHTML += '<link type="text/css" rel="stylesheet" href="' + t.contentCSS[i] + '" />';
			}

			bi = s.body_id || 'tinymce';
			if (bi.indexOf('=') != -1) {
				bi = t.getParam('body_id', '', 'hash');
				bi = bi[t.id] || bi;
			}

			bc = s.body_class || '';
			if (bc.indexOf('=') != -1) {
				bc = t.getParam('body_class', '', 'hash');
				bc = bc[t.id] || '';
			}

			t.iframeHTML += '</head><body id="' + bi + '" class="mceContentBody ' + bc + '"><br></body></html>';

			// Domain relaxing enabled, then set document domain
			if (tinymce.relaxedDomain && (isIE || (tinymce.isOpera && parseFloat(opera.version()) < 11))) {
				// We need to write the contents here in IE since multiple writes messes up refresh button and back button
				u = 'javascript:(function(){document.open();document.domain="' + document.domain + '";var ed = window.parent.tinyMCE.get("' + t.id + '");document.write(ed.iframeHTML);document.close();ed.setupIframe();})()';
			}

			// Create iframe
			// TODO: ACC add the appropriate description on this.
			n = DOM.add(o.iframeContainer, 'iframe', { 
				id : t.id + "_ifr",
				src : u || 'javascript:""', // Workaround for HTTPS warning in IE6/7
				frameBorder : '0',
				allowTransparency : "true",
				title : s.aria_label,
				style : {
					width : '100%',
					height : h,
					display : 'block' // Important for Gecko to render the iframe correctly
				}
			});

			t.contentAreaContainer = o.iframeContainer;
			DOM.get(o.editorContainer).style.display = t.orgDisplay;
			DOM.get(t.id).style.display = 'none';
			DOM.setAttrib(t.id, 'aria-hidden', true);

			if (!tinymce.relaxedDomain || !u)
				t.setupIframe();

			e = n = o = null; // Cleanup
		},

		setupIframe : function() {
			var t = this, s = t.settings, e = DOM.get(t.id), d = t.getDoc(), h, b;

			// Setup iframe body
			if (!isIE || !tinymce.relaxedDomain) {
				d.open();
				d.write(t.iframeHTML);
				d.close();

				if (tinymce.relaxedDomain)
					d.domain = tinymce.relaxedDomain;
			}

			// It will not steal focus while setting contentEditable
			b = t.getBody();
			b.disabled = true;

			if (!s.readonly)
				b.contentEditable = true;

			b.disabled = false;

			t.schema = new tinymce.html.Schema(s);

			t.dom = new tinymce.dom.DOMUtils(t.getDoc(), {
				keep_values : true,
				url_converter : t.convertURL,
				url_converter_scope : t,
				hex_colors : s.force_hex_style_colors,
				class_filter : s.class_filter,
				update_styles : 1,
				fix_ie_paragraphs : 1,
				schema : t.schema
			});

			t.parser = new tinymce.html.DomParser(s, t.schema);

			// Force anchor names closed, unless the setting "allow_html_in_named_anchor" is explicitly included.
			if (!t.settings.allow_html_in_named_anchor) {
				t.parser.addAttributeFilter('name', function(nodes, name) {
					var i = nodes.length, sibling, prevSibling, parent, node;
	
					while (i--) {
						node = nodes[i];
						if (node.name === 'a' && node.firstChild) {
							parent = node.parent;
	
							// Move children after current node
							sibling = node.lastChild;
							do {
								prevSibling = sibling.prev;
								parent.insert(sibling, node);
								sibling = prevSibling;
							} while (sibling);
						}
					}
				});
			}

			// Convert src and href into data-mce-src, data-mce-href and data-mce-style
			t.parser.addAttributeFilter('src,href,style', function(nodes, name) {
				var i = nodes.length, node, dom = t.dom, value, internalName;

				while (i--) {
					node = nodes[i];
					value = node.attr(name);
					internalName = 'data-mce-' + name;

					// Add internal attribute if we need to we don't on a refresh of the document
					if (!node.attributes.map[internalName]) {	
						if (name === "style")
							node.attr(internalName, dom.serializeStyle(dom.parseStyle(value), node.name));
						else
							node.attr(internalName, t.convertURL(value, name, node.name));
					}
				}
			});

			// Keep scripts from executing
			t.parser.addNodeFilter('script', function(nodes, name) {
				var i = nodes.length, node;

				while (i--) {
					node = nodes[i];
					node.attr('type', 'mce-' + (node.attr('type') || 'text/javascript'));
				}
			});

			t.parser.addNodeFilter('#cdata', function(nodes, name) {
				var i = nodes.length, node;

				while (i--) {
					node = nodes[i];
					node.type = 8;
					node.name = '#comment';
					node.value = '[CDATA[' + node.value + ']]';
				}
			});

			t.parser.addNodeFilter('p,h1,h2,h3,h4,h5,h6,div', function(nodes, name) {
				var i = nodes.length, node, nonEmptyElements = t.schema.getNonEmptyElements();

				while (i--) {
					node = nodes[i];

					if (node.isEmpty(nonEmptyElements))
						node.empty().append(new tinymce.html.Node('br', 1)).shortEnded = true;
				}
			});

			t.serializer = new tinymce.dom.Serializer(s, t.dom, t.schema);

			t.selection = new tinymce.dom.Selection(t.dom, t.getWin(), t.serializer);

			t.formatter = new tinymce.Formatter(this);

			// Register default formats
			t.formatter.register({
				alignleft : [
					{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'left'}},
					{selector : 'img,table', collapsed : false, styles : {'float' : 'left'}}
				],

				aligncenter : [
					{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'center'}},
					{selector : 'img', collapsed : false, styles : {display : 'block', marginLeft : 'auto', marginRight : 'auto'}},
					{selector : 'table', collapsed : false, styles : {marginLeft : 'auto', marginRight : 'auto'}}
				],

				alignright : [
					{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'right'}},
					{selector : 'img,table', collapsed : false, styles : {'float' : 'right'}}
				],

				alignfull : [
					{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'justify'}}
				],

				bold : [
					{inline : 'strong', remove : 'all'},
					{inline : 'span', styles : {fontWeight : 'bold'}},
					{inline : 'b', remove : 'all'}
				],

				italic : [
					{inline : 'em', remove : 'all'},
					{inline : 'span', styles : {fontStyle : 'italic'}},
					{inline : 'i', remove : 'all'}
				],

				underline : [
					{inline : 'span', styles : {textDecoration : 'underline'}, exact : true},
					{inline : 'u', remove : 'all'}
				],

				strikethrough : [
					{inline : 'span', styles : {textDecoration : 'line-through'}, exact : true},
					{inline : 'strike', remove : 'all'}
				],

				forecolor : {inline : 'span', styles : {color : '%value'}, wrap_links : false},
				hilitecolor : {inline : 'span', styles : {backgroundColor : '%value'}, wrap_links : false},
				fontname : {inline : 'span', styles : {fontFamily : '%value'}},
				fontsize : {inline : 'span', styles : {fontSize : '%value'}},
				fontsize_class : {inline : 'span', attributes : {'class' : '%value'}},
				blockquote : {block : 'blockquote', wrapper : 1, remove : 'all'},
				subscript : {inline : 'sub'},
				superscript : {inline : 'sup'},

				link : {inline : 'a', selector : 'a', remove : 'all', split : true, deep : true,
					onmatch : function(node) {
						return true;
					},

					onformat : function(elm, fmt, vars) {
						each(vars, function(value, key) {
							t.dom.setAttrib(elm, key, value);
						});
					}
				},

				removeformat : [
					{selector : 'b,strong,em,i,font,u,strike', remove : 'all', split : true, expand : false, block_expand : true, deep : true},
					{selector : 'span', attributes : ['style', 'class'], remove : 'empty', split : true, expand : false, deep : true},
					{selector : '*', attributes : ['style', 'class'], split : false, expand : false, deep : true}
				]
			});

			// Register default block formats
			each('p h1 h2 h3 h4 h5 h6 div address pre div code dt dd samp'.split(/\s/), function(name) {
				t.formatter.register(name, {block : name, remove : 'all'});
			});

			// Register user defined formats
			t.formatter.register(t.settings.formats);

			t.undoManager = new tinymce.UndoManager(t);

			// Pass through
			t.undoManager.onAdd.add(function(um, l) {
				if (um.hasUndo())
					return t.onChange.dispatch(t, l, um);
			});

			t.undoManager.onUndo.add(function(um, l) {
				return t.onUndo.dispatch(t, l, um);
			});

			t.undoManager.onRedo.add(function(um, l) {
				return t.onRedo.dispatch(t, l, um);
			});

			t.forceBlocks = new tinymce.ForceBlocks(t, {
				forced_root_block : s.forced_root_block
			});

			t.editorCommands = new tinymce.EditorCommands(t);

			// Pass through
			t.serializer.onPreProcess.add(function(se, o) {
				return t.onPreProcess.dispatch(t, o, se);
			});

			t.serializer.onPostProcess.add(function(se, o) {
				return t.onPostProcess.dispatch(t, o, se);
			});

			t.onPreInit.dispatch(t);

			if (!s.gecko_spellcheck)
				t.getBody().spellcheck = 0;

			if (!s.readonly)
				t._addEvents();

			t.controlManager.onPostRender.dispatch(t, t.controlManager);
			t.onPostRender.dispatch(t);

			t.quirks = new tinymce.util.Quirks(this);

			if (s.directionality)
				t.getBody().dir = s.directionality;

			if (s.nowrap)
				t.getBody().style.whiteSpace = "nowrap";

			if (s.handle_node_change_callback) {
				t.onNodeChange.add(function(ed, cm, n) {
					t.execCallback('handle_node_change_callback', t.id, n, -1, -1, true, t.selection.isCollapsed());
				});
			}

			if (s.save_callback) {
				t.onSaveContent.add(function(ed, o) {
					var h = t.execCallback('save_callback', t.id, o.content, t.getBody());

					if (h)
						o.content = h;
				});
			}

			if (s.onchange_callback) {
				t.onChange.add(function(ed, l) {
					t.execCallback('onchange_callback', t, l);
				});
			}

			if (s.protect) {
				t.onBeforeSetContent.add(function(ed, o) {
					if (s.protect) {
						each(s.protect, function(pattern) {
							o.content = o.content.replace(pattern, function(str) {
								return '<!--mce:protected ' + escape(str) + '-->';
							});
						});
					}
				});
			}

			if (s.convert_newlines_to_brs) {
				t.onBeforeSetContent.add(function(ed, o) {
					if (o.initial)
						o.content = o.content.replace(/\r?\n/g, '<br />');
				});
			}

			if (s.preformatted) {
				t.onPostProcess.add(function(ed, o) {
					o.content = o.content.replace(/^\s*<pre.*?>/, '');
					o.content = o.content.replace(/<\/pre>\s*$/, '');

					if (o.set)
						o.content = '<pre class="mceItemHidden">' + o.content + '</pre>';
				});
			}

			if (s.verify_css_classes) {
				t.serializer.attribValueFilter = function(n, v) {
					var s, cl;

					if (n == 'class') {
						// Build regexp for classes
						if (!t.classesRE) {
							cl = t.dom.getClasses();

							if (cl.length > 0) {
								s = '';

								each (cl, function(o) {
									s += (s ? '|' : '') + o['class'];
								});

								t.classesRE = new RegExp('(' + s + ')', 'gi');
							}
						}

						return !t.classesRE || /(\bmceItem\w+\b|\bmceTemp\w+\b)/g.test(v) || t.classesRE.test(v) ? v : '';
					}

					return v;
				};
			}

			if (s.cleanup_callback) {
				t.onBeforeSetContent.add(function(ed, o) {
					o.content = t.execCallback('cleanup_callback', 'insert_to_editor', o.content, o);
				});

				t.onPreProcess.add(function(ed, o) {
					if (o.set)
						t.execCallback('cleanup_callback', 'insert_to_editor_dom', o.node, o);

					if (o.get)
						t.execCallback('cleanup_callback', 'get_from_editor_dom', o.node, o);
				});

				t.onPostProcess.add(function(ed, o) {
					if (o.set)
						o.content = t.execCallback('cleanup_callback', 'insert_to_editor', o.content, o);

					if (o.get)						
						o.content = t.execCallback('cleanup_callback', 'get_from_editor', o.content, o);
				});
			}

			if (s.save_callback) {
				t.onGetContent.add(function(ed, o) {
					if (o.save)
						o.content = t.execCallback('save_callback', t.id, o.content, t.getBody());
				});
			}

			if (s.handle_event_callback) {
				t.onEvent.add(function(ed, e, o) {
					if (t.execCallback('handle_event_callback', e, ed, o) === false)
						Event.cancel(e);
				});
			}

			// Add visual aids when new contents is added
			t.onSetContent.add(function() {
				t.addVisual(t.getBody());
			});

			// Remove empty contents
			if (s.padd_empty_editor) {
				t.onPostProcess.add(function(ed, o) {
					o.content = o.content.replace(/^(<p[^>]*>(&nbsp;|&#160;|\s|\u00a0|)<\/p>[\r\n]*|<br \/>[\r\n]*)$/, '');
				});
			}

			if (isGecko) {
				// Fix gecko link bug, when a link is placed at the end of block elements there is
				// no way to move the caret behind the link. This fix adds a bogus br element after the link
				function fixLinks(ed, o) {
					each(ed.dom.select('a'), function(n) {
						var pn = n.parentNode;

						if (ed.dom.isBlock(pn) && pn.lastChild === n)
							ed.dom.add(pn, 'br', {'data-mce-bogus' : 1});
					});
				};

				t.onExecCommand.add(function(ed, cmd) {
					if (cmd === 'CreateLink')
						fixLinks(ed);
				});

				t.onSetContent.add(t.selection.onSetContent.add(fixLinks));
			}

			t.load({initial : true, format : 'html'});
			t.startContent = t.getContent({format : 'raw'});
			t.undoManager.add();
			t.initialized = true;

			t.onInit.dispatch(t);
			t.execCallback('setupcontent_callback', t.id, t.getBody(), t.getDoc());
			t.execCallback('init_instance_callback', t);
			t.focus(true);
			t.nodeChanged({initial : 1});

			// Load specified content CSS last
			each(t.contentCSS, function(u) {
				t.dom.loadCSS(u);
			});

			// Handle auto focus
			if (s.auto_focus) {
				setTimeout(function () {
					var ed = tinymce.get(s.auto_focus);

					ed.selection.select(ed.getBody(), 1);
					ed.selection.collapse(1);
					ed.getBody().focus();
					ed.getWin().focus();
				}, 100);
			}

			e = null;
		},


		focus : function(sf) {
			var oed, t = this, selection = t.selection, ce = t.settings.content_editable, ieRng, controlElm, doc = t.getDoc();

			if (!sf) {
				// Get selected control element
				ieRng = selection.getRng();
				if (ieRng.item) {
					controlElm = ieRng.item(0);
				}

				t._refreshContentEditable();
				selection.normalize();

				// Is not content editable
				if (!ce)
					t.getWin().focus();

				// Focus the body as well since it's contentEditable
				if (tinymce.isGecko) {
					t.getBody().focus();
				}

				// Restore selected control element
				// This is needed when for example an image is selected within a
				// layer a call to focus will then remove the control selection
				if (controlElm && controlElm.ownerDocument == doc) {
					ieRng = doc.body.createControlRange();
					ieRng.addElement(controlElm);
					ieRng.select();
				}

			}

			if (tinymce.activeEditor != t) {
				if ((oed = tinymce.activeEditor) != null)
					oed.onDeactivate.dispatch(oed, t);

				t.onActivate.dispatch(t, oed);
			}

			tinymce._setActive(t);
		},

		execCallback : function(n) {
			var t = this, f = t.settings[n], s;

			if (!f)
				return;

			// Look through lookup
			if (t.callbackLookup && (s = t.callbackLookup[n])) {
				f = s.func;
				s = s.scope;
			}

			if (is(f, 'string')) {
				s = f.replace(/\.\w+$/, '');
				s = s ? tinymce.resolve(s) : 0;
				f = tinymce.resolve(f);
				t.callbackLookup = t.callbackLookup || {};
				t.callbackLookup[n] = {func : f, scope : s};
			}

			return f.apply(s || t, Array.prototype.slice.call(arguments, 1));
		},

		translate : function(s) {
			var c = this.settings.language || 'en', i18n = tinymce.i18n;

			if (!s)
				return '';

			return i18n[c + '.' + s] || s.replace(/{\#([^}]+)\}/g, function(a, b) {
				return i18n[c + '.' + b] || '{#' + b + '}';
			});
		},

		getLang : function(n, dv) {
			return tinymce.i18n[(this.settings.language || 'en') + '.' + n] || (is(dv) ? dv : '{#' + n + '}');
		},

		getParam : function(n, dv, ty) {
			var tr = tinymce.trim, v = is(this.settings[n]) ? this.settings[n] : dv, o;

			if (ty === 'hash') {
				o = {};

				if (is(v, 'string')) {
					each(v.indexOf('=') > 0 ? v.split(/[;,](?![^=;,]*(?:[;,]|$))/) : v.split(','), function(v) {
						v = v.split('=');

						if (v.length > 1)
							o[tr(v[0])] = tr(v[1]);
						else
							o[tr(v[0])] = tr(v);
					});
				} else
					o = v;

				return o;
			}

			return v;
		},

		nodeChanged : function(o) {
			var t = this, s = t.selection, n = s.getStart() || t.getBody();

			// Fix for bug #1896577 it seems that this can not be fired while the editor is loading
			if (t.initialized) {
				o = o || {};
				n = isIE && n.ownerDocument != t.getDoc() ? t.getBody() : n; // Fix for IE initial state

				// Get parents and add them to object
				o.parents = [];
				t.dom.getParent(n, function(node) {
					if (node.nodeName == 'BODY')
						return true;

					o.parents.push(node);
				});

				t.onNodeChange.dispatch(
					t,
					o ? o.controlManager || t.controlManager : t.controlManager,
					n,
					s.isCollapsed(),
					o
				);
			}
		},

		addButton : function(n, s) {
			var t = this;

			t.buttons = t.buttons || {};
			t.buttons[n] = s;
		},

		addCommand : function(name, callback, scope) {
			this.execCommands[name] = {func : callback, scope : scope || this};
		},

		addQueryStateHandler : function(name, callback, scope) {
			this.queryStateCommands[name] = {func : callback, scope : scope || this};
		},

		addQueryValueHandler : function(name, callback, scope) {
			this.queryValueCommands[name] = {func : callback, scope : scope || this};
		},

		addShortcut : function(pa, desc, cmd_func, sc) {
			var t = this, c;

			if (!t.settings.custom_shortcuts)
				return false;

			t.shortcuts = t.shortcuts || {};

			if (is(cmd_func, 'string')) {
				c = cmd_func;

				cmd_func = function() {
					t.execCommand(c, false, null);
				};
			}

			if (is(cmd_func, 'object')) {
				c = cmd_func;

				cmd_func = function() {
					t.execCommand(c[0], c[1], c[2]);
				};
			}

			each(explode(pa), function(pa) {
				var o = {
					func : cmd_func,
					scope : sc || this,
					desc : desc,
					alt : false,
					ctrl : false,
					shift : false
				};

				each(explode(pa, '+'), function(v) {
					switch (v) {
						case 'alt':
						case 'ctrl':
						case 'shift':
							o[v] = true;
							break;

						default:
							o.charCode = v.charCodeAt(0);
							o.keyCode = v.toUpperCase().charCodeAt(0);
					}
				});

				t.shortcuts[(o.ctrl ? 'ctrl' : '') + ',' + (o.alt ? 'alt' : '') + ',' + (o.shift ? 'shift' : '') + ',' + o.keyCode] = o;
			});

			return true;
		},

		execCommand : function(cmd, ui, val, a) {
			var t = this, s = 0, o, st;

			if (!/^(mceAddUndoLevel|mceEndUndoLevel|mceBeginUndoLevel|mceRepaint|SelectAll)$/.test(cmd) && (!a || !a.skip_focus))
				t.focus();

			o = {};
			t.onBeforeExecCommand.dispatch(t, cmd, ui, val, o);
			if (o.terminate)
				return false;

			// Command callback
			if (t.execCallback('execcommand_callback', t.id, t.selection.getNode(), cmd, ui, val)) {
				t.onExecCommand.dispatch(t, cmd, ui, val, a);
				return true;
			}

			// Registred commands
			if (o = t.execCommands[cmd]) {
				st = o.func.call(o.scope, ui, val);

				// Fall through on true
				if (st !== true) {
					t.onExecCommand.dispatch(t, cmd, ui, val, a);
					return st;
				}
			}

			// Plugin commands
			each(t.plugins, function(p) {
				if (p.execCommand && p.execCommand(cmd, ui, val)) {
					t.onExecCommand.dispatch(t, cmd, ui, val, a);
					s = 1;
					return false;
				}
			});

			if (s)
				return true;

			// Theme commands
			if (t.theme && t.theme.execCommand && t.theme.execCommand(cmd, ui, val)) {
				t.onExecCommand.dispatch(t, cmd, ui, val, a);
				return true;
			}

			// Editor commands
			if (t.editorCommands.execCommand(cmd, ui, val)) {
				t.onExecCommand.dispatch(t, cmd, ui, val, a);
				return true;
			}

			// Browser commands
			t.getDoc().execCommand(cmd, ui, val);
			t.onExecCommand.dispatch(t, cmd, ui, val, a);
		},

		queryCommandState : function(cmd) {
			var t = this, o, s;

			// Is hidden then return undefined
			if (t._isHidden())
				return;

			// Registred commands
			if (o = t.queryStateCommands[cmd]) {
				s = o.func.call(o.scope);

				// Fall though on true
				if (s !== true)
					return s;
			}

			// Registred commands
			o = t.editorCommands.queryCommandState(cmd);
			if (o !== -1)
				return o;

			// Browser commands
			try {
				return this.getDoc().queryCommandState(cmd);
			} catch (ex) {
				// Fails sometimes see bug: 1896577
			}
		},

		queryCommandValue : function(c) {
			var t = this, o, s;

			// Is hidden then return undefined
			if (t._isHidden())
				return;

			// Registred commands
			if (o = t.queryValueCommands[c]) {
				s = o.func.call(o.scope);

				// Fall though on true
				if (s !== true)
					return s;
			}

			// Registred commands
			o = t.editorCommands.queryCommandValue(c);
			if (is(o))
				return o;

			// Browser commands
			try {
				return this.getDoc().queryCommandValue(c);
			} catch (ex) {
				// Fails sometimes see bug: 1896577
			}
		},

		show : function() {
			var t = this;

			DOM.show(t.getContainer());
			DOM.hide(t.id);
			t.load();
		},

		hide : function() {
			var t = this, d = t.getDoc();

			// Fixed bug where IE has a blinking cursor left from the editor
			if (isIE && d)
				d.execCommand('SelectAll');

			// We must save before we hide so Safari doesn't crash
			t.save();
			DOM.hide(t.getContainer());
			DOM.setStyle(t.id, 'display', t.orgDisplay);
		},

		isHidden : function() {
			return !DOM.isHidden(this.id);
		},

		setProgressState : function(b, ti, o) {
			this.onSetProgressState.dispatch(this, b, ti, o);

			return b;
		},

		load : function(o) {
			var t = this, e = t.getElement(), h;

			if (e) {
				o = o || {};
				o.load = true;

				// Double encode existing entities in the value
				h = t.setContent(is(e.value) ? e.value : e.innerHTML, o);
				o.element = e;

				if (!o.no_events)
					t.onLoadContent.dispatch(t, o);

				o.element = e = null;

				return h;
			}
		},

		save : function(o) {
			var t = this, e = t.getElement(), h, f;

			if (!e || !t.initialized)
				return;

			o = o || {};
			o.save = true;

			// Add undo level will trigger onchange event
			if (!o.no_events) {
				t.undoManager.typing = false;
				t.undoManager.add();
			}

			o.element = e;
			h = o.content = t.getContent(o);

			if (!o.no_events)
				t.onSaveContent.dispatch(t, o);

			h = o.content;

			if (!/TEXTAREA|INPUT/i.test(e.nodeName)) {
				e.innerHTML = h;

				// Update hidden form element
				if (f = DOM.getParent(t.id, 'form')) {
					each(f.elements, function(e) {
						if (e.name == t.id) {
							e.value = h;
							return false;
						}
					});
				}
			} else
				e.value = h;

			o.element = e = null;

			return h;
		},

		setContent : function(content, args) {
			var self = this, rootNode, body = self.getBody(), forcedRootBlockName;

			// Setup args object
			args = args || {};
			args.format = args.format || 'html';
			args.set = true;
			args.content = content;

			// Do preprocessing
			if (!args.no_events)
				self.onBeforeSetContent.dispatch(self, args);

			content = args.content;

			// Padd empty content in Gecko and Safari. Commands will otherwise fail on the content
			// It will also be impossible to place the caret in the editor unless there is a BR element present
			if (!tinymce.isIE && (content.length === 0 || /^\s+$/.test(content))) {
				forcedRootBlockName = self.settings.forced_root_block;
				if (forcedRootBlockName)
					content = '<' + forcedRootBlockName + '><br data-mce-bogus="1"></' + forcedRootBlockName + '>';
				else
					content = '<br data-mce-bogus="1">';

				body.innerHTML = content;
				self.selection.select(body, true);
				self.selection.collapse(true);
				return;
			}

			// Parse and serialize the html
			if (args.format !== 'raw') {
				content = new tinymce.html.Serializer({}, self.schema).serialize(
					self.parser.parse(content)
				);
			}

			// Set the new cleaned contents to the editor
			args.content = tinymce.trim(content);
			self.dom.setHTML(body, args.content);

			// Do post processing
			if (!args.no_events)
				self.onSetContent.dispatch(self, args);

			self.selection.normalize();

			return args.content;
		},

		getContent : function(args) {
			var self = this, content;

			// Setup args object
			args = args || {};
			args.format = args.format || 'html';
			args.get = true;

			// Do preprocessing
			if (!args.no_events)
				self.onBeforeGetContent.dispatch(self, args);

			// Get raw contents or by default the cleaned contents
			if (args.format == 'raw')
				content = self.getBody().innerHTML;
			else
				content = self.serializer.serialize(self.getBody(), args);

			args.content = tinymce.trim(content);

			// Do post processing
			if (!args.no_events)
				self.onGetContent.dispatch(self, args);

			return args.content;
		},

		isDirty : function() {
			var self = this;

			return tinymce.trim(self.startContent) != tinymce.trim(self.getContent({format : 'raw', no_events : 1})) && !self.isNotDirty;
		},

		getContainer : function() {
			var t = this;

			if (!t.container)
				t.container = DOM.get(t.editorContainer || t.id + '_parent');

			return t.container;
		},

		getContentAreaContainer : function() {
			return this.contentAreaContainer;
		},

		getElement : function() {
			return DOM.get(this.settings.content_element || this.id);
		},

		getWin : function() {
			var t = this, e;

			if (!t.contentWindow) {
				e = DOM.get(t.id + "_ifr");

				if (e)
					t.contentWindow = e.contentWindow;
			}

			return t.contentWindow;
		},

		getDoc : function() {
			var t = this, w;

			if (!t.contentDocument) {
				w = t.getWin();

				if (w)
					t.contentDocument = w.document;
			}

			return t.contentDocument;
		},

		getBody : function() {
			return this.bodyElement || this.getDoc().body;
		},

		convertURL : function(u, n, e) {
			var t = this, s = t.settings;

			// Use callback instead
			if (s.urlconverter_callback)
				return t.execCallback('urlconverter_callback', u, e, true, n);

			// Don't convert link href since thats the CSS files that gets loaded into the editor also skip local file URLs
			if (!s.convert_urls || (e && e.nodeName == 'LINK') || u.indexOf('file:') === 0)
				return u;

			// Convert to relative
			if (s.relative_urls)
				return t.documentBaseURI.toRelative(u);

			// Convert to absolute
			u = t.documentBaseURI.toAbsolute(u, s.remove_script_host);

			return u;
		},

		addVisual : function(e) {
			var t = this, s = t.settings;

			e = e || t.getBody();

			if (!is(t.hasVisual))
				t.hasVisual = s.visual;

			each(t.dom.select('table,a', e), function(e) {
				var v;

				switch (e.nodeName) {
					case 'TABLE':
						v = t.dom.getAttrib(e, 'border');

						if (!v || v == '0') {
							if (t.hasVisual)
								t.dom.addClass(e, s.visual_table_class);
							else
								t.dom.removeClass(e, s.visual_table_class);
						}

						return;

					case 'A':
						v = t.dom.getAttrib(e, 'name');

						if (v) {
							if (t.hasVisual)
								t.dom.addClass(e, 'mceItemAnchor');
							else
								t.dom.removeClass(e, 'mceItemAnchor');
						}

						return;
				}
			});

			t.onVisualAid.dispatch(t, e, t.hasVisual);
		},

		remove : function() {
			var t = this, e = t.getContainer();

			t.removed = 1; // Cancels post remove event execution
			t.hide();

			t.execCallback('remove_instance_callback', t);
			t.onRemove.dispatch(t);

			// Clear all execCommand listeners this is required to avoid errors if the editor was removed inside another command
			t.onExecCommand.listeners = [];

			tinymce.remove(t);
			DOM.remove(e);
		},

		destroy : function(s) {
			var t = this;

			// One time is enough
			if (t.destroyed)
				return;

			if (!s) {
				tinymce.removeUnload(t.destroy);
				tinyMCE.onBeforeUnload.remove(t._beforeUnload);

				// Manual destroy
				if (t.theme && t.theme.destroy)
					t.theme.destroy();

				// Destroy controls, selection and dom
				t.controlManager.destroy();
				t.selection.destroy();
				t.dom.destroy();

				// Remove all events

				// Don't clear the window or document if content editable
				// is enabled since other instances might still be present
				if (!t.settings.content_editable) {
					Event.clear(t.getWin());
					Event.clear(t.getDoc());
				}

				Event.clear(t.getBody());
				Event.clear(t.formElement);
			}

			if (t.formElement) {
				t.formElement.submit = t.formElement._mceOldSubmit;
				t.formElement._mceOldSubmit = null;
			}

			t.contentAreaContainer = t.formElement = t.container = t.settings.content_element = t.bodyElement = t.contentDocument = t.contentWindow = null;

			if (t.selection)
				t.selection = t.selection.win = t.selection.dom = t.selection.dom.doc = null;

			t.destroyed = 1;
		},

		// Internal functions

		_addEvents : function() {
			// 'focus', 'blur', 'dblclick', 'beforedeactivate', submit, reset
			var t = this, i, s = t.settings, dom = t.dom, lo = {
				mouseup : 'onMouseUp',
				mousedown : 'onMouseDown',
				click : 'onClick',
				keyup : 'onKeyUp',
				keydown : 'onKeyDown',
				keypress : 'onKeyPress',
				submit : 'onSubmit',
				reset : 'onReset',
				contextmenu : 'onContextMenu',
				dblclick : 'onDblClick',
				paste : 'onPaste' // Doesn't work in all browsers yet
			};

			function eventHandler(e, o) {
				var ty = e.type;

				// Don't fire events when it's removed
				if (t.removed)
					return;

				// Generic event handler
				if (t.onEvent.dispatch(t, e, o) !== false) {
					// Specific event handler
					t[lo[e.fakeType || e.type]].dispatch(t, e, o);
				}
			};

			// Add DOM events
			each(lo, function(v, k) {
				switch (k) {
					case 'contextmenu':
						dom.bind(t.getDoc(), k, eventHandler);
						break;

					case 'paste':
						dom.bind(t.getBody(), k, function(e) {
							eventHandler(e);
						});
						break;

					case 'submit':
					case 'reset':
						dom.bind(t.getElement().form || DOM.getParent(t.id, 'form'), k, eventHandler);
						break;

					default:
						dom.bind(s.content_editable ? t.getBody() : t.getDoc(), k, eventHandler);
				}
			});

			dom.bind(s.content_editable ? t.getBody() : (isGecko ? t.getDoc() : t.getWin()), 'focus', function(e) {
				t.focus(true);
			});


			// Fixes bug where a specified document_base_uri could result in broken images
			// This will also fix drag drop of images in Gecko
			if (tinymce.isGecko) {
				dom.bind(t.getDoc(), 'DOMNodeInserted', function(e) {
					var v;

					e = e.target;

					if (e.nodeType === 1 && e.nodeName === 'IMG' && (v = e.getAttribute('data-mce-src')))
						e.src = t.documentBaseURI.toAbsolute(v);
				});
			}

			// Set various midas options in Gecko
			if (isGecko) {
				function setOpts() {
					var t = this, d = t.getDoc(), s = t.settings;

					if (isGecko && !s.readonly) {
						t._refreshContentEditable();

						try {
							// Try new Gecko method
							d.execCommand("styleWithCSS", 0, false);
						} catch (ex) {
							// Use old method
							if (!t._isHidden())
								try {d.execCommand("useCSS", 0, true);} catch (ex) {}
						}

						if (!s.table_inline_editing)
							try {d.execCommand('enableInlineTableEditing', false, false);} catch (ex) {}

						if (!s.object_resizing)
							try {d.execCommand('enableObjectResizing', false, false);} catch (ex) {}
					}
				};

				t.onBeforeExecCommand.add(setOpts);
				t.onMouseDown.add(setOpts);
			}

			// Add node change handlers
			t.onMouseUp.add(t.nodeChanged);
			//t.onClick.add(t.nodeChanged);
			t.onKeyUp.add(function(ed, e) {
				var c = e.keyCode;

				if ((c >= 33 && c <= 36) || (c >= 37 && c <= 40) || c == 13 || c == 45 || c == 46 || c == 8 || (tinymce.isMac && (c == 91 || c == 93)) || e.ctrlKey)
					t.nodeChanged();
			});


			// Add block quote deletion handler
			t.onKeyDown.add(function(ed, e) {
				// Was the BACKSPACE key pressed?
				if (e.keyCode != 8)
					return;

				var n = ed.selection.getRng().startContainer;
				var offset = ed.selection.getRng().startOffset;

				while (n && n.nodeType && n.nodeType != 1 && n.parentNode)
					n = n.parentNode;
					
				// Is the cursor at the beginning of a blockquote?
				if (n && n.parentNode && n.parentNode.tagName === 'BLOCKQUOTE' && n.parentNode.firstChild == n && offset == 0) {
					// Remove the blockquote
					ed.formatter.toggle('blockquote', null, n.parentNode);

					// Move the caret to the beginning of n
					var rng = ed.selection.getRng();
					rng.setStart(n, 0);
					rng.setEnd(n, 0);
					ed.selection.setRng(rng);
					ed.selection.collapse(false);
				}
			});
 


			// Add reset handler
			t.onReset.add(function() {
				t.setContent(t.startContent, {format : 'raw'});
			});

			// Add shortcuts
			if (s.custom_shortcuts) {
				if (s.custom_undo_redo_keyboard_shortcuts) {
					t.addShortcut('ctrl+z', t.getLang('undo_desc'), 'Undo');
					t.addShortcut('ctrl+y', t.getLang('redo_desc'), 'Redo');
				}

				// Add default shortcuts for gecko
				t.addShortcut('ctrl+b', t.getLang('bold_desc'), 'Bold');
				t.addShortcut('ctrl+i', t.getLang('italic_desc'), 'Italic');
				t.addShortcut('ctrl+u', t.getLang('underline_desc'), 'Underline');

				// BlockFormat shortcuts keys
				for (i=1; i<=6; i++)
					t.addShortcut('ctrl+' + i, '', ['FormatBlock', false, 'h' + i]);

				t.addShortcut('ctrl+7', '', ['FormatBlock', false, 'p']);
				t.addShortcut('ctrl+8', '', ['FormatBlock', false, 'div']);
				t.addShortcut('ctrl+9', '', ['FormatBlock', false, 'address']);

				function find(e) {
					var v = null;

					if (!e.altKey && !e.ctrlKey && !e.metaKey)
						return v;

					each(t.shortcuts, function(o) {
						if (tinymce.isMac && o.ctrl != e.metaKey)
							return;
						else if (!tinymce.isMac && o.ctrl != e.ctrlKey)
							return;

						if (o.alt != e.altKey)
							return;

						if (o.shift != e.shiftKey)
							return;

						if (e.keyCode == o.keyCode || (e.charCode && e.charCode == o.charCode)) {
							v = o;
							return false;
						}
					});

					return v;
				};

				t.onKeyUp.add(function(ed, e) {
					var o = find(e);

					if (o)
						return Event.cancel(e);
				});

				t.onKeyPress.add(function(ed, e) {
					var o = find(e);

					if (o)
						return Event.cancel(e);
				});

				t.onKeyDown.add(function(ed, e) {
					var o = find(e);

					if (o) {
						o.func.call(o.scope);
						return Event.cancel(e);
					}
				});
			}

			if (tinymce.isIE) {
				// Fix so resize will only update the width and height attributes not the styles of an image
				// It will also block mceItemNoResize items
				dom.bind(t.getDoc(), 'controlselect', function(e) {
					var re = t.resizeInfo, cb;

					e = e.target;

					// Don't do this action for non image elements
					if (e.nodeName !== 'IMG')
						return;

					if (re)
						dom.unbind(re.node, re.ev, re.cb);

					if (!dom.hasClass(e, 'mceItemNoResize')) {
						ev = 'resizeend';
						cb = dom.bind(e, ev, function(e) {
							var v;

							e = e.target;

							if (v = dom.getStyle(e, 'width')) {
								dom.setAttrib(e, 'width', v.replace(/[^0-9%]+/g, ''));
								dom.setStyle(e, 'width', '');
							}

							if (v = dom.getStyle(e, 'height')) {
								dom.setAttrib(e, 'height', v.replace(/[^0-9%]+/g, ''));
								dom.setStyle(e, 'height', '');
							}
						});
					} else {
						ev = 'resizestart';
						cb = dom.bind(e, 'resizestart', Event.cancel, Event);
					}

					re = t.resizeInfo = {
						node : e,
						ev : ev,
						cb : cb
					};
				});
			}

			if (tinymce.isOpera) {
				t.onClick.add(function(ed, e) {
					Event.prevent(e);
				});
			}

			// Add custom undo/redo handlers
			if (s.custom_undo_redo) {
				function addUndo() {
					t.undoManager.typing = false;
					t.undoManager.add();
				};

				dom.bind(t.getDoc(), 'focusout', function(e) {
					if (!t.removed && t.undoManager.typing)
						addUndo();
				});

				// Add undo level when contents is drag/dropped within the editor
				t.dom.bind(t.dom.getRoot(), 'dragend', function(e) {
					addUndo();
				});

				t.onKeyUp.add(function(ed, e) {
					var keyCode = e.keyCode;

					if ((keyCode >= 33 && keyCode <= 36) || (keyCode >= 37 && keyCode <= 40) || keyCode == 13 || keyCode == 45 || e.ctrlKey)
						addUndo();
				});

				t.onKeyDown.add(function(ed, e) {
					var keyCode = e.keyCode, sel;

					if (keyCode == 8) {
						sel = t.getDoc().selection;

						// Fix IE control + backspace browser bug
						if (sel && sel.createRange && sel.createRange().item) {
							t.undoManager.beforeChange();
							ed.dom.remove(sel.createRange().item(0));
							addUndo();

							return Event.cancel(e);
						}
					}

					// Is caracter positon keys left,right,up,down,home,end,pgdown,pgup,enter
					if ((keyCode >= 33 && keyCode <= 36) || (keyCode >= 37 && keyCode <= 40) || keyCode == 13 || keyCode == 45) {
						// Add position before enter key is pressed, used by IE since it still uses the default browser behavior
						// Todo: Remove this once we normalize enter behavior on IE
						if (tinymce.isIE && keyCode == 13)
							t.undoManager.beforeChange();

						if (t.undoManager.typing)
							addUndo();

						return;
					}

					// If key isn't shift,ctrl,alt,capslock,metakey
					if ((keyCode < 16 || keyCode > 20) && keyCode != 224 && keyCode != 91 && !t.undoManager.typing) {
						t.undoManager.beforeChange();
						t.undoManager.typing = true;
						t.undoManager.add();
					}
				});

				t.onMouseDown.add(function() {
					if (t.undoManager.typing)
						addUndo();
				});
			}

			// Bug fix for FireFox keeping styles from end of selection instead of start.
			if (tinymce.isGecko) {
				function getAttributeApplyFunction() {
					var template = t.dom.getAttribs(t.selection.getStart().cloneNode(false));

					return function() {
						var target = t.selection.getStart();

						if (target !== t.getBody()) {
							t.dom.setAttrib(target, "style", null);

							each(template, function(attr) {
								target.setAttributeNode(attr.cloneNode(true));
							});
						}
					};
				}

				function isSelectionAcrossElements() {
					var s = t.selection;

					return !s.isCollapsed() && s.getStart() != s.getEnd();
				}

				t.onKeyPress.add(function(ed, e) {
					var applyAttributes;

					if ((e.keyCode == 8 || e.keyCode == 46) && isSelectionAcrossElements()) {
						applyAttributes = getAttributeApplyFunction();
						t.getDoc().execCommand('delete', false, null);
						applyAttributes();

						return Event.cancel(e);
					}
				});

				t.dom.bind(t.getDoc(), 'cut', function(e) {
					var applyAttributes;

					if (isSelectionAcrossElements()) {
						applyAttributes = getAttributeApplyFunction();
						t.onKeyUp.addToTop(Event.cancel, Event);

						setTimeout(function() {
							applyAttributes();
							t.onKeyUp.remove(Event.cancel, Event);
						}, 0);
					}
				});
			}
		},

		_refreshContentEditable : function() {
			var self = this, body, parent;

			// Check if the editor was hidden and the re-initalize contentEditable mode by removing and adding the body again
			if (self._isHidden()) {
				body = self.getBody();
				parent = body.parentNode;

				parent.removeChild(body);
				parent.appendChild(body);

				body.focus();
			}
		},

		_isHidden : function() {
			var s;

			if (!isGecko)
				return 0;

			// Weird, wheres that cursor selection?
			s = this.selection.getSel();
			return (!s || !s.rangeCount || s.rangeCount == 0);
		}
	});
})(tinymce);

(function(tinymce) {
	// Added for compression purposes
	var each = tinymce.each, undefined, TRUE = true, FALSE = false;

	tinymce.EditorCommands = function(editor) {
		var dom = editor.dom,
			selection = editor.selection,
			commands = {state: {}, exec : {}, value : {}},
			settings = editor.settings,
			formatter = editor.formatter,
			bookmark;

		function execCommand(command, ui, value) {
			var func;

			command = command.toLowerCase();
			if (func = commands.exec[command]) {
				func(command, ui, value);
				return TRUE;
			}

			return FALSE;
		};

		function queryCommandState(command) {
			var func;

			command = command.toLowerCase();
			if (func = commands.state[command])
				return func(command);

			return -1;
		};

		function queryCommandValue(command) {
			var func;

			command = command.toLowerCase();
			if (func = commands.value[command])
				return func(command);

			return FALSE;
		};

		function addCommands(command_list, type) {
			type = type || 'exec';

			each(command_list, function(callback, command) {
				each(command.toLowerCase().split(','), function(command) {
					commands[type][command] = callback;
				});
			});
		};

		// Expose public methods
		tinymce.extend(this, {
			execCommand : execCommand,
			queryCommandState : queryCommandState,
			queryCommandValue : queryCommandValue,
			addCommands : addCommands
		});

		// Private methods

		function execNativeCommand(command, ui, value) {
			if (ui === undefined)
				ui = FALSE;

			if (value === undefined)
				value = null;

			return editor.getDoc().execCommand(command, ui, value);
		};

		function isFormatMatch(name) {
			return formatter.match(name);
		};

		function toggleFormat(name, value) {
			formatter.toggle(name, value ? {value : value} : undefined);
		};

		function storeSelection(type) {
			bookmark = selection.getBookmark(type);
		};

		function restoreSelection() {
			selection.moveToBookmark(bookmark);
		};

		// Add execCommand overrides
		addCommands({
			// Ignore these, added for compatibility
			'mceResetDesignMode,mceBeginUndoLevel' : function() {},

			// Add undo manager logic
			'mceEndUndoLevel,mceAddUndoLevel' : function() {
				editor.undoManager.add();
			},

			'Cut,Copy,Paste' : function(command) {
				var doc = editor.getDoc(), failed;

				// Try executing the native command
				try {
					execNativeCommand(command);
				} catch (ex) {
					// Command failed
					failed = TRUE;
				}

				// Present alert message about clipboard access not being available
				if (failed || !doc.queryCommandSupported(command)) {
					if (tinymce.isGecko) {
						editor.windowManager.confirm(editor.getLang('clipboard_msg'), function(state) {
							if (state)
								open('http://www.mozilla.org/editor/midasdemo/securityprefs.html', '_blank');
						});
					} else
						editor.windowManager.alert(editor.getLang('clipboard_no_support'));
				}
			},

			// Override unlink command
			unlink : function(command) {
				if (selection.isCollapsed())
					selection.select(selection.getNode());

				execNativeCommand(command);
				selection.collapse(FALSE);
			},

			// Override justify commands to use the text formatter engine
			'JustifyLeft,JustifyCenter,JustifyRight,JustifyFull' : function(command) {
				var align = command.substring(7);

				// Remove all other alignments first
				each('left,center,right,full'.split(','), function(name) {
					if (align != name)
						formatter.remove('align' + name);
				});

				toggleFormat('align' + align);
				execCommand('mceRepaint');
			},

			// Override list commands to fix WebKit bug
			'InsertUnorderedList,InsertOrderedList' : function(command) {
				var listElm, listParent;

				execNativeCommand(command);

				// WebKit produces lists within block elements so we need to split them
				// we will replace the native list creation logic to custom logic later on
				// TODO: Remove this when the list creation logic is removed
				listElm = dom.getParent(selection.getNode(), 'ol,ul');
				if (listElm) {
					listParent = listElm.parentNode;

					// If list is within a text block then split that block
					if (/^(H[1-6]|P|ADDRESS|PRE)$/.test(listParent.nodeName)) {
						storeSelection();
						dom.split(listParent, listElm);
						restoreSelection();
					}
				}
			},

			// Override commands to use the text formatter engine
			'Bold,Italic,Underline,Strikethrough,Superscript,Subscript' : function(command) {
				toggleFormat(command);
			},

			// Override commands to use the text formatter engine
			'ForeColor,HiliteColor,FontName' : function(command, ui, value) {
				toggleFormat(command, value);
			},

			FontSize : function(command, ui, value) {
				var fontClasses, fontSizes;

				// Convert font size 1-7 to styles
				if (value >= 1 && value <= 7) {
					fontSizes = tinymce.explode(settings.font_size_style_values);
					fontClasses = tinymce.explode(settings.font_size_classes);

					if (fontClasses)
						value = fontClasses[value - 1] || value;
					else
						value = fontSizes[value - 1] || value;
				}

				toggleFormat(command, value);
			},

			RemoveFormat : function(command) {
				formatter.remove(command);
			},

			mceBlockQuote : function(command) {
				toggleFormat('blockquote');
			},

			FormatBlock : function(command, ui, value) {
				return toggleFormat(value || 'p');
			},

			mceCleanup : function() {
				var bookmark = selection.getBookmark();

				editor.setContent(editor.getContent({cleanup : TRUE}), {cleanup : TRUE});

				selection.moveToBookmark(bookmark);
			},

			mceRemoveNode : function(command, ui, value) {
				var node = value || selection.getNode();

				// Make sure that the body node isn't removed
				if (node != editor.getBody()) {
					storeSelection();
					editor.dom.remove(node, TRUE);
					restoreSelection();
				}
			},

			mceSelectNodeDepth : function(command, ui, value) {
				var counter = 0;

				dom.getParent(selection.getNode(), function(node) {
					if (node.nodeType == 1 && counter++ == value) {
						selection.select(node);
						return FALSE;
					}
				}, editor.getBody());
			},

			mceSelectNode : function(command, ui, value) {
				selection.select(value);
			},

			mceInsertContent : function(command, ui, value) {
				var parser, serializer, parentNode, rootNode, fragment, args,
					marker, nodeRect, viewPortRect, rng, node, node2, bookmarkHtml, viewportBodyElement;

				// Setup parser and serializer
				parser = editor.parser;
				serializer = new tinymce.html.Serializer({}, editor.schema);
				bookmarkHtml = '<span id="mce_marker" data-mce-type="bookmark">\uFEFF</span>';

				// Run beforeSetContent handlers on the HTML to be inserted
				args = {content: value, format: 'html'};
				selection.onBeforeSetContent.dispatch(selection, args);
				value = args.content;

				// Add caret at end of contents if it's missing
				if (value.indexOf('{$caret}') == -1)
					value += '{$caret}';

				// Replace the caret marker with a span bookmark element
				value = value.replace(/\{\$caret\}/, bookmarkHtml);

				// Insert node maker where we will insert the new HTML and get it's parent
				if (!selection.isCollapsed())
					editor.getDoc().execCommand('Delete', false, null);

				parentNode = selection.getNode();

				// Parse the fragment within the context of the parent node
				args = {context : parentNode.nodeName.toLowerCase()};
				fragment = parser.parse(value, args);

				// Move the caret to a more suitable location
				node = fragment.lastChild;
				if (node.attr('id') == 'mce_marker') {
					marker = node;

					for (node = node.prev; node; node = node.walk(true)) {
						if (node.type == 3 || !dom.isBlock(node.name)) {
							node.parent.insert(marker, node, node.name === 'br');
							break;
						}
					}
				}

				// If parser says valid we can insert the contents into that parent
				if (!args.invalid) {
					value = serializer.serialize(fragment);

					// Check if parent is empty or only has one BR element then set the innerHTML of that parent
					node = parentNode.firstChild;
					node2 = parentNode.lastChild;
					if (!node || (node === node2 && node.nodeName === 'BR'))
						dom.setHTML(parentNode, value);
					else
						selection.setContent(value);
				} else {
					// If the fragment was invalid within that context then we need
					// to parse and process the parent it's inserted into

					// Insert bookmark node and get the parent
					selection.setContent(bookmarkHtml);
					parentNode = editor.selection.getNode();
					rootNode = editor.getBody();

					// Opera will return the document node when selection is in root
					if (parentNode.nodeType == 9)
						parentNode = node = rootNode;
					else
						node = parentNode;

					// Find the ancestor just before the root element
					while (node !== rootNode) {
						parentNode = node;
						node = node.parentNode;
					}

					// Get the outer/inner HTML depending on if we are in the root and parser and serialize that
					value = parentNode == rootNode ? rootNode.innerHTML : dom.getOuterHTML(parentNode);
					value = serializer.serialize(
						parser.parse(
							// Need to replace by using a function since $ in the contents would otherwise be a problem
							value.replace(/<span (id="mce_marker"|id=mce_marker).+?<\/span>/i, function() {
								return serializer.serialize(fragment);
							})
						)
					);

					// Set the inner/outer HTML depending on if we are in the root or not
					if (parentNode == rootNode)
						dom.setHTML(rootNode, value);
					else
						dom.setOuterHTML(parentNode, value);
				}

				marker = dom.get('mce_marker');

				// Scroll range into view scrollIntoView on element can't be used since it will scroll the main view port as well
				nodeRect = dom.getRect(marker);
				viewPortRect = dom.getViewPort(editor.getWin());

				// Check if node is out side the viewport if it is then scroll to it
				if ((nodeRect.y + nodeRect.h > viewPortRect.y + viewPortRect.h || nodeRect.y < viewPortRect.y) ||
					(nodeRect.x > viewPortRect.x + viewPortRect.w || nodeRect.x < viewPortRect.x)) {
					viewportBodyElement = tinymce.isIE ? editor.getDoc().documentElement : editor.getBody();
					viewportBodyElement.scrollLeft = nodeRect.x;
					viewportBodyElement.scrollTop = nodeRect.y - viewPortRect.h + 25;
				}

				// Move selection before marker and remove it
				rng = dom.createRng();

				// If previous sibling is a text node set the selection to the end of that node
				node = marker.previousSibling;
				if (node && node.nodeType == 3) {
					rng.setStart(node, node.nodeValue.length);
				} else {
					// If the previous sibling isn't a text node or doesn't exist set the selection before the marker node
					rng.setStartBefore(marker);
					rng.setEndBefore(marker);
				}

				// Remove the marker node and set the new range
				dom.remove(marker);
				selection.setRng(rng);

				// Dispatch after event and add any visual elements needed
				selection.onSetContent.dispatch(selection, args);
				editor.addVisual();
			},

			mceInsertRawHTML : function(command, ui, value) {
				selection.setContent('tiny_mce_marker');
				editor.setContent(editor.getContent().replace(/tiny_mce_marker/g, function() { return value }));
			},

			mceSetContent : function(command, ui, value) {
				editor.setContent(value);
			},

			'Indent,Outdent' : function(command) {
				var intentValue, indentUnit, value;

				// Setup indent level
				intentValue = settings.indentation;
				indentUnit = /[a-z%]+$/i.exec(intentValue);
				intentValue = parseInt(intentValue);

				if (!queryCommandState('InsertUnorderedList') && !queryCommandState('InsertOrderedList')) {
					each(selection.getSelectedBlocks(), function(element) {
						if (command == 'outdent') {
							value = Math.max(0, parseInt(element.style.paddingLeft || 0) - intentValue);
							dom.setStyle(element, 'paddingLeft', value ? value + indentUnit : '');
						} else
							dom.setStyle(element, 'paddingLeft', (parseInt(element.style.paddingLeft || 0) + intentValue) + indentUnit);
					});
				} else
					execNativeCommand(command);
			},

			mceRepaint : function() {
				var bookmark;

				if (tinymce.isGecko) {
					try {
						storeSelection(TRUE);

						if (selection.getSel())
							selection.getSel().selectAllChildren(editor.getBody());

						selection.collapse(TRUE);
						restoreSelection();
					} catch (ex) {
						// Ignore
					}
				}
			},

			mceToggleFormat : function(command, ui, value) {
				formatter.toggle(value);
			},

			InsertHorizontalRule : function() {
				editor.execCommand('mceInsertContent', false, '<hr />');
			},

			mceToggleVisualAid : function() {
				editor.hasVisual = !editor.hasVisual;
				editor.addVisual();
			},

			mceReplaceContent : function(command, ui, value) {
				editor.execCommand('mceInsertContent', false, value.replace(/\{\$selection\}/g, selection.getContent({format : 'text'})));
			},

			mceInsertLink : function(command, ui, value) {
				var anchor;

				if (typeof(value) == 'string')
					value = {href : value};

				anchor = dom.getParent(selection.getNode(), 'a');

				// Spaces are never valid in URLs and it's a very common mistake for people to make so we fix it here.
				value.href = value.href.replace(' ', '%20');

				// Remove existing links if there could be child links or that the href isn't specified
				if (!anchor || !value.href) {
					formatter.remove('link');
				}		

				// Apply new link to selection
				if (value.href) {
					formatter.apply('link', value, anchor);
				}
			},

			selectAll : function() {
				var root = dom.getRoot(), rng = dom.createRng();

				rng.setStart(root, 0);
				rng.setEnd(root, root.childNodes.length);

				editor.selection.setRng(rng);
			}
		});

		// Add queryCommandState overrides
		addCommands({
			// Override justify commands
			'JustifyLeft,JustifyCenter,JustifyRight,JustifyFull' : function(command) {
				return isFormatMatch('align' + command.substring(7));
			},

			'Bold,Italic,Underline,Strikethrough,Superscript,Subscript' : function(command) {
				return isFormatMatch(command);
			},

			mceBlockQuote : function() {
				return isFormatMatch('blockquote');
			},

			Outdent : function() {
				var node;

				if (settings.inline_styles) {
					if ((node = dom.getParent(selection.getStart(), dom.isBlock)) && parseInt(node.style.paddingLeft) > 0)
						return TRUE;

					if ((node = dom.getParent(selection.getEnd(), dom.isBlock)) && parseInt(node.style.paddingLeft) > 0)
						return TRUE;
				}

				return queryCommandState('InsertUnorderedList') || queryCommandState('InsertOrderedList') || (!settings.inline_styles && !!dom.getParent(selection.getNode(), 'BLOCKQUOTE'));
			},

			'InsertUnorderedList,InsertOrderedList' : function(command) {
				return dom.getParent(selection.getNode(), command == 'insertunorderedlist' ? 'UL' : 'OL');
			}
		}, 'state');

		// Add queryCommandValue overrides
		addCommands({
			'FontSize,FontName' : function(command) {
				var value = 0, parent;

				if (parent = dom.getParent(selection.getNode(), 'span')) {
					if (command == 'fontsize')
						value = parent.style.fontSize;
					else
						value = parent.style.fontFamily.replace(/, /g, ',').replace(/[\'\"]/g, '').toLowerCase();
				}

				return value;
			}
		}, 'value');

		// Add undo manager logic
		if (settings.custom_undo_redo) {
			addCommands({
				Undo : function() {
					editor.undoManager.undo();
				},

				Redo : function() {
					editor.undoManager.redo();
				}
			});
		}
	};
})(tinymce);

(function(tinymce) {
	var Dispatcher = tinymce.util.Dispatcher;

	tinymce.UndoManager = function(editor) {
		var self, index = 0, data = [], beforeBookmark;

		function getContent() {
			return tinymce.trim(editor.getContent({format : 'raw', no_events : 1}));
		};

		return self = {
			typing : false,

			onAdd : new Dispatcher(self),

			onUndo : new Dispatcher(self),

			onRedo : new Dispatcher(self),

			beforeChange : function() {
				beforeBookmark = editor.selection.getBookmark(2, true);
			},

			add : function(level) {
				var i, settings = editor.settings, lastLevel;

				level = level || {};
				level.content = getContent();

				// Add undo level if needed
				lastLevel = data[index];
				if (lastLevel && lastLevel.content == level.content)
					return null;

				// Set before bookmark on previous level
				if (data[index])
					data[index].beforeBookmark = beforeBookmark;

				// Time to compress
				if (settings.custom_undo_redo_levels) {
					if (data.length > settings.custom_undo_redo_levels) {
						for (i = 0; i < data.length - 1; i++)
							data[i] = data[i + 1];

						data.length--;
						index = data.length;
					}
				}

				// Get a non intrusive normalized bookmark
				level.bookmark = editor.selection.getBookmark(2, true);

				// Crop array if needed
				if (index < data.length - 1)
					data.length = index + 1;

				data.push(level);
				index = data.length - 1;

				self.onAdd.dispatch(self, level);
				editor.isNotDirty = 0;

				return level;
			},

			undo : function() {
				var level, i;

				if (self.typing) {
					self.add();
					self.typing = false;
				}

				if (index > 0) {
					level = data[--index];

					editor.setContent(level.content, {format : 'raw'});
					editor.selection.moveToBookmark(level.beforeBookmark);

					self.onUndo.dispatch(self, level);
				}

				return level;
			},

			redo : function() {
				var level;

				if (index < data.length - 1) {
					level = data[++index];

					editor.setContent(level.content, {format : 'raw'});
					editor.selection.moveToBookmark(level.bookmark);

					self.onRedo.dispatch(self, level);
				}

				return level;
			},

			clear : function() {
				data = [];
				index = 0;
				self.typing = false;
			},

			hasUndo : function() {
				return index > 0 || this.typing;
			},

			hasRedo : function() {
				return index < data.length - 1 && !this.typing;
			}
		};
	};
})(tinymce);

(function(tinymce) {
	// Shorten names
	var Event = tinymce.dom.Event,
		isIE = tinymce.isIE,
		isGecko = tinymce.isGecko,
		isOpera = tinymce.isOpera,
		each = tinymce.each,
		extend = tinymce.extend,
		TRUE = true,
		FALSE = false;

	function cloneFormats(node) {
		var clone, temp, inner;

		do {
			if (/^(SPAN|STRONG|B|EM|I|FONT|STRIKE|U)$/.test(node.nodeName)) {
				if (clone) {
					temp = node.cloneNode(false);
					temp.appendChild(clone);
					clone = temp;
				} else {
					clone = inner = node.cloneNode(false);
				}

				clone.removeAttribute('id');
			}
		} while (node = node.parentNode);

		if (clone)
			return {wrapper : clone, inner : inner};
	};

	// Checks if the selection/caret is at the end of the specified block element
	function isAtEnd(rng, par) {
		var rng2 = par.ownerDocument.createRange();

		rng2.setStart(rng.endContainer, rng.endOffset);
		rng2.setEndAfter(par);

		// Get number of characters to the right of the cursor if it's zero then we are at the end and need to merge the next block element
		return rng2.cloneContents().textContent.length == 0;
	};

	function splitList(selection, dom, li) {
		var listBlock, block;

		if (dom.isEmpty(li)) {
			listBlock = dom.getParent(li, 'ul,ol');

			if (!dom.getParent(listBlock.parentNode, 'ul,ol')) {
				dom.split(listBlock, li);
				block = dom.create('p', 0, '<br data-mce-bogus="1" />');
				dom.replace(block, li);
				selection.select(block, 1);
			}

			return FALSE;
		}

		return TRUE;
	};

	tinymce.create('tinymce.ForceBlocks', {
		ForceBlocks : function(ed) {
			var t = this, s = ed.settings, elm;

			t.editor = ed;
			t.dom = ed.dom;
			elm = (s.forced_root_block || 'p').toLowerCase();
			s.element = elm.toUpperCase();

			ed.onPreInit.add(t.setup, t);
		},

		setup : function() {
			var t = this, ed = t.editor, s = ed.settings, dom = ed.dom, selection = ed.selection, blockElements = ed.schema.getBlockElements();

			// Force root blocks
			if (s.forced_root_block) {
				function addRootBlocks() {
					var node = selection.getStart(), rootNode = ed.getBody(), rng, startContainer, startOffset, endContainer, endOffset, rootBlockNode, tempNode, offset = -0xFFFFFF;

					if (!node || node.nodeType !== 1)
						return;

					// Check if node is wrapped in block
					while (node != rootNode) {
						if (blockElements[node.nodeName])
							return;

						node = node.parentNode;
					}

					// Get current selection
					rng = selection.getRng();
					if (rng.setStart) {
						startContainer = rng.startContainer;
						startOffset = rng.startOffset;
						endContainer = rng.endContainer;
						endOffset = rng.endOffset;
					} else {
						// Force control range into text range
						if (rng.item) {
							rng = ed.getDoc().body.createTextRange();
							rng.moveToElementText(rng.item(0));
						}

						tmpRng = rng.duplicate();
						tmpRng.collapse(true);
						startOffset = tmpRng.move('character', offset) * -1;

						if (!tmpRng.collapsed) {
							tmpRng = rng.duplicate();
							tmpRng.collapse(false);
							endOffset = (tmpRng.move('character', offset) * -1) - startOffset;
						}
					}

					// Wrap non block elements and text nodes
					for (node = rootNode.firstChild; node; node) {
						if (node.nodeType === 3 || (node.nodeType == 1 && !blockElements[node.nodeName])) {
							if (!rootBlockNode) {
								rootBlockNode = dom.create(s.forced_root_block);
								node.parentNode.insertBefore(rootBlockNode, node);
							}

							tempNode = node;
							node = node.nextSibling;
							rootBlockNode.appendChild(tempNode);
						} else {
							rootBlockNode = null;
							node = node.nextSibling;
						}
					}

					if (rng.setStart) {
						rng.setStart(startContainer, startOffset);
						rng.setEnd(endContainer, endOffset);
						selection.setRng(rng);
					} else {
						try {
							rng = ed.getDoc().body.createTextRange();
							rng.moveToElementText(rootNode);
							rng.collapse(true);
							rng.moveStart('character', startOffset);

							if (endOffset > 0)
								rng.moveEnd('character', endOffset);

							rng.select();
						} catch (ex) {
							// Ignore
						}
					}

					ed.nodeChanged();
				};

				ed.onKeyUp.add(addRootBlocks);
				ed.onClick.add(addRootBlocks);
			}

			if (s.force_br_newlines) {
				// Force IE to produce BRs on enter
				if (isIE) {
					ed.onKeyPress.add(function(ed, e) {
						var n;

						if (e.keyCode == 13 && selection.getNode().nodeName != 'LI') {
							selection.setContent('<br id="__" /> ', {format : 'raw'});
							n = dom.get('__');
							n.removeAttribute('id');
							selection.select(n);
							selection.collapse();
							return Event.cancel(e);
						}
					});
				}
			}

			if (s.force_p_newlines) {
				if (!isIE) {
					ed.onKeyPress.add(function(ed, e) {
						if (e.keyCode == 13 && !e.shiftKey && !t.insertPara(e))
							Event.cancel(e);
					});
				} else {
					// Ungly hack to for IE to preserve the formatting when you press
					// enter at the end of a block element with formatted contents
					// This logic overrides the browsers default logic with
					// custom logic that enables us to control the output
					tinymce.addUnload(function() {
						t._previousFormats = 0; // Fix IE leak
					});

					ed.onKeyPress.add(function(ed, e) {
						t._previousFormats = 0;

						// Clone the current formats, this will later be applied to the new block contents
						if (e.keyCode == 13 && !e.shiftKey && ed.selection.isCollapsed() && s.keep_styles)
							t._previousFormats = cloneFormats(ed.selection.getStart());
					});

					ed.onKeyUp.add(function(ed, e) {
						// Let IE break the element and the wrap the new caret location in the previous formats
						if (e.keyCode == 13 && !e.shiftKey) {
							var parent = ed.selection.getStart(), fmt = t._previousFormats;

							// Parent is an empty block
							if (!parent.hasChildNodes() && fmt) {
								parent = dom.getParent(parent, dom.isBlock);

								if (parent && parent.nodeName != 'LI') {
									parent.innerHTML = '';

									if (t._previousFormats) {
										parent.appendChild(fmt.wrapper);
										fmt.inner.innerHTML = '\uFEFF';
									} else
										parent.innerHTML = '\uFEFF';

									selection.select(parent, 1);
									selection.collapse(true);
									ed.getDoc().execCommand('Delete', false, null);
									t._previousFormats = 0;
								}
							}
						}
					});
				}

				if (isGecko) {
					ed.onKeyDown.add(function(ed, e) {
						if ((e.keyCode == 8 || e.keyCode == 46) && !e.shiftKey)
							t.backspaceDelete(e, e.keyCode == 8);
					});
				}
			}

			// Workaround for missing shift+enter support, http://bugs.webkit.org/show_bug.cgi?id=16973
			if (tinymce.isWebKit) {
				function insertBr(ed) {
					var rng = selection.getRng(), br, div = dom.create('div', null, ' '), divYPos, vpHeight = dom.getViewPort(ed.getWin()).h;

					// Insert BR element
					rng.insertNode(br = dom.create('br'));

					// Place caret after BR
					rng.setStartAfter(br);
					rng.setEndAfter(br);
					selection.setRng(rng);

					// Could not place caret after BR then insert an nbsp entity and move the caret
					if (selection.getSel().focusNode == br.previousSibling) {
						selection.select(dom.insertAfter(dom.doc.createTextNode('\u00a0'), br));
						selection.collapse(TRUE);
					}

					// Create a temporary DIV after the BR and get the position as it
					// seems like getPos() returns 0 for text nodes and BR elements.
					dom.insertAfter(div, br);
					divYPos = dom.getPos(div).y;
					dom.remove(div);

					// Scroll to new position, scrollIntoView can't be used due to bug: http://bugs.webkit.org/show_bug.cgi?id=16117
					if (divYPos > vpHeight) // It is not necessary to scroll if the DIV is inside the view port.
						ed.getWin().scrollTo(0, divYPos);
				};

				ed.onKeyPress.add(function(ed, e) {
					if (e.keyCode == 13 && (e.shiftKey || (s.force_br_newlines && !dom.getParent(selection.getNode(), 'h1,h2,h3,h4,h5,h6,ol,ul')))) {
						insertBr(ed);
						Event.cancel(e);
					}
				});
			}

			// IE specific fixes
			if (isIE) {
				// Replaces IE:s auto generated paragraphs with the specified element name
				if (s.element != 'P') {
					ed.onKeyPress.add(function(ed, e) {
						t.lastElm = selection.getNode().nodeName;
					});

					ed.onKeyUp.add(function(ed, e) {
						var bl, n = selection.getNode(), b = ed.getBody();

						if (b.childNodes.length === 1 && n.nodeName == 'P') {
							n = dom.rename(n, s.element);
							selection.select(n);
							selection.collapse();
							ed.nodeChanged();
						} else if (e.keyCode == 13 && !e.shiftKey && t.lastElm != 'P') {
							bl = dom.getParent(n, 'p');

							if (bl) {
								dom.rename(bl, s.element);
								ed.nodeChanged();
							}
						}
					});
				}
			}
		},

		getParentBlock : function(n) {
			var d = this.dom;

			return d.getParent(n, d.isBlock);
		},

		insertPara : function(e) {
			var t = this, ed = t.editor, dom = ed.dom, d = ed.getDoc(), se = ed.settings, s = ed.selection.getSel(), r = s.getRangeAt(0), b = d.body;
			var rb, ra, dir, sn, so, en, eo, sb, eb, bn, bef, aft, sc, ec, n, vp = dom.getViewPort(ed.getWin()), y, ch, car;

			ed.undoManager.beforeChange();

			// If root blocks are forced then use Operas default behavior since it's really good
// Removed due to bug: #1853816
//			if (se.forced_root_block && isOpera)
//				return TRUE;

			// Setup before range
			rb = d.createRange();

			// If is before the first block element and in body, then move it into first block element
			rb.setStart(s.anchorNode, s.anchorOffset);
			rb.collapse(TRUE);

			// Setup after range
			ra = d.createRange();

			// If is before the first block element and in body, then move it into first block element
			ra.setStart(s.focusNode, s.focusOffset);
			ra.collapse(TRUE);

			// Setup start/end points
			dir = rb.compareBoundaryPoints(rb.START_TO_END, ra) < 0;
			sn = dir ? s.anchorNode : s.focusNode;
			so = dir ? s.anchorOffset : s.focusOffset;
			en = dir ? s.focusNode : s.anchorNode;
			eo = dir ? s.focusOffset : s.anchorOffset;

			// If selection is in empty table cell
			if (sn === en && /^(TD|TH)$/.test(sn.nodeName)) {
				if (sn.firstChild.nodeName == 'BR')
					dom.remove(sn.firstChild); // Remove BR

				// Create two new block elements
				if (sn.childNodes.length == 0) {
					ed.dom.add(sn, se.element, null, '<br />');
					aft = ed.dom.add(sn, se.element, null, '<br />');
				} else {
					n = sn.innerHTML;
					sn.innerHTML = '';
					ed.dom.add(sn, se.element, null, n);
					aft = ed.dom.add(sn, se.element, null, '<br />');
				}

				// Move caret into the last one
				r = d.createRange();
				r.selectNodeContents(aft);
				r.collapse(1);
				ed.selection.setRng(r);

				return FALSE;
			}

			// If the caret is in an invalid location in FF we need to move it into the first block
			if (sn == b && en == b && b.firstChild && ed.dom.isBlock(b.firstChild)) {
				sn = en = sn.firstChild;
				so = eo = 0;
				rb = d.createRange();
				rb.setStart(sn, 0);
				ra = d.createRange();
				ra.setStart(en, 0);
			}

			// If the body is totally empty add a BR element this might happen on webkit
			if (!d.body.hasChildNodes()) {
				d.body.appendChild(dom.create('br'));
			}

			// Never use body as start or end node
			sn = sn.nodeName == "HTML" ? d.body : sn; // Fix for Opera bug: https://bugs.opera.com/show_bug.cgi?id=273224&comments=yes
			sn = sn.nodeName == "BODY" ? sn.firstChild : sn;
			en = en.nodeName == "HTML" ? d.body : en; // Fix for Opera bug: https://bugs.opera.com/show_bug.cgi?id=273224&comments=yes
			en = en.nodeName == "BODY" ? en.firstChild : en;

			// Get start and end blocks
			sb = t.getParentBlock(sn);
			eb = t.getParentBlock(en);
			bn = sb ? sb.nodeName : se.element; // Get block name to create

			// Return inside list use default browser behavior
			if (n = t.dom.getParent(sb, 'li,pre')) {
				if (n.nodeName == 'LI')
					return splitList(ed.selection, t.dom, n);

				return TRUE;
			}

			// If caption or absolute layers then always generate new blocks within
			if (sb && (sb.nodeName == 'CAPTION' || /absolute|relative|fixed/gi.test(dom.getStyle(sb, 'position', 1)))) {
				bn = se.element;
				sb = null;
			}

			// If caption or absolute layers then always generate new blocks within
			if (eb && (eb.nodeName == 'CAPTION' || /absolute|relative|fixed/gi.test(dom.getStyle(sb, 'position', 1)))) {
				bn = se.element;
				eb = null;
			}

			// Use P instead
			if (/(TD|TABLE|TH|CAPTION)/.test(bn) || (sb && bn == "DIV" && /left|right/gi.test(dom.getStyle(sb, 'float', 1)))) {
				bn = se.element;
				sb = eb = null;
			}

			// Setup new before and after blocks
			bef = (sb && sb.nodeName == bn) ? sb.cloneNode(0) : ed.dom.create(bn);
			aft = (eb && eb.nodeName == bn) ? eb.cloneNode(0) : ed.dom.create(bn);

			// Remove id from after clone
			aft.removeAttribute('id');

			// Is header and cursor is at the end, then force paragraph under
			if (/^(H[1-6])$/.test(bn) && isAtEnd(r, sb))
				aft = ed.dom.create(se.element);

			// Find start chop node
			n = sc = sn;
			do {
				if (n == b || n.nodeType == 9 || t.dom.isBlock(n) || /(TD|TABLE|TH|CAPTION)/.test(n.nodeName))
					break;

				sc = n;
			} while ((n = n.previousSibling ? n.previousSibling : n.parentNode));

			// Find end chop node
			n = ec = en;
			do {
				if (n == b || n.nodeType == 9 || t.dom.isBlock(n) || /(TD|TABLE|TH|CAPTION)/.test(n.nodeName))
					break;

				ec = n;
			} while ((n = n.nextSibling ? n.nextSibling : n.parentNode));

			// Place first chop part into before block element
			if (sc.nodeName == bn)
				rb.setStart(sc, 0);
			else
				rb.setStartBefore(sc);

			rb.setEnd(sn, so);
			bef.appendChild(rb.cloneContents() || d.createTextNode('')); // Empty text node needed for Safari

			// Place secnd chop part within new block element
			try {
				ra.setEndAfter(ec);
			} catch(ex) {
				//console.debug(s.focusNode, s.focusOffset);
			}

			ra.setStart(en, eo);
			aft.appendChild(ra.cloneContents() || d.createTextNode('')); // Empty text node needed for Safari

			// Create range around everything
			r = d.createRange();
			if (!sc.previousSibling && sc.parentNode.nodeName == bn) {
				r.setStartBefore(sc.parentNode);
			} else {
				if (rb.startContainer.nodeName == bn && rb.startOffset == 0)
					r.setStartBefore(rb.startContainer);
				else
					r.setStart(rb.startContainer, rb.startOffset);
			}

			if (!ec.nextSibling && ec.parentNode.nodeName == bn)
				r.setEndAfter(ec.parentNode);
			else
				r.setEnd(ra.endContainer, ra.endOffset);

			// Delete and replace it with new block elements
			r.deleteContents();

			if (isOpera)
				ed.getWin().scrollTo(0, vp.y);

			// Never wrap blocks in blocks
			if (bef.firstChild && bef.firstChild.nodeName == bn)
				bef.innerHTML = bef.firstChild.innerHTML;

			if (aft.firstChild && aft.firstChild.nodeName == bn)
				aft.innerHTML = aft.firstChild.innerHTML;

			function appendStyles(e, en) {
				var nl = [], nn, n, i;

				e.innerHTML = '';

				// Make clones of style elements
				if (se.keep_styles) {
					n = en;
					do {
						// We only want style specific elements
						if (/^(SPAN|STRONG|B|EM|I|FONT|STRIKE|U)$/.test(n.nodeName)) {
							nn = n.cloneNode(FALSE);
							dom.setAttrib(nn, 'id', ''); // Remove ID since it needs to be unique
							nl.push(nn);
						}
					} while (n = n.parentNode);
				}

				// Append style elements to aft
				if (nl.length > 0) {
					for (i = nl.length - 1, nn = e; i >= 0; i--)
						nn = nn.appendChild(nl[i]);

					// Padd most inner style element
					nl[0].innerHTML = isOpera ? '\u00a0' : '<br />'; // Extra space for Opera so that the caret can move there
					return nl[0]; // Move caret to most inner element
				} else
					e.innerHTML = isOpera ? '\u00a0' : '<br />'; // Extra space for Opera so that the caret can move there
			};
				
			// Padd empty blocks
			if (dom.isEmpty(bef))
				appendStyles(bef, sn);

			// Fill empty afterblook with current style
			if (dom.isEmpty(aft))
				car = appendStyles(aft, en);

			// Opera needs this one backwards for older versions
			if (isOpera && parseFloat(opera.version()) < 9.5) {
				r.insertNode(bef);
				r.insertNode(aft);
			} else {
				r.insertNode(aft);
				r.insertNode(bef);
			}

			// Normalize
			aft.normalize();
			bef.normalize();

			// Move cursor and scroll into view
			ed.selection.select(aft, true);
			ed.selection.collapse(true);

			// scrollIntoView seems to scroll the parent window in most browsers now including FF 3.0b4 so it's time to stop using it and do it our selfs
			y = ed.dom.getPos(aft).y;
			//ch = aft.clientHeight;

			// Is element within viewport
			if (y < vp.y || y + 25 > vp.y + vp.h) {
				ed.getWin().scrollTo(0, y < vp.y ? y : y - vp.h + 25); // Needs to be hardcoded to roughly one line of text if a huge text block is broken into two blocks

				/*console.debug(
					'Element: y=' + y + ', h=' + ch + ', ' +
					'Viewport: y=' + vp.y + ", h=" + vp.h + ', bottom=' + (vp.y + vp.h)
				);*/
			}

			ed.undoManager.add();

			return FALSE;
		},

		backspaceDelete : function(e, bs) {
			var t = this, ed = t.editor, b = ed.getBody(), dom = ed.dom, n, se = ed.selection, r = se.getRng(), sc = r.startContainer, n, w, tn, walker;

			// Delete when caret is behind a element doesn't work correctly on Gecko see #3011651
			if (!bs && r.collapsed && sc.nodeType == 1 && r.startOffset == sc.childNodes.length) {
				walker = new tinymce.dom.TreeWalker(sc.lastChild, sc);

				// Walk the dom backwards until we find a text node
				for (n = sc.lastChild; n; n = walker.prev()) {
					if (n.nodeType == 3) {
						r.setStart(n, n.nodeValue.length);
						r.collapse(true);
						se.setRng(r);
						return;
					}
				}
			}

			// The caret sometimes gets stuck in Gecko if you delete empty paragraphs
			// This workaround removes the element by hand and moves the caret to the previous element
			if (sc && ed.dom.isBlock(sc) && !/^(TD|TH)$/.test(sc.nodeName) && bs) {
				if (sc.childNodes.length == 0 || (sc.childNodes.length == 1 && sc.firstChild.nodeName == 'BR')) {
					// Find previous block element
					n = sc;
					while ((n = n.previousSibling) && !ed.dom.isBlock(n)) ;

					if (n) {
						if (sc != b.firstChild) {
							// Find last text node
							w = ed.dom.doc.createTreeWalker(n, NodeFilter.SHOW_TEXT, null, FALSE);
							while (tn = w.nextNode())
								n = tn;

							// Place caret at the end of last text node
							r = ed.getDoc().createRange();
							r.setStart(n, n.nodeValue ? n.nodeValue.length : 0);
							r.setEnd(n, n.nodeValue ? n.nodeValue.length : 0);
							se.setRng(r);

							// Remove the target container
							ed.dom.remove(sc);
						}

						return Event.cancel(e);
					}
				}
			}
		}
	});
})(tinymce);

(function(tinymce) {
	// Shorten names
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, each = tinymce.each, extend = tinymce.extend;

	tinymce.create('tinymce.ControlManager', {
		ControlManager : function(ed, s) {
			var t = this, i;

			s = s || {};
			t.editor = ed;
			t.controls = {};
			t.onAdd = new tinymce.util.Dispatcher(t);
			t.onPostRender = new tinymce.util.Dispatcher(t);
			t.prefix = s.prefix || ed.id + '_';
			t._cls = {};

			t.onPostRender.add(function() {
				each(t.controls, function(c) {
					c.postRender();
				});
			});
		},

		get : function(id) {
			return this.controls[this.prefix + id] || this.controls[id];
		},

		setActive : function(id, s) {
			var c = null;

			if (c = this.get(id))
				c.setActive(s);

			return c;
		},

		setDisabled : function(id, s) {
			var c = null;

			if (c = this.get(id))
				c.setDisabled(s);

			return c;
		},

		add : function(c) {
			var t = this;

			if (c) {
				t.controls[c.id] = c;
				t.onAdd.dispatch(c, t);
			}

			return c;
		},

		createControl : function(n) {
			var c, t = this, ed = t.editor;

			each(ed.plugins, function(p) {
				if (p.createControl) {
					c = p.createControl(n, t);

					if (c)
						return false;
				}
			});

			switch (n) {
				case "|":
				case "separator":
					return t.createSeparator();
			}

			if (!c && ed.buttons && (c = ed.buttons[n]))
				return t.createButton(n, c);

			return t.add(c);
		},

		createDropMenu : function(id, s, cc) {
			var t = this, ed = t.editor, c, bm, v, cls;

			s = extend({
				'class' : 'mceDropDown',
				constrain : ed.settings.constrain_menus
			}, s);

			s['class'] = s['class'] + ' ' + ed.getParam('skin') + 'Skin';
			if (v = ed.getParam('skin_variant'))
				s['class'] += ' ' + ed.getParam('skin') + 'Skin' + v.substring(0, 1).toUpperCase() + v.substring(1);

			id = t.prefix + id;
			cls = cc || t._cls.dropmenu || tinymce.ui.DropMenu;
			c = t.controls[id] = new cls(id, s);
			c.onAddItem.add(function(c, o) {
				var s = o.settings;

				s.title = ed.getLang(s.title, s.title);

				if (!s.onclick) {
					s.onclick = function(v) {
						if (s.cmd)
							ed.execCommand(s.cmd, s.ui || false, s.value);
					};
				}
			});

			ed.onRemove.add(function() {
				c.destroy();
			});

			// Fix for bug #1897785, #1898007
			if (tinymce.isIE) {
				c.onShowMenu.add(function() {
					// IE 8 needs focus in order to store away a range with the current collapsed caret location
					ed.focus();

					bm = ed.selection.getBookmark(1);
				});

				c.onHideMenu.add(function() {
					if (bm) {
						ed.selection.moveToBookmark(bm);
						bm = 0;
					}
				});
			}

			return t.add(c);
		},

		createListBox : function(id, s, cc) {
			var t = this, ed = t.editor, cmd, c, cls;

			if (t.get(id))
				return null;

			s.title = ed.translate(s.title);
			s.scope = s.scope || ed;

			if (!s.onselect) {
				s.onselect = function(v) {
					ed.execCommand(s.cmd, s.ui || false, v || s.value);
				};
			}

			s = extend({
				title : s.title,
				'class' : 'mce_' + id,
				scope : s.scope,
				control_manager : t
			}, s);

			id = t.prefix + id;


			function useNativeListForAccessibility(ed) {
				return ed.settings.use_accessible_selects && !tinymce.isGecko
			}

			if (ed.settings.use_native_selects || useNativeListForAccessibility(ed))
				c = new tinymce.ui.NativeListBox(id, s);
			else {
				cls = cc || t._cls.listbox || tinymce.ui.ListBox;
				c = new cls(id, s, ed);
			}

			t.controls[id] = c;

			// Fix focus problem in Safari
			if (tinymce.isWebKit) {
				c.onPostRender.add(function(c, n) {
					// Store bookmark on mousedown
					Event.add(n, 'mousedown', function() {
						ed.bookmark = ed.selection.getBookmark(1);
					});

					// Restore on focus, since it might be lost
					Event.add(n, 'focus', function() {
						ed.selection.moveToBookmark(ed.bookmark);
						ed.bookmark = null;
					});
				});
			}

			if (c.hideMenu)
				ed.onMouseDown.add(c.hideMenu, c);

			return t.add(c);
		},

		createButton : function(id, s, cc) {
			var t = this, ed = t.editor, o, c, cls;

			if (t.get(id))
				return null;

			s.title = ed.translate(s.title);
			s.label = ed.translate(s.label);
			s.scope = s.scope || ed;

			if (!s.onclick && !s.menu_button) {
				s.onclick = function() {
					ed.execCommand(s.cmd, s.ui || false, s.value);
				};
			}

			s = extend({
				title : s.title,
				'class' : 'mce_' + id,
				unavailable_prefix : ed.getLang('unavailable', ''),
				scope : s.scope,
				control_manager : t
			}, s);

			id = t.prefix + id;

			if (s.menu_button) {
				cls = cc || t._cls.menubutton || tinymce.ui.MenuButton;
				c = new cls(id, s, ed);
				ed.onMouseDown.add(c.hideMenu, c);
			} else {
				cls = t._cls.button || tinymce.ui.Button;
				c = new cls(id, s, ed);
			}

			return t.add(c);
		},

		createMenuButton : function(id, s, cc) {
			s = s || {};
			s.menu_button = 1;

			return this.createButton(id, s, cc);
		},

		createSplitButton : function(id, s, cc) {
			var t = this, ed = t.editor, cmd, c, cls;

			if (t.get(id))
				return null;

			s.title = ed.translate(s.title);
			s.scope = s.scope || ed;

			if (!s.onclick) {
				s.onclick = function(v) {
					ed.execCommand(s.cmd, s.ui || false, v || s.value);
				};
			}

			if (!s.onselect) {
				s.onselect = function(v) {
					ed.execCommand(s.cmd, s.ui || false, v || s.value);
				};
			}

			s = extend({
				title : s.title,
				'class' : 'mce_' + id,
				scope : s.scope,
				control_manager : t
			}, s);

			id = t.prefix + id;
			cls = cc || t._cls.splitbutton || tinymce.ui.SplitButton;
			c = t.add(new cls(id, s, ed));
			ed.onMouseDown.add(c.hideMenu, c);

			return c;
		},

		createColorSplitButton : function(id, s, cc) {
			var t = this, ed = t.editor, cmd, c, cls, bm;

			if (t.get(id))
				return null;

			s.title = ed.translate(s.title);
			s.scope = s.scope || ed;

			if (!s.onclick) {
				s.onclick = function(v) {
					if (tinymce.isIE)
						bm = ed.selection.getBookmark(1);

					ed.execCommand(s.cmd, s.ui || false, v || s.value);
				};
			}

			if (!s.onselect) {
				s.onselect = function(v) {
					ed.execCommand(s.cmd, s.ui || false, v || s.value);
				};
			}

			s = extend({
				title : s.title,
				'class' : 'mce_' + id,
				'menu_class' : ed.getParam('skin') + 'Skin',
				scope : s.scope,
				more_colors_title : ed.getLang('more_colors')
			}, s);

			id = t.prefix + id;
			cls = cc || t._cls.colorsplitbutton || tinymce.ui.ColorSplitButton;
			c = new cls(id, s, ed);
			ed.onMouseDown.add(c.hideMenu, c);

			// Remove the menu element when the editor is removed
			ed.onRemove.add(function() {
				c.destroy();
			});

			// Fix for bug #1897785, #1898007
			if (tinymce.isIE) {
				c.onShowMenu.add(function() {
					// IE 8 needs focus in order to store away a range with the current collapsed caret location
					ed.focus();
					bm = ed.selection.getBookmark(1);
				});

				c.onHideMenu.add(function() {
					if (bm) {
						ed.selection.moveToBookmark(bm);
						bm = 0;
					}
				});
			}

			return t.add(c);
		},

		createToolbar : function(id, s, cc) {
			var c, t = this, cls;

			id = t.prefix + id;
			cls = cc || t._cls.toolbar || tinymce.ui.Toolbar;
			c = new cls(id, s, t.editor);

			if (t.get(id))
				return null;

			return t.add(c);
		},
		
		createToolbarGroup : function(id, s, cc) {
			var c, t = this, cls;
			id = t.prefix + id;
			cls = cc || this._cls.toolbarGroup || tinymce.ui.ToolbarGroup;
			c = new cls(id, s, t.editor);
			
			if (t.get(id))
				return null;
			
			return t.add(c);
		},

		createSeparator : function(cc) {
			var cls = cc || this._cls.separator || tinymce.ui.Separator;

			return new cls();
		},

		setControlType : function(n, c) {
			return this._cls[n.toLowerCase()] = c;
		},
	
		destroy : function() {
			each(this.controls, function(c) {
				c.destroy();
			});

			this.controls = null;
		}
	});
})(tinymce);

(function(tinymce) {
	var Dispatcher = tinymce.util.Dispatcher, each = tinymce.each, isIE = tinymce.isIE, isOpera = tinymce.isOpera;

	tinymce.create('tinymce.WindowManager', {
		WindowManager : function(ed) {
			var t = this;

			t.editor = ed;
			t.onOpen = new Dispatcher(t);
			t.onClose = new Dispatcher(t);
			t.params = {};
			t.features = {};
		},

		open : function(s, p) {
			var t = this, f = '', x, y, mo = t.editor.settings.dialog_type == 'modal', w, sw, sh, vp = tinymce.DOM.getViewPort(), u;

			// Default some options
			s = s || {};
			p = p || {};
			sw = isOpera ? vp.w : screen.width; // Opera uses windows inside the Opera window
			sh = isOpera ? vp.h : screen.height;
			s.name = s.name || 'mc_' + new Date().getTime();
			s.width = parseInt(s.width || 320);
			s.height = parseInt(s.height || 240);
			s.resizable = true;
			s.left = s.left || parseInt(sw / 2.0) - (s.width / 2.0);
			s.top = s.top || parseInt(sh / 2.0) - (s.height / 2.0);
			p.inline = false;
			p.mce_width = s.width;
			p.mce_height = s.height;
			p.mce_auto_focus = s.auto_focus;

			if (mo) {
				if (isIE) {
					s.center = true;
					s.help = false;
					s.dialogWidth = s.width + 'px';
					s.dialogHeight = s.height + 'px';
					s.scroll = s.scrollbars || false;
				}
			}

			// Build features string
			each(s, function(v, k) {
				if (tinymce.is(v, 'boolean'))
					v = v ? 'yes' : 'no';

				if (!/^(name|url)$/.test(k)) {
					if (isIE && mo)
						f += (f ? ';' : '') + k + ':' + v;
					else
						f += (f ? ',' : '') + k + '=' + v;
				}
			});

			t.features = s;
			t.params = p;
			t.onOpen.dispatch(t, s, p);

			u = s.url || s.file;
			u = tinymce._addVer(u);

			try {
				if (isIE && mo) {
					w = 1;
					window.showModalDialog(u, window, f);
				} else
					w = window.open(u, s.name, f);
			} catch (ex) {
				// Ignore
			}

			if (!w)
				alert(t.editor.getLang('popup_blocked'));
		},

		close : function(w) {
			w.close();
			this.onClose.dispatch(this);
		},

		createInstance : function(cl, a, b, c, d, e) {
			var f = tinymce.resolve(cl);

			return new f(a, b, c, d, e);
		},

		confirm : function(t, cb, s, w) {
			w = w || window;

			cb.call(s || this, w.confirm(this._decode(this.editor.getLang(t, t))));
		},

		alert : function(tx, cb, s, w) {
			var t = this;

			w = w || window;
			w.alert(t._decode(t.editor.getLang(tx, tx)));

			if (cb)
				cb.call(s || t);
		},

		resizeBy : function(dw, dh, win) {
			win.resizeBy(dw, dh);
		},

		// Internal functions

		_decode : function(s) {
			return tinymce.DOM.decode(s).replace(/\\n/g, '\n');
		}
	});
}(tinymce));
(function(tinymce) {
	tinymce.Formatter = function(ed) {
		var formats = {},
			each = tinymce.each,
			dom = ed.dom,
			selection = ed.selection,
			TreeWalker = tinymce.dom.TreeWalker,
			rangeUtils = new tinymce.dom.RangeUtils(dom),
			isValid = ed.schema.isValidChild,
			isBlock = dom.isBlock,
			forcedRootBlock = ed.settings.forced_root_block,
			nodeIndex = dom.nodeIndex,
			INVISIBLE_CHAR = '\uFEFF',
			MCE_ATTR_RE = /^(src|href|style)$/,
			FALSE = false,
			TRUE = true,
			undefined;

		function isArray(obj) {
			return obj instanceof Array;
		};

		function getParents(node, selector) {
			return dom.getParents(node, selector, dom.getRoot());
		};

		function isCaretNode(node) {
			return node.nodeType === 1 && (node.face === 'mceinline' || node.style.fontFamily === 'mceinline');
		};

		// Public functions

		function get(name) {
			return name ? formats[name] : formats;
		};

		function register(name, format) {
			if (name) {
				if (typeof(name) !== 'string') {
					each(name, function(format, name) {
						register(name, format);
					});
				} else {
					// Force format into array and add it to internal collection
					format = format.length ? format : [format];

					each(format, function(format) {
						// Set deep to false by default on selector formats this to avoid removing
						// alignment on images inside paragraphs when alignment is changed on paragraphs
						if (format.deep === undefined)
							format.deep = !format.selector;

						// Default to true
						if (format.split === undefined)
							format.split = !format.selector || format.inline;

						// Default to true
						if (format.remove === undefined && format.selector && !format.inline)
							format.remove = 'none';

						// Mark format as a mixed format inline + block level
						if (format.selector && format.inline) {
							format.mixed = true;
							format.block_expand = true;
						}

						// Split classes if needed
						if (typeof(format.classes) === 'string')
							format.classes = format.classes.split(/\s+/);
					});

					formats[name] = format;
				}
			}
		};

		var getTextDecoration = function(node) {
			var decoration;

			ed.dom.getParent(node, function(n) {
				decoration = ed.dom.getStyle(n, 'text-decoration');
				return decoration && decoration !== 'none';
			});

			return decoration;
		};

		var processUnderlineAndColor = function(node) {
			var textDecoration;
			if (node.nodeType === 1 && node.parentNode && node.parentNode.nodeType === 1) {
				textDecoration = getTextDecoration(node.parentNode);
				if (ed.dom.getStyle(node, 'color') && textDecoration) {
					ed.dom.setStyle(node, 'text-decoration', textDecoration);
				} else if (ed.dom.getStyle(node, 'textdecoration') === textDecoration) {
					ed.dom.setStyle(node, 'text-decoration', null);
				}
			}
		};

		function apply(name, vars, node) {
			var formatList = get(name), format = formatList[0], bookmark, rng, i, isCollapsed = selection.isCollapsed();

			function moveStart(rng) {
				var container = rng.startContainer,
					offset = rng.startOffset,
					walker, node;

				// Move startContainer/startOffset in to a suitable node
				if (container.nodeType == 1 || container.nodeValue === "") {
					container = container.nodeType == 1 ? container.childNodes[offset] : container;

					// Might fail if the offset is behind the last element in it's container
					if (container) {
						walker = new TreeWalker(container, container.parentNode);
						for (node = walker.current(); node; node = walker.next()) {
							if (node.nodeType == 3 && !isWhiteSpaceNode(node)) {
								rng.setStart(node, 0);
								break;
							}
						}
					}
				}

				return rng;
			};

			function setElementFormat(elm, fmt) {
				fmt = fmt || format;

				if (elm) {
					if (fmt.onformat) {
						fmt.onformat(elm, fmt, vars, node);
					}

					each(fmt.styles, function(value, name) {
						dom.setStyle(elm, name, replaceVars(value, vars));
					});

					each(fmt.attributes, function(value, name) {
						dom.setAttrib(elm, name, replaceVars(value, vars));
					});

					each(fmt.classes, function(value) {
						value = replaceVars(value, vars);

						if (!dom.hasClass(elm, value))
							dom.addClass(elm, value);
					});
				}
			};
			function adjustSelectionToVisibleSelection() {
				function findSelectionEnd(start, end) {
					var walker = new TreeWalker(end);
					for (node = walker.current(); node; node = walker.prev()) {
						if (node.childNodes.length > 1 || node == start) {
							return node;
						}
					}
				};

				// Adjust selection so that a end container with a end offset of zero is not included in the selection
				// as this isn't visible to the user.
				var rng = ed.selection.getRng();
				var start = rng.startContainer;
				var end = rng.endContainer;

				if (start != end && rng.endOffset == 0) {
					var newEnd = findSelectionEnd(start, end);
					var endOffset = newEnd.nodeType == 3 ? newEnd.length : newEnd.childNodes.length;

					rng.setEnd(newEnd, endOffset);
				}

				return rng;
			}
			
			function applyStyleToList(node, bookmark, wrapElm, newWrappers, process){
				var nodes = [], listIndex = -1, list, startIndex = -1, endIndex = -1, currentWrapElm;
				
				// find the index of the first child list.
				each(node.childNodes, function(n, index) {
					if (n.nodeName === "UL" || n.nodeName === "OL") {
						listIndex = index;
						list = n;
						return false;
					}
				});
				
				// get the index of the bookmarks
				each(node.childNodes, function(n, index) {
					if (n.nodeName === "SPAN" && dom.getAttrib(n, "data-mce-type") == "bookmark") {
						if (n.id == bookmark.id + "_start") {
							startIndex = index;
						} else if (n.id == bookmark.id + "_end") {
							endIndex = index;
						}
					}
				});
				
				// if the selection spans across an embedded list, or there isn't an embedded list - handle processing normally
				if (listIndex <= 0 || (startIndex < listIndex && endIndex > listIndex)) {
					each(tinymce.grep(node.childNodes), process);
					return 0;
				} else {
					currentWrapElm = wrapElm.cloneNode(FALSE);
					
					// create a list of the nodes on the same side of the list as the selection
					each(tinymce.grep(node.childNodes), function(n, index) {
						if ((startIndex < listIndex && index < listIndex) || (startIndex > listIndex && index > listIndex)) {
							nodes.push(n); 
							n.parentNode.removeChild(n);
						}
					});
					
					// insert the wrapping element either before or after the list.
					if (startIndex < listIndex) {
						node.insertBefore(currentWrapElm, list);
					} else if (startIndex > listIndex) {
						node.insertBefore(currentWrapElm, list.nextSibling);
					}
					
					// add the new nodes to the list.
					newWrappers.push(currentWrapElm);

					each(nodes, function(node) {
						currentWrapElm.appendChild(node);
					});

					return currentWrapElm;
				}
			};
			
			function applyRngStyle(rng, bookmark, node_specific) {
				var newWrappers = [], wrapName, wrapElm;

				// Setup wrapper element
				wrapName = format.inline || format.block;
				wrapElm = dom.create(wrapName);
				setElementFormat(wrapElm);

				rangeUtils.walk(rng, function(nodes) {
					var currentWrapElm;

					function process(node) {
						var nodeName = node.nodeName.toLowerCase(), parentName = node.parentNode.nodeName.toLowerCase(), found;

						// Stop wrapping on br elements
						if (isEq(nodeName, 'br')) {
							currentWrapElm = 0;

							// Remove any br elements when we wrap things
							if (format.block)
								dom.remove(node);

							return;
						}

						// If node is wrapper type
						if (format.wrapper && matchNode(node, name, vars)) {
							currentWrapElm = 0;
							return;
						}

						// Can we rename the block
						if (format.block && !format.wrapper && isTextBlock(nodeName)) {
							node = dom.rename(node, wrapName);
							setElementFormat(node);
							newWrappers.push(node);
							currentWrapElm = 0;
							return;
						}

						// Handle selector patterns
						if (format.selector) {
							// Look for matching formats
							each(formatList, function(format) {
								// Check collapsed state if it exists
								if ('collapsed' in format && format.collapsed !== isCollapsed) {
									return;
								}

								if (dom.is(node, format.selector) && !isCaretNode(node)) {
									setElementFormat(node, format);
									found = true;
								}
							});

							// Continue processing if a selector match wasn't found and a inline element is defined
							if (!format.inline || found) {
								currentWrapElm = 0;
								return;
							}
						}

						// Is it valid to wrap this item
						if (isValid(wrapName, nodeName) && isValid(parentName, wrapName) &&
								!(!node_specific && node.nodeType === 3 && node.nodeValue.length === 1 && node.nodeValue.charCodeAt(0) === 65279) && node.id !== '_mce_caret') {
							// Start wrapping
							if (!currentWrapElm) {
								// Wrap the node
								currentWrapElm = wrapElm.cloneNode(FALSE);
								node.parentNode.insertBefore(currentWrapElm, node);
								newWrappers.push(currentWrapElm);
							}

							currentWrapElm.appendChild(node);
						} else if (nodeName == 'li' && bookmark) {
							// Start wrapping - if we are in a list node and have a bookmark, then we will always begin by wrapping in a new element.
							currentWrapElm = applyStyleToList(node, bookmark, wrapElm, newWrappers, process);
						} else {
							// Start a new wrapper for possible children
							currentWrapElm = 0;

							each(tinymce.grep(node.childNodes), process);

							// End the last wrapper
							currentWrapElm = 0;
						}
					};

					// Process siblings from range
					each(nodes, process);
				});

				// Wrap links inside as well, for example color inside a link when the wrapper is around the link
				if (format.wrap_links === false) {
					each(newWrappers, function(node) {
						function process(node) {
							var i, currentWrapElm, children;

							if (node.nodeName === 'A') {
								currentWrapElm = wrapElm.cloneNode(FALSE);
								newWrappers.push(currentWrapElm);

								children = tinymce.grep(node.childNodes);
								for (i = 0; i < children.length; i++)
									currentWrapElm.appendChild(children[i]);

								node.appendChild(currentWrapElm);
							}

							each(tinymce.grep(node.childNodes), process);
						};

						process(node);
					});
				}

				// Cleanup
				each(newWrappers, function(node) {
					var childCount;

					function getChildCount(node) {
						var count = 0;

						each(node.childNodes, function(node) {
							if (!isWhiteSpaceNode(node) && !isBookmarkNode(node))
								count++;
						});

						return count;
					};

					function mergeStyles(node) {
						var child, clone;

						each(node.childNodes, function(node) {
							if (node.nodeType == 1 && !isBookmarkNode(node) && !isCaretNode(node)) {
								child = node;
								return FALSE; // break loop
							}
						});

						// If child was found and of the same type as the current node
						if (child && matchName(child, format)) {
							clone = child.cloneNode(FALSE);
							setElementFormat(clone);

							dom.replace(clone, node, TRUE);
							dom.remove(child, 1);
						}

						return clone || node;
					};

					childCount = getChildCount(node);

					// Remove empty nodes but only if there is multiple wrappers and they are not block
					// elements so never remove single <h1></h1> since that would remove the currrent empty block element where the caret is at
					if ((newWrappers.length > 1 || !isBlock(node)) && childCount === 0) {
						dom.remove(node, 1);
						return;
					}

					if (format.inline || format.wrapper) {
						// Merges the current node with it's children of similar type to reduce the number of elements
						if (!format.exact && childCount === 1)
							node = mergeStyles(node);

						// Remove/merge children
						each(formatList, function(format) {
							// Merge all children of similar type will move styles from child to parent
							// this: <span style="color:red"><b><span style="color:red; font-size:10px">text</span></b></span>
							// will become: <span style="color:red"><b><span style="font-size:10px">text</span></b></span>
							each(dom.select(format.inline, node), function(child) {
								var parent;

								// When wrap_links is set to false we don't want
								// to remove the format on children within links
								if (format.wrap_links === false) {
									parent = child.parentNode;

									do {
										if (parent.nodeName === 'A')
											return;
									} while (parent = parent.parentNode);
								}

								removeFormat(format, vars, child, format.exact ? child : null);
							});
						});

						// Remove child if direct parent is of same type
						if (matchNode(node.parentNode, name, vars)) {
							dom.remove(node, 1);
							node = 0;
							return TRUE;
						}

						// Look for parent with similar style format
						if (format.merge_with_parents) {
							dom.getParent(node.parentNode, function(parent) {
								if (matchNode(parent, name, vars)) {
									dom.remove(node, 1);
									node = 0;
									return TRUE;
								}
							});
						}

						// Merge next and previous siblings if they are similar <b>text</b><b>text</b> becomes <b>texttext</b>
						if (node && format.merge_siblings !== false) {
							node = mergeSiblings(getNonWhiteSpaceSibling(node), node);
							node = mergeSiblings(node, getNonWhiteSpaceSibling(node, TRUE));
						}
					}
				});
			};

			if (format) {
				if (node) {
					if (node.nodeType) {
						rng = dom.createRng();
						rng.setStartBefore(node);
						rng.setEndAfter(node);
						applyRngStyle(expandRng(rng, formatList), null, true);
					} else {
						applyRngStyle(node, null, true);
					}
				} else {
					if (!isCollapsed || !format.inline || dom.select('td.mceSelected,th.mceSelected').length) {
						// Obtain selection node before selection is unselected by applyRngStyle()
						var curSelNode = ed.selection.getNode();

						// Apply formatting to selection
						ed.selection.setRng(adjustSelectionToVisibleSelection());
						bookmark = selection.getBookmark();
						applyRngStyle(expandRng(selection.getRng(TRUE), formatList), bookmark);

						// Colored nodes should be underlined so that the color of the underline matches the text color.
						if (format.styles && (format.styles.color || format.styles.textDecoration)) {
							tinymce.walk(curSelNode, processUnderlineAndColor, 'childNodes');
							processUnderlineAndColor(curSelNode);
						}

						selection.moveToBookmark(bookmark);
						selection.setRng(moveStart(selection.getRng(TRUE)));
						ed.nodeChanged();
					} else
						performCaretAction('apply', name, vars);
				}
			}
		};

		function remove(name, vars, node) {
			var formatList = get(name), format = formatList[0], bookmark, i, rng;
			function moveStart(rng) {
				var container = rng.startContainer,
					offset = rng.startOffset,
					walker, node, nodes, tmpNode;

				// Convert text node into index if possible
				if (container.nodeType == 3 && offset >= container.nodeValue.length - 1) {
					container = container.parentNode;
					offset = nodeIndex(container) + 1;
				}

				// Move startContainer/startOffset in to a suitable node
				if (container.nodeType == 1) {
					nodes = container.childNodes;
					container = nodes[Math.min(offset, nodes.length - 1)];
					walker = new TreeWalker(container);

					// If offset is at end of the parent node walk to the next one
					if (offset > nodes.length - 1)
						walker.next();

					for (node = walker.current(); node; node = walker.next()) {
						if (node.nodeType == 3 && !isWhiteSpaceNode(node)) {
							// IE has a "neat" feature where it moves the start node into the closest element
							// we can avoid this by inserting an element before it and then remove it after we set the selection
							tmpNode = dom.create('a', null, INVISIBLE_CHAR);
							node.parentNode.insertBefore(tmpNode, node);

							// Set selection and remove tmpNode
							rng.setStart(node, 0);
							selection.setRng(rng);
							dom.remove(tmpNode);

							return;
						}
					}
				}
			};

			// Merges the styles for each node
			function process(node) {
				var children, i, l;

				// Grab the children first since the nodelist might be changed
				children = tinymce.grep(node.childNodes);

				// Process current node
				for (i = 0, l = formatList.length; i < l; i++) {
					if (removeFormat(formatList[i], vars, node, node))
						break;
				}

				// Process the children
				if (format.deep) {
					for (i = 0, l = children.length; i < l; i++)
						process(children[i]);
				}
			};

			function findFormatRoot(container) {
				var formatRoot;

				// Find format root
				each(getParents(container.parentNode).reverse(), function(parent) {
					var format;

					// Find format root element
					if (!formatRoot && parent.id != '_start' && parent.id != '_end') {
						// Is the node matching the format we are looking for
						format = matchNode(parent, name, vars);
						if (format && format.split !== false)
							formatRoot = parent;
					}
				});

				return formatRoot;
			};

			function wrapAndSplit(format_root, container, target, split) {
				var parent, clone, lastClone, firstClone, i, formatRootParent;

				// Format root found then clone formats and split it
				if (format_root) {
					formatRootParent = format_root.parentNode;

					for (parent = container.parentNode; parent && parent != formatRootParent; parent = parent.parentNode) {
						clone = parent.cloneNode(FALSE);

						for (i = 0; i < formatList.length; i++) {
							if (removeFormat(formatList[i], vars, clone, clone)) {
								clone = 0;
								break;
							}
						}

						// Build wrapper node
						if (clone) {
							if (lastClone)
								clone.appendChild(lastClone);

							if (!firstClone)
								firstClone = clone;

							lastClone = clone;
						}
					}

					// Never split block elements if the format is mixed
					if (split && (!format.mixed || !isBlock(format_root)))
						container = dom.split(format_root, container);

					// Wrap container in cloned formats
					if (lastClone) {
						target.parentNode.insertBefore(lastClone, target);
						firstClone.appendChild(target);
					}
				}

				return container;
			};

			function splitToFormatRoot(container) {
				return wrapAndSplit(findFormatRoot(container), container, container, true);
			};

			function unwrap(start) {
				var node = dom.get(start ? '_start' : '_end'),
					out = node[start ? 'firstChild' : 'lastChild'];

				// If the end is placed within the start the result will be removed
				// So this checks if the out node is a bookmark node if it is it
				// checks for another more suitable node
				if (isBookmarkNode(out))
					out = out[start ? 'firstChild' : 'lastChild'];

				dom.remove(node, true);

				return out;
			};

			function removeRngStyle(rng) {
				var startContainer, endContainer;

				rng = expandRng(rng, formatList, TRUE);

				if (format.split) {
					startContainer = getContainer(rng, TRUE);
					endContainer = getContainer(rng);

					if (startContainer != endContainer) {
						// Wrap start/end nodes in span element since these might be cloned/moved
						startContainer = wrap(startContainer, 'span', {id : '_start', 'data-mce-type' : 'bookmark'});
						endContainer = wrap(endContainer, 'span', {id : '_end', 'data-mce-type' : 'bookmark'});

						// Split start/end
						splitToFormatRoot(startContainer);
						splitToFormatRoot(endContainer);

						// Unwrap start/end to get real elements again
						startContainer = unwrap(TRUE);
						endContainer = unwrap();
					} else
						startContainer = endContainer = splitToFormatRoot(startContainer);

					// Update range positions since they might have changed after the split operations
					rng.startContainer = startContainer.parentNode;
					rng.startOffset = nodeIndex(startContainer);
					rng.endContainer = endContainer.parentNode;
					rng.endOffset = nodeIndex(endContainer) + 1;
				}

				// Remove items between start/end
				rangeUtils.walk(rng, function(nodes) {
					each(nodes, function(node) {
						process(node);

						// Remove parent span if it only contains text-decoration: underline, yet a parent node is also underlined.
						if (node.nodeType === 1 && ed.dom.getStyle(node, 'text-decoration') === 'underline' && node.parentNode && getTextDecoration(node.parentNode) === 'underline') {
							removeFormat({'deep': false, 'exact': true, 'inline': 'span', 'styles': {'textDecoration' : 'underline'}}, null, node);
						}
					});
				});
			};

			// Handle node
			if (node) {
				if (node.nodeType) {
					rng = dom.createRng();
					rng.setStartBefore(node);
					rng.setEndAfter(node);
					removeRngStyle(rng);
				} else {
					removeRngStyle(node);
				}

				return;
			}

			if (!selection.isCollapsed() || !format.inline || dom.select('td.mceSelected,th.mceSelected').length) {
				bookmark = selection.getBookmark();
				removeRngStyle(selection.getRng(TRUE));
				selection.moveToBookmark(bookmark);

				// Check if start element still has formatting then we are at: "<b>text|</b>text" and need to move the start into the next text node
				if (format.inline && match(name, vars, selection.getStart())) {
					moveStart(selection.getRng(true));
				}

				ed.nodeChanged();
			} else
				performCaretAction('remove', name, vars);

			// When you remove formatting from a table cell in WebKit (cell, not the contents of a cell) there is a rendering issue with column width
			if (tinymce.isWebKit) {
				ed.execCommand('mceCleanup');
			}
		};

		function toggle(name, vars, node) {
			var fmt = get(name);

			if (match(name, vars, node) && (!('toggle' in fmt[0]) || fmt[0]['toggle']))
				remove(name, vars, node);
			else
				apply(name, vars, node);
		};

		function matchNode(node, name, vars, similar) {
			var formatList = get(name), format, i, classes;

			function matchItems(node, format, item_name) {
				var key, value, items = format[item_name], i;

				// Custom match
				if (format.onmatch) {
					return format.onmatch(node, format, item_name);
				}

				// Check all items
				if (items) {
					// Non indexed object
					if (items.length === undefined) {
						for (key in items) {
							if (items.hasOwnProperty(key)) {
								if (item_name === 'attributes')
									value = dom.getAttrib(node, key);
								else
									value = getStyle(node, key);

								if (similar && !value && !format.exact)
									return;

								if ((!similar || format.exact) && !isEq(value, replaceVars(items[key], vars)))
									return;
							}
						}
					} else {
						// Only one match needed for indexed arrays
						for (i = 0; i < items.length; i++) {
							if (item_name === 'attributes' ? dom.getAttrib(node, items[i]) : getStyle(node, items[i]))
								return format;
						}
					}
				}

				return format;
			};

			if (formatList && node) {
				// Check each format in list
				for (i = 0; i < formatList.length; i++) {
					format = formatList[i];

					// Name name, attributes, styles and classes
					if (matchName(node, format) && matchItems(node, format, 'attributes') && matchItems(node, format, 'styles')) {
						// Match classes
						if (classes = format.classes) {
							for (i = 0; i < classes.length; i++) {
								if (!dom.hasClass(node, classes[i]))
									return;
							}
						}

						return format;
					}
				}
			}
		};

		function match(name, vars, node) {
			var startNode;

			function matchParents(node) {
				// Find first node with similar format settings
				node = dom.getParent(node, function(node) {
					return !!matchNode(node, name, vars, true);
				});

				// Do an exact check on the similar format element
				return matchNode(node, name, vars);
			};

			// Check specified node
			if (node)
				return matchParents(node);

			// Check selected node
			node = selection.getNode();
			if (matchParents(node))
				return TRUE;

			// Check start node if it's different
			startNode = selection.getStart();
			if (startNode != node) {
				if (matchParents(startNode))
					return TRUE;
			}

			return FALSE;
		};

		function matchAll(names, vars) {
			var startElement, matchedFormatNames = [], checkedMap = {}, i, ni, name;

			// Check start of selection for formats
			startElement = selection.getStart();
			dom.getParent(startElement, function(node) {
				var i, name;

				for (i = 0; i < names.length; i++) {
					name = names[i];

					if (!checkedMap[name] && matchNode(node, name, vars)) {
						checkedMap[name] = true;
						matchedFormatNames.push(name);
					}
				}
			});

			return matchedFormatNames;
		};

		function canApply(name) {
			var formatList = get(name), startNode, parents, i, x, selector;

			if (formatList) {
				startNode = selection.getStart();
				parents = getParents(startNode);

				for (x = formatList.length - 1; x >= 0; x--) {
					selector = formatList[x].selector;

					// Format is not selector based, then always return TRUE
					if (!selector)
						return TRUE;

					for (i = parents.length - 1; i >= 0; i--) {
						if (dom.is(parents[i], selector))
							return TRUE;
					}
				}
			}

			return FALSE;
		};

		// Expose to public
		tinymce.extend(this, {
			get : get,
			register : register,
			apply : apply,
			remove : remove,
			toggle : toggle,
			match : match,
			matchAll : matchAll,
			matchNode : matchNode,
			canApply : canApply
		});

		// Private functions

		function matchName(node, format) {
			// Check for inline match
			if (isEq(node, format.inline))
				return TRUE;

			// Check for block match
			if (isEq(node, format.block))
				return TRUE;

			// Check for selector match
			if (format.selector)
				return dom.is(node, format.selector);
		};

		function isEq(str1, str2) {
			str1 = str1 || '';
			str2 = str2 || '';

			str1 = '' + (str1.nodeName || str1);
			str2 = '' + (str2.nodeName || str2);

			return str1.toLowerCase() == str2.toLowerCase();
		};

		function getStyle(node, name) {
			var styleVal = dom.getStyle(node, name);

			// Force the format to hex
			if (name == 'color' || name == 'backgroundColor')
				styleVal = dom.toHex(styleVal);

			// Opera will return bold as 700
			if (name == 'fontWeight' && styleVal == 700)
				styleVal = 'bold';

			return '' + styleVal;
		};

		function replaceVars(value, vars) {
			if (typeof(value) != "string")
				value = value(vars);
			else if (vars) {
				value = value.replace(/%(\w+)/g, function(str, name) {
					return vars[name] || str;
				});
			}

			return value;
		};

		function isWhiteSpaceNode(node) {
			return node && node.nodeType === 3 && /^([\t \r\n]+|)$/.test(node.nodeValue);
		};

		function wrap(node, name, attrs) {
			var wrapper = dom.create(name, attrs);

			node.parentNode.insertBefore(wrapper, node);
			wrapper.appendChild(node);

			return wrapper;
		};

		function expandRng(rng, format, remove) {
			var startContainer = rng.startContainer,
				startOffset = rng.startOffset,
				endContainer = rng.endContainer,
				endOffset = rng.endOffset, sibling, lastIdx, leaf, endPoint;

			// This function walks up the tree if there is no siblings before/after the node
			function findParentContainer(start) {
				var container, parent, child, sibling, siblingName;

				container = parent = start ? startContainer : endContainer;
				siblingName = start ? 'previousSibling' : 'nextSibling';
				root = dom.getRoot();

				// If it's a text node and the offset is inside the text
				if (container.nodeType == 3 && !isWhiteSpaceNode(container)) {
					if (start ? startOffset > 0 : endOffset < container.nodeValue.length) {
						return container;
					}
				}

				for (;;) {
					// Stop expanding on block elements or root depending on format
					if (parent == root || (!format[0].block_expand && isBlock(parent)))
						return parent;

					// Walk left/right
					for (sibling = parent[siblingName]; sibling; sibling = sibling[siblingName]) {
						if (!isBookmarkNode(sibling) && !isWhiteSpaceNode(sibling)) {
							return parent;
						}
					}

					// Check if we can move up are we at root level or body level
					parent = parent.parentNode;
				}

				return container;
			};

			// This function walks down the tree to find the leaf at the selection.
			// The offset is also returned as if node initially a leaf, the offset may be in the middle of the text node.
			function findLeaf(node, offset) {
				if (offset === undefined)
					offset = node.nodeType === 3 ? node.length : node.childNodes.length;
				while (node && node.hasChildNodes()) {
					node = node.childNodes[offset];
					if (node)
						offset = node.nodeType === 3 ? node.length : node.childNodes.length;
				}
				return { node: node, offset: offset };
			}

			// If index based start position then resolve it
			if (startContainer.nodeType == 1 && startContainer.hasChildNodes()) {
				lastIdx = startContainer.childNodes.length - 1;
				startContainer = startContainer.childNodes[startOffset > lastIdx ? lastIdx : startOffset];

				if (startContainer.nodeType == 3)
					startOffset = 0;
			}

			// If index based end position then resolve it
			if (endContainer.nodeType == 1 && endContainer.hasChildNodes()) {
				lastIdx = endContainer.childNodes.length - 1;
				endContainer = endContainer.childNodes[endOffset > lastIdx ? lastIdx : endOffset - 1];

				if (endContainer.nodeType == 3)
					endOffset = endContainer.nodeValue.length;
			}

			// Exclude bookmark nodes if possible
			if (isBookmarkNode(startContainer.parentNode) || isBookmarkNode(startContainer)) {
				startContainer = isBookmarkNode(startContainer) ? startContainer : startContainer.parentNode;
				startContainer = startContainer.nextSibling || startContainer;

				if (startContainer.nodeType == 3)
					startOffset = 0;
			}

			if (isBookmarkNode(endContainer.parentNode) || isBookmarkNode(endContainer)) {
				endContainer = isBookmarkNode(endContainer) ? endContainer : endContainer.parentNode;
				endContainer = endContainer.previousSibling || endContainer;

				if (endContainer.nodeType == 3)
					endOffset = endContainer.length;
			}

			if (format[0].inline) {
				if (rng.collapsed) {
					function findWordEndPoint(container, offset, start) {
						var walker, node, pos, lastTextNode;

						function findSpace(node, offset) {
							var pos, pos2, str = node.nodeValue;

							if (typeof(offset) == "undefined") {
								offset = start ? str.length : 0;
							}

							if (start) {
								pos = str.lastIndexOf(' ', offset);
								pos2 = str.lastIndexOf('\u00a0', offset);
								pos = pos > pos2 ? pos : pos2;

								// Include the space on remove to avoid tag soup
								if (pos !== -1 && !remove) {
									pos++;
								}
							} else {
								pos = str.indexOf(' ', offset);
								pos2 = str.indexOf('\u00a0', offset);
								pos = pos !== -1 && (pos2 === -1 || pos < pos2) ? pos : pos2;
							}

							return pos;
						};

						if (container.nodeType === 3) {
							pos = findSpace(container, offset);

							if (pos !== -1) {
								return {container : container, offset : pos};
							}

							lastTextNode = container;
						}

						// Walk the nodes inside the block
						walker = new TreeWalker(container, dom.getParent(container, isBlock) || ed.getBody());
						while (node = walker[start ? 'prev' : 'next']()) {
							if (node.nodeType === 3) {
								lastTextNode = node;
								pos = findSpace(node);

								if (pos !== -1) {
									return {container : node, offset : pos};
								}
							} else if (isBlock(node)) {
								break;
							}
						}

						if (lastTextNode) {
							if (start) {
								offset = 0;
							} else {
								offset = lastTextNode.length;
							}

							return {container: lastTextNode, offset: offset};
						}
					}

					// Expand left to closest word boundery
					endPoint = findWordEndPoint(startContainer, startOffset, true);
					if (endPoint) {
						startContainer = endPoint.container;
						startOffset = endPoint.offset;
					}

					// Expand right to closest word boundery
					endPoint = findWordEndPoint(endContainer, endOffset);
					if (endPoint) {
						endContainer = endPoint.container;
						endOffset = endPoint.offset;
					}
				}

				// Avoid applying formatting to a trailing space.
				leaf = findLeaf(endContainer, endOffset);
				if (leaf.node) {
					while (leaf.node && leaf.offset === 0 && leaf.node.previousSibling)
						leaf = findLeaf(leaf.node.previousSibling);

					if (leaf.node && leaf.offset > 0 && leaf.node.nodeType === 3 &&
							leaf.node.nodeValue.charAt(leaf.offset - 1) === ' ') {

						if (leaf.offset > 1) {
							endContainer = leaf.node;
							endContainer.splitText(leaf.offset - 1);
						} else if (leaf.node.previousSibling) {
							// TODO: Figure out why this is in here
							//endContainer = leaf.node.previousSibling;
						}
					}
				}
			}

			// Move start/end point up the tree if the leaves are sharp and if we are in different containers
			// Example * becomes !: !<p><b><i>*text</i><i>text*</i></b></p>!
			// This will reduce the number of wrapper elements that needs to be created
			// Move start point up the tree
			if (format[0].inline || format[0].block_expand) {
				if (!format[0].inline || (startContainer.nodeType != 3 || startOffset === 0)) {
					startContainer = findParentContainer(true);
				}

				if (!format[0].inline || (endContainer.nodeType != 3 || endOffset === endContainer.nodeValue.length)) {
					endContainer = findParentContainer();
				}
			}

			// Expand start/end container to matching selector
			if (format[0].selector && format[0].expand !== FALSE && !format[0].inline) {
				function findSelectorEndPoint(container, sibling_name) {
					var parents, i, y, curFormat;

					if (container.nodeType == 3 && container.nodeValue.length == 0 && container[sibling_name])
						container = container[sibling_name];

					parents = getParents(container);
					for (i = 0; i < parents.length; i++) {
						for (y = 0; y < format.length; y++) {
							curFormat = format[y];

							// If collapsed state is set then skip formats that doesn't match that
							if ("collapsed" in curFormat && curFormat.collapsed !== rng.collapsed)
								continue;

							if (dom.is(parents[i], curFormat.selector))
								return parents[i];
						}
					}

					return container;
				};

				// Find new startContainer/endContainer if there is better one
				startContainer = findSelectorEndPoint(startContainer, 'previousSibling');
				endContainer = findSelectorEndPoint(endContainer, 'nextSibling');
			}

			// Expand start/end container to matching block element or text node
			if (format[0].block || format[0].selector) {
				function findBlockEndPoint(container, sibling_name, sibling_name2) {
					var node;

					// Expand to block of similar type
					if (!format[0].wrapper)
						node = dom.getParent(container, format[0].block);

					// Expand to first wrappable block element or any block element
					if (!node)
						node = dom.getParent(container.nodeType == 3 ? container.parentNode : container, isBlock);

					// Exclude inner lists from wrapping
					if (node && format[0].wrapper)
						node = getParents(node, 'ul,ol').reverse()[0] || node;

					// Didn't find a block element look for first/last wrappable element
					if (!node) {
						node = container;

						while (node[sibling_name] && !isBlock(node[sibling_name])) {
							node = node[sibling_name];

							// Break on BR but include it will be removed later on
							// we can't remove it now since we need to check if it can be wrapped
							if (isEq(node, 'br'))
								break;
						}
					}

					return node || container;
				};

				// Find new startContainer/endContainer if there is better one
				startContainer = findBlockEndPoint(startContainer, 'previousSibling');
				endContainer = findBlockEndPoint(endContainer, 'nextSibling');

				// Non block element then try to expand up the leaf
				if (format[0].block) {
					if (!isBlock(startContainer))
						startContainer = findParentContainer(true);

					if (!isBlock(endContainer))
						endContainer = findParentContainer();
				}
			}

			// Setup index for startContainer
			if (startContainer.nodeType == 1) {
				startOffset = nodeIndex(startContainer);
				startContainer = startContainer.parentNode;
			}

			// Setup index for endContainer
			if (endContainer.nodeType == 1) {
				endOffset = nodeIndex(endContainer) + 1;
				endContainer = endContainer.parentNode;
			}

			// Return new range like object
			return {
				startContainer : startContainer,
				startOffset : startOffset,
				endContainer : endContainer,
				endOffset : endOffset
			};
		}

		function removeFormat(format, vars, node, compare_node) {
			var i, attrs, stylesModified;

			// Check if node matches format
			if (!matchName(node, format))
				return FALSE;

			// Should we compare with format attribs and styles
			if (format.remove != 'all') {
				// Remove styles
				each(format.styles, function(value, name) {
					value = replaceVars(value, vars);

					// Indexed array
					if (typeof(name) === 'number') {
						name = value;
						compare_node = 0;
					}

					if (!compare_node || isEq(getStyle(compare_node, name), value))
						dom.setStyle(node, name, '');

					stylesModified = 1;
				});

				// Remove style attribute if it's empty
				if (stylesModified && dom.getAttrib(node, 'style') == '') {
					node.removeAttribute('style');
					node.removeAttribute('data-mce-style');
				}

				// Remove attributes
				each(format.attributes, function(value, name) {
					var valueOut;

					value = replaceVars(value, vars);

					// Indexed array
					if (typeof(name) === 'number') {
						name = value;
						compare_node = 0;
					}

					if (!compare_node || isEq(dom.getAttrib(compare_node, name), value)) {
						// Keep internal classes
						if (name == 'class') {
							value = dom.getAttrib(node, name);
							if (value) {
								// Build new class value where everything is removed except the internal prefixed classes
								valueOut = '';
								each(value.split(/\s+/), function(cls) {
									if (/mce\w+/.test(cls))
										valueOut += (valueOut ? ' ' : '') + cls;
								});

								// We got some internal classes left
								if (valueOut) {
									dom.setAttrib(node, name, valueOut);
									return;
								}
							}
						}

						// IE6 has a bug where the attribute doesn't get removed correctly
						if (name == "class")
							node.removeAttribute('className');

						// Remove mce prefixed attributes
						if (MCE_ATTR_RE.test(name))
							node.removeAttribute('data-mce-' + name);

						node.removeAttribute(name);
					}
				});

				// Remove classes
				each(format.classes, function(value) {
					value = replaceVars(value, vars);

					if (!compare_node || dom.hasClass(compare_node, value))
						dom.removeClass(node, value);
				});

				// Check for non internal attributes
				attrs = dom.getAttribs(node);
				for (i = 0; i < attrs.length; i++) {
					if (attrs[i].nodeName.indexOf('_') !== 0)
						return FALSE;
				}
			}

			// Remove the inline child if it's empty for example <b> or <span>
			if (format.remove != 'none') {
				removeNode(node, format);
				return TRUE;
			}
		};

		function removeNode(node, format) {
			var parentNode = node.parentNode, rootBlockElm;

			if (format.block) {
				if (!forcedRootBlock) {
					function find(node, next, inc) {
						node = getNonWhiteSpaceSibling(node, next, inc);

						return !node || (node.nodeName == 'BR' || isBlock(node));
					};

					// Append BR elements if needed before we remove the block
					if (isBlock(node) && !isBlock(parentNode)) {
						if (!find(node, FALSE) && !find(node.firstChild, TRUE, 1))
							node.insertBefore(dom.create('br'), node.firstChild);

						if (!find(node, TRUE) && !find(node.lastChild, FALSE, 1))
							node.appendChild(dom.create('br'));
					}
				} else {
					// Wrap the block in a forcedRootBlock if we are at the root of document
					if (parentNode == dom.getRoot()) {
						if (!format.list_block || !isEq(node, format.list_block)) {
							each(tinymce.grep(node.childNodes), function(node) {
								if (isValid(forcedRootBlock, node.nodeName.toLowerCase())) {
									if (!rootBlockElm)
										rootBlockElm = wrap(node, forcedRootBlock);
									else
										rootBlockElm.appendChild(node);
								} else
									rootBlockElm = 0;
							});
						}
					}
				}
			}

			// Never remove nodes that isn't the specified inline element if a selector is specified too
			if (format.selector && format.inline && !isEq(format.inline, node))
				return;

			dom.remove(node, 1);
		};

		function getNonWhiteSpaceSibling(node, next, inc) {
			if (node) {
				next = next ? 'nextSibling' : 'previousSibling';

				for (node = inc ? node : node[next]; node; node = node[next]) {
					if (node.nodeType == 1 || !isWhiteSpaceNode(node))
						return node;
				}
			}
		};

		function isBookmarkNode(node) {
			return node && node.nodeType == 1 && node.getAttribute('data-mce-type') == 'bookmark';
		};

		function mergeSiblings(prev, next) {
			var marker, sibling, tmpSibling;

			function compareElements(node1, node2) {
				// Not the same name
				if (node1.nodeName != node2.nodeName)
					return FALSE;

				function getAttribs(node) {
					var attribs = {};

					each(dom.getAttribs(node), function(attr) {
						var name = attr.nodeName.toLowerCase();

						// Don't compare internal attributes or style
						if (name.indexOf('_') !== 0 && name !== 'style')
							attribs[name] = dom.getAttrib(node, name);
					});

					return attribs;
				};

				function compareObjects(obj1, obj2) {
					var value, name;

					for (name in obj1) {
						// Obj1 has item obj2 doesn't have
						if (obj1.hasOwnProperty(name)) {
							value = obj2[name];

							// Obj2 doesn't have obj1 item
							if (value === undefined)
								return FALSE;

							// Obj2 item has a different value
							if (obj1[name] != value)
								return FALSE;

							// Delete similar value
							delete obj2[name];
						}
					}

					// Check if obj 2 has something obj 1 doesn't have
					for (name in obj2) {
						// Obj2 has item obj1 doesn't have
						if (obj2.hasOwnProperty(name))
							return FALSE;
					}

					return TRUE;
				};

				// Attribs are not the same
				if (!compareObjects(getAttribs(node1), getAttribs(node2)))
					return FALSE;

				// Styles are not the same
				if (!compareObjects(dom.parseStyle(dom.getAttrib(node1, 'style')), dom.parseStyle(dom.getAttrib(node2, 'style'))))
					return FALSE;

				return TRUE;
			};

			// Check if next/prev exists and that they are elements
			if (prev && next) {
				function findElementSibling(node, sibling_name) {
					for (sibling = node; sibling; sibling = sibling[sibling_name]) {
						if (sibling.nodeType == 3 && sibling.nodeValue.length !== 0)
							return node;

						if (sibling.nodeType == 1 && !isBookmarkNode(sibling))
							return sibling;
					}

					return node;
				};

				// If previous sibling is empty then jump over it
				prev = findElementSibling(prev, 'previousSibling');
				next = findElementSibling(next, 'nextSibling');

				// Compare next and previous nodes
				if (compareElements(prev, next)) {
					// Append nodes between
					for (sibling = prev.nextSibling; sibling && sibling != next;) {
						tmpSibling = sibling;
						sibling = sibling.nextSibling;
						prev.appendChild(tmpSibling);
					}

					// Remove next node
					dom.remove(next);

					// Move children into prev node
					each(tinymce.grep(next.childNodes), function(node) {
						prev.appendChild(node);
					});

					return prev;
				}
			}

			return next;
		};

		function isTextBlock(name) {
			return /^(h[1-6]|p|div|pre|address|dl|dt|dd)$/.test(name);
		};

		function getContainer(rng, start) {
			var container, offset, lastIdx, walker;

			container = rng[start ? 'startContainer' : 'endContainer'];
			offset = rng[start ? 'startOffset' : 'endOffset'];

			if (container.nodeType == 1) {
				lastIdx = container.childNodes.length - 1;

				if (!start && offset)
					offset--;

				container = container.childNodes[offset > lastIdx ? lastIdx : offset];
			}

			// If start text node is excluded then walk to the next node
			if (container.nodeType === 3 && start && offset >= container.nodeValue.length) {
				container = new TreeWalker(container, ed.getBody()).next() || container;
			}

			// If end text node is excluded then walk to the previous node
			if (container.nodeType === 3 && !start && offset == 0) {
				container = new TreeWalker(container, ed.getBody()).prev() || container;
			}

			return container;
		};

		function performCaretAction(type, name, vars) {
			var invisibleChar, caretContainerId = '_mce_caret', debug = ed.settings.caret_debug;

			// Setup invisible character use zero width space on Gecko since it doesn't change the heigt of the container
			invisibleChar = tinymce.isGecko ? '\u200B' : INVISIBLE_CHAR;

			// Creates a caret container bogus element
			function createCaretContainer(fill) {
				var caretContainer = dom.create('span', {id: caretContainerId, 'data-mce-bogus': true, style: debug ? 'color:red' : ''});

				if (fill) {
					caretContainer.appendChild(ed.getDoc().createTextNode(invisibleChar));
				}

				return caretContainer;
			};

			function isCaretContainerEmpty(node, nodes) {
				while (node) {
					if ((node.nodeType === 3 && node.nodeValue !== invisibleChar) || node.childNodes.length > 1) {
						return false;
					}

					// Collect nodes
					if (nodes && node.nodeType === 1) {
						nodes.push(node);
					}

					node = node.firstChild;
				}

				return true;
			};
			
			// Returns any parent caret container element
			function getParentCaretContainer(node) {
				while (node) {
					if (node.id === caretContainerId) {
						return node;
					}

					node = node.parentNode;
				}
			};

			// Finds the first text node in the specified node
			function findFirstTextNode(node) {
				var walker;

				if (node) {
					walker = new TreeWalker(node, node);

					for (node = walker.current(); node; node = walker.next()) {
						if (node.nodeType === 3) {
							return node;
						}
					}
				}
			};

			// Removes the caret container for the specified node or all on the current document
			function removeCaretContainer(node, move_caret) {
				var child, rng;

				if (!node) {
					node = getParentCaretContainer(selection.getStart());

					if (!node) {
						while (node = dom.get(caretContainerId)) {
							removeCaretContainer(node, false);
						}
					}
				} else {
					rng = selection.getRng(true);

					if (isCaretContainerEmpty(node)) {
						if (move_caret !== false) {
							rng.setStartBefore(node);
							rng.setEndBefore(node);
						}

						dom.remove(node);
					} else {
						child = findFirstTextNode(node);
						child = child.deleteData(0, 1);
						dom.remove(node, 1);
					}

					selection.setRng(rng);
				}
			};
			
			// Applies formatting to the caret postion
			function applyCaretFormat() {
				var rng, caretContainer, textNode, offset, bookmark, container, text;

				rng = selection.getRng(true);
				offset = rng.startOffset;
				container = rng.startContainer;
				text = container.nodeValue;

				caretContainer = getParentCaretContainer(selection.getStart());
				if (caretContainer) {
					textNode = findFirstTextNode(caretContainer);
				}

				// Expand to word is caret is in the middle of a text node and the char before/after is a alpha numeric character
				if (text && offset > 0 && offset < text.length && /\w/.test(text.charAt(offset)) && /\w/.test(text.charAt(offset - 1))) {
					// Get bookmark of caret position
					bookmark = selection.getBookmark();

					// Collapse bookmark range (WebKit)
					rng.collapse(true);

					// Expand the range to the closest word and split it at those points
					rng = expandRng(rng, get(name));
					rng = rangeUtils.split(rng);

					// Apply the format to the range
					apply(name, vars, rng);

					// Move selection back to caret position
					selection.moveToBookmark(bookmark);
				} else {
					if (!caretContainer || textNode.nodeValue !== invisibleChar) {
						caretContainer = createCaretContainer(true);
						textNode = caretContainer.firstChild;

						rng.insertNode(caretContainer);
						offset = 1;

						apply(name, vars, caretContainer);
					} else {
						apply(name, vars, caretContainer);
					}

					// Move selection to text node
					selection.setCursorLocation(textNode, offset);
				}
			};

			function removeCaretFormat() {
				var rng = selection.getRng(true), container, offset, bookmark,
					hasContentAfter, node, formatNode, parents = [], i, caretContainer;

				container = rng.startContainer;
				offset = rng.startOffset;
				node = container;

				if (container.nodeType == 3) {
					if (offset != container.nodeValue.length || container.nodeValue === invisibleChar) {
						hasContentAfter = true;
					}

					node = node.parentNode;
				}

				while (node) {
					if (matchNode(node, name, vars)) {
						formatNode = node;
						break;
					}

					if (node.nextSibling) {
						hasContentAfter = true;
					}

					parents.push(node);
					node = node.parentNode;
				}

				// Node doesn't have the specified format
				if (!formatNode) {
					return;
				}

				// Is there contents after the caret then remove the format on the element
				if (hasContentAfter) {
					// Get bookmark of caret position
					bookmark = selection.getBookmark();

					// Collapse bookmark range (WebKit)
					rng.collapse(true);

					// Expand the range to the closest word and split it at those points
					rng = expandRng(rng, get(name), true);
					rng = rangeUtils.split(rng);

					// Remove the format from the range
					remove(name, vars, rng);

					// Move selection back to caret position
					selection.moveToBookmark(bookmark);
				} else {
					caretContainer = createCaretContainer();

					node = caretContainer;
					for (i = parents.length - 1; i >= 0; i--) {
						node.appendChild(parents[i].cloneNode(false));
						node = node.firstChild;
					}

					// Insert invisible character into inner most format element
					node.appendChild(dom.doc.createTextNode(invisibleChar));
					node = node.firstChild;

					// Insert caret container after the formated node
					dom.insertAfter(caretContainer, formatNode);

					// Move selection to text node
					selection.setCursorLocation(node, 1);
				}
			};

			// Mark current caret container elements as bogus when getting the contents so we don't end up with empty elements
			ed.onBeforeGetContent.addToTop(function() {
				var nodes = [], i;

				if (isCaretContainerEmpty(getParentCaretContainer(selection.getStart()), nodes)) {
					// Mark children
					i = nodes.length;
					while (i--) {
						dom.setAttrib(nodes[i], 'data-mce-bogus', '1');
					}
				}
			});

			// Remove caret container on mouse up and on key up
			tinymce.each('onMouseUp onKeyUp'.split(' '), function(name) {
				ed[name].addToTop(function() {
					removeCaretContainer();
				});
			});

			// Remove caret container on keydown and it's a backspace, enter or left/right arrow keys
			ed.onKeyDown.addToTop(function(ed, e) {
				var keyCode = e.keyCode;

				if (keyCode == 8 || keyCode == 37 || keyCode == 39) {
					removeCaretContainer(getParentCaretContainer(selection.getStart()));
				}
			});

			// Do apply or remove caret format
			if (type == "apply") {
				applyCaretFormat();
			} else {
				removeCaretFormat();
			}
		};
	};
})(tinymce);

tinymce.onAddEditor.add(function(tinymce, ed) {
	var filters, fontSizes, dom, settings = ed.settings;

	if (settings.inline_styles) {
		fontSizes = tinymce.explode(settings.font_size_legacy_values);

		function replaceWithSpan(node, styles) {
			tinymce.each(styles, function(value, name) {
				if (value)
					dom.setStyle(node, name, value);
			});

			dom.rename(node, 'span');
		};

		filters = {
			font : function(dom, node) {
				replaceWithSpan(node, {
					backgroundColor : node.style.backgroundColor,
					color : node.color,
					fontFamily : node.face,
					fontSize : fontSizes[parseInt(node.size) - 1]
				});
			},

			u : function(dom, node) {
				replaceWithSpan(node, {
					textDecoration : 'underline'
				});
			},

			strike : function(dom, node) {
				replaceWithSpan(node, {
					textDecoration : 'line-through'
				});
			}
		};

		function convert(editor, params) {
			dom = editor.dom;

			if (settings.convert_fonts_to_spans) {
				tinymce.each(dom.select('font,u,strike', params.node), function(node) {
					filters[node.nodeName.toLowerCase()](ed.dom, node);
				});
			}
		};

		ed.onPreProcess.add(convert);
		ed.onSetContent.add(convert);

		ed.onInit.add(function() {
			ed.selection.onSetContent.add(convert);
		});
	}
});

