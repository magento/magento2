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
     * Class for managing skin selector control
     */
    DesignEditorSkinSelector = function (config) {
        this._init(config);
        this._addListener();
        return this;
    };

    DesignEditorSkinSelector.prototype._init = function (config) {
        this._skinControlSelector = '#' + config.selectId;
        this._backParams = config.backParams;
        this.changeSkinUrl = config.changeSkinUrl;
        return this;
    };

    DesignEditorSkinSelector.prototype._addListener = function () {
        var thisObj = this;
        $(this._skinControlSelector).change(
            function () {thisObj.changeSkin()}
        );
        return this;
    };

    DesignEditorSkinSelector.prototype.changeSkin = function () {
        var separator = /\?/.test(this.changeSkinUrl) ? '&' : '?';

        var params = {skin: $(this._skinControlSelector).val()};
        for (var i in this._backParams) {
            params[i] = this._backParams[i];
        }

        var url = this.changeSkinUrl + separator + $.param(params);

        window.location.href = url;
        return this;
    };

    /**
     * Class for design editor
     */
    DesignEditor = function () {
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
        $('.vde_element_wrapper').each(function () {
            var elem = $(this);
            var children = $('[vde_parent_element="' + elem.attr('id') + '"]');
            children.removeAttr('vde_parent_element');
            elem.show().append(children);
            elem.children('.vde_element_title').slideDown('fast');
        });
        return this;
    };

    DesignEditor.prototype._turnHighlightingOff = function () {
        $('.vde_element_wrapper').each(function () {
            var elem = $(this);
            elem.children('.vde_element_title').slideUp('fast', function () {
                var children = elem.children(':not(.vde_element_title)');
                children.attr('vde_parent_element', elem.attr('id'));
                elem.after(children).hide();
            });
        });
        return this;
    };
})(jQuery);
