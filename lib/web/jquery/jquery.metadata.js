/*
 * Metadata - jQuery plugin for parsing metadata from elements
 *
 * Copyright (c) 2006 John Resig, Yehuda Katz, Jï¿½Ã¶rn Zaefferer, Paul McLanahan
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.metadata.js 3640 2007-10-11 18:34:38Z pmclanahan $
 *
 */

/**
 * Sets the type of metadata to use. Metadata is encoded in JSON, and each property
 * in the JSON will become a property of the element itself.
 *
 * There are four supported types of metadata storage:
 *
 *   attr:  Inside an attribute. The name parameter indicates *which* attribute.
 *
 *   class: Inside the class attribute, wrapped in curly braces: { }
 *
 *   elem:  Inside a child element (e.g. a script tag). The
 *          name parameter indicates *which* element.
 *   html5: Values are stored in data-* attributes.
 *
 * The metadata for an element is loaded the first time the element is accessed via jQuery.
 *
 * As a result, you can define the metadata type, use $(expr) to load the metadata into the elements
 * matched by expr, then redefine the metadata type and run another $(expr) for other elements.
 *
 * @name $.metadata.setType
 *
 * @example <p id="one" class="some_class {item_id: 1, item_label: 'Label'}">This is a p</p>
 * @before $.metadata.setType("class")
 * @after $("#one").metadata().item_id == 1; $("#one").metadata().item_label == "Label"
 * @desc Reads metadata from the class attribute
 *
 * @example <p id="one" class="some_class" data="{item_id: 1, item_label: 'Label'}">This is a p</p>
 * @before $.metadata.setType("attr", "data")
 * @after $("#one").metadata().item_id == 1; $("#one").metadata().item_label == "Label"
 * @desc Reads metadata from a "data" attribute
 *
 * @example <p id="one" class="some_class"><script>{item_id: 1, item_label: 'Label'}</script>This is a p</p>
 * @before $.metadata.setType("elem", "script")
 * @after $("#one").metadata().item_id == 1; $("#one").metadata().item_label == "Label"
 * @desc Reads metadata from a nested script element
 *
 * @example <p id="one" class="some_class" data-item_id="1" data-item_label="Label">This is a p</p>
 * @before $.metadata.setType("html5")
 * @after $("#one").metadata().item_id == 1; $("#one").metadata().item_label == "Label"
 * @desc Reads metadata from a series of data-* attributes
 *
 * @param String type The encoding type
 * @param String name The name of the attribute to be used to get metadata (optional)
 * @cat Plugins/Metadata
 * @descr Sets the type of encoding to be used when loading metadata for the first time
 * @type undefined
 * @see metadata()
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(["jquery"], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {


    $.extend({
        metadata : {
            defaults : {
                type: 'class',
                name: 'metadata',
                cre: /({.*})/,
                single: 'metadata',
                meta:'validate'
            },
            setType: function( type, name ){
                this.defaults.type = type;
                this.defaults.name = name;
            },
            get: function( elem, opts ){
                var settings = $.extend({},this.defaults,opts);
                // check for empty string in single property
                if (!settings.single.length) {
                    settings.single = 'metadata';
                }
                if (!settings.meta.length) {
                    settings.meta = 'validate';
                }

                var data = $.data(elem, settings.single);
                // returned cached data if it already exists
                if ( data ) return data;

                data = "{}";

                var getData = function(data) {
                    if(typeof data != "string") return data;

                    if( data.indexOf('{') < 0 ) {
                        data = eval("(" + data + ")");
                    }
                }

                var getObject = function(data) {
                    if(typeof data != "string") return data;

                    data = eval("(" + data + ")");
                    return data;
                }

                if ( settings.type == "html5" ) {
                    var object = {};
                    $( elem.attributes ).each(function() {
                        var name = this.nodeName;
                        if (name.indexOf('data-' + settings.meta) === 0) {
                            name = name.replace(/^data-/, '');
                        }
                        else {
                            return true;
                        }
                        object[name] = getObject(this.value);
                    });
                } else {
                    if ( settings.type == "class" ) {
                        var m = settings.cre.exec( elem.className );
                        if ( m )
                            data = m[1];
                    } else if ( settings.type == "elem" ) {
                        if( !elem.getElementsByTagName ) return;
                        var e = elem.getElementsByTagName(settings.name);
                        if ( e.length )
                            data = $.trim(e[0].innerText);
                    } else if ( elem.getAttribute != undefined ) {
                        var attr = elem.getAttribute( settings.name );
                        if ( attr )
                            data = attr;
                    }
                    object = getObject(data.indexOf("{") < 0 ? "{" + data + "}" : data);
                }

                $.data( elem, settings.single, object );
                return object;
            }
        }
    });

    /**
     * Returns the metadata object for the first member of the jQuery object.
     *
     * @name metadata
     * @descr Returns element's metadata object
     * @param Object opts An object contianing settings to override the defaults
     * @type jQuery
     * @cat Plugins/Metadata
     */
    $.fn.metadata = function( opts ){
        return $.metadata.get( this[0], opts );
    };

}));
