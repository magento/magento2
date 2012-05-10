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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

(function ($) {
    /**
     * Class for design editor
     */
    DesignEditor = function (opts) {
        /* Children storage for the period, when highlighting is disabled */
        this._children = {};

        /* Configuration options for design editor */
        this._options = {};

        var defaultOptions = {'cookie_highlighting_name': 'vde_highlighting'};
        $.extend(this._options, defaultOptions, opts);

        if (Mage.Cookies.get(this._options['cookie_highlighting_name']) == 'off') {
            this._processMarkers();
        }
        this._enableDragDrop();
    };

    DesignEditor.prototype._enableDragDrop = function () {
        var thisObj = this;
        /* Enable reordering of draggable children within their containers */
        $('.vde_element_wrapper.vde_container').sortable({
            items: '.vde_element_wrapper.vde_draggable',
            tolerance: 'pointer',
            revert: true,
            helper: 'clone',
            appendTo: 'body',
            placeholder: 'vde_placeholder',
            start: function(event, ui) {
                thisObj._resizePlaceholder(ui.placeholder, ui.item);
                thisObj._outlineDropContainer(this);
                /* Enable dropping of the elements outside of their containers */
                var otherContainers = $('.vde_element_wrapper.vde_container').not(ui.item);
                $(this).sortable('option', 'connectWith', otherContainers);
                otherContainers.sortable('refresh');
            },
            over: function(event, ui) {
                thisObj._outlineDropContainer(this);
            },
            stop: function(event, ui) {
                thisObj._removeDropContainerOutline();
            }
        }).disableSelection();
        return this;
    };

    DesignEditor.prototype._resizePlaceholder = function (placeholder, element) {
        placeholder.css({height: $(element).outerHeight(true) + 'px'});
    };

    DesignEditor.prototype._outlineDropContainer = function (container) {
        this._removeDropContainerOutline();
        $(container).addClass('vde_container_hover');
    };

    DesignEditor.prototype._removeDropContainerOutline = function () {
        $('.vde_container_hover').removeClass('vde_container_hover');
    };

    DesignEditor.prototype.highlight = function (isOn) {
        if (isOn) {
            this._turnHighlightingOn();
        } else {
            this._turnHighlightingOff();
        }
        return this;
    };

    DesignEditor.prototype._turnHighlightingOn = function () {
        var thisObj = this;
        Mage.Cookies.clear(this._options['cookie_highlighting_name']);
        $('.vde_element_wrapper').each(function () {
            $(this)
                .append(thisObj._getChildren($(this).attr('id')))
                .show()
                .children('.vde_element_title').slideDown('fast');
        });
        this._children = {};
        return this;
    };

    DesignEditor.prototype._turnHighlightingOff = function () {
        var thisObj = this;
        Mage.Cookies.set(this._options['cookie_highlighting_name'], "off");
        $('.vde_element_wrapper').each(function () {
            var elem = $(this);
            elem.children('.vde_element_title').slideUp('fast', function () {
                var children = elem.contents(':not(.vde_element_title)');
                var parentId = elem.attr('id');
                children.each(function(){
                    thisObj._storeChild(parentId, this);
                });
                elem.after(children).hide();
            });
        });
        return this;
    };

    DesignEditor.prototype._processMarkers = function () {
        var thisObj = this;
        var parentsIdsStack = [];
        var currentParentId;
        $('*').contents().each(function(){
            if (this.nodeType == Node.COMMENT_NODE) {
                if (this.data.substr(0, 9) == 'start_vde') {
                    currentParentId = this.data.substr(6, this.data.length);
                    parentsIdsStack.push(currentParentId);
                    this.parentNode.removeChild(this);
                } else if (this.data.substr(0, 7) == 'end_vde') {
                    if (this.data.substr(4, this.data.length) !== currentParentId) {
                        throw "Could not find closing element for opened '" + currentParentId + "' element";
                    }
                    parentsIdsStack.pop();
                    currentParentId = parentsIdsStack[parentsIdsStack.length - 1];
                    this.parentNode.removeChild(this);
                }
            } else if (currentParentId) {
                thisObj._storeChild(currentParentId, this);
            }
        });
    };

    DesignEditor.prototype._storeChild = function (parentId, child) {
        if (!this._children[parentId]) {
            this._children[parentId] = [];
        }
        this._children[parentId].push(child);
    };

    DesignEditor.prototype._getChildren = function (parentId) {
        if (!this._children[parentId]) {
            return [];
        }
        return this._children[parentId];
    };

})(jQuery);
