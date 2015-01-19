/**
 * Handles additional ajax request for rendering user private content
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/cookies",
    "Magento_PageCache/js/comments"
], function($){
    "use strict";
    
    $.widget('mage.pageCache', {
        options: {
            url: '/',
            patternPlaceholderOpen: /^ BLOCK (.+) $/,
            patternPlaceholderClose: /^ \/BLOCK (.+) $/,
            versionCookieName: 'private_content_version',
            handles: []
        },
        _create: function () {
            var version = $.mage.cookies.get(this.options.versionCookieName);
            if (!version) {
                return ;
            }
            var placeholders = this._searchPlaceholders(this.element.comments());
            if (placeholders.length) {
                this._ajax(placeholders, version);
            }
        },
        _searchPlaceholders: function (elements) {
            var placeholders = [],
                tmp = {};
            if (!elements.length) {
                return placeholders;
            }
            for (var i = 0; i < elements.length; i++) {
                var el = elements[i],
                    matches = this.options.patternPlaceholderOpen.exec(el.nodeValue),
                    name = null;

                if (matches) {
                    name = matches[1];
                    tmp[name] = {
                        name: name,
                        openElement: el
                    };
                } else {
                    matches = this.options.patternPlaceholderClose.exec(el.nodeValue);
                    if (matches) {
                        name = matches[1];
                        if (tmp[name]) {
                            tmp[name].closeElement = el;
                            placeholders.push(tmp[name]);
                            delete tmp[name];
                        }
                    }
                }
            }
            return placeholders;
        },
        _replacePlaceholder: function(placeholder, html) {
            var parent = $(placeholder.openElement).parent(),
                contents = parent.contents(),
                startReplacing = false,
                prevSibling = null;
            for (var y = 0; y < contents.length; y++) {
                var element = contents[y];
                if (element == placeholder.openElement) {
                    startReplacing = true;
                }
                if (startReplacing) {
                    $(element).remove();
                } else if (element.nodeType != 8) {
                    //due to comment tag doesn't have siblings we try to find it manually
                    prevSibling = element;
                }
                if (element == placeholder.closeElement) {
                    break;
                }
            }
            if (prevSibling) {
                $(prevSibling).after(html);
            } else {
                $(parent).prepend(html);
            }
            // trigger event to use mage-data-init attribute
            $(parent).trigger('contentUpdated');
        },
        _ajax: function (placeholders, version) {
            var data = {
                blocks: [],
                handles: this.options.handles,
                version: version
            };
            for (var i = 0; i < placeholders.length; i++) {
                data.blocks.push(placeholders[i].name);
            }
            data.blocks = JSON.stringify(data.blocks.sort());
            data.handles = JSON.stringify(data.handles);
            $.ajax({
                url: this.options.url,
                data: data,
                type: 'GET',
                cache: true,
                dataType: 'json',
                context: this,
                success: function (response) {
                    for (var i = 0; i < placeholders.length; i++) {
                        var placeholder = placeholders[i];
                        if (!response.hasOwnProperty(placeholder.name)) {
                            continue;
                        }
                        this._replacePlaceholder(placeholder, response[placeholder.name]);
                    }
                }
            });
        }
    });
    
    return $.mage.pageCache;
});
