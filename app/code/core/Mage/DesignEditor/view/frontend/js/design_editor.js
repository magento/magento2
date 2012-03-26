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
    }

    DesignEditorSkinSelector.prototype._init = function (config) {
        this._skinControlSelector = '#' + config.selectId;
        this._backParams = config.backParams;
        this.changeSkinUrl = config.changeSkinUrl;
        return this;
    }

    DesignEditorSkinSelector.prototype._addListener = function () {
        var thisObj = this;
        $(this._skinControlSelector).change(
            function () {thisObj.changeSkin()}
        );
        return this;
    }

    DesignEditorSkinSelector.prototype.changeSkin = function () {
        var separator = /\?/.test(this.changeSkinUrl) ? '&' : '?';

        var params = {skin: $(this._skinControlSelector).val()};
        for (var i in this._backParams) {
            params[i] = this._backParams[i];
        }

        var url = this.changeSkinUrl + separator + $.param(params);

        window.location.href = url;
        return this;
    }

    /**
     * Class for design editor
     */
    DesignEditor = function () {
        this._init();
    }

    DesignEditor.prototype._init = function () {
        this._dragged = null;
        this._placeholder = null;
        this._templatePlaceholder = '<div class="vde_placeholder"></div>';

        this._enableDragging();
        return this;
    }

    DesignEditor.prototype._enableDragging = function () {
        var thisObj = this;
        $('.vde_element_wrapper').draggable({
            helper: 'clone',
            revert: true,
            start: function (event, ui) {thisObj._onDragStarted(event, ui)},
            stop: function (event, ui) {thisObj._onDragStopped(event, ui)}
        });
        return this;
    }

    DesignEditor.prototype._triggerStartedEvent = function () {
        $(document).trigger('started.vde', this);
        return this;
    }

    DesignEditor.prototype._onDragStarted = function (event, ui) {
        if (this._dragged) {
            return this;
        }
        var dragged = $(event.target);
        this._hideDragged(dragged)
            ._resizeHelperSameAsDragged(ui.helper, dragged)
            ._putPlaceholder();
    }

    DesignEditor.prototype._onDragStopped = function (event, ui) {
        if (!this._dragged) {
            return this;
        }
        this._removePlaceholder()
            ._showDragged();
    }

    DesignEditor.prototype._hideDragged = function (dragged) {
        this._showDragged(); // Maybe some other dragged element was hidden before, just restore it
        this._dragged = dragged;
        this._dragged.css('visibility', 'hidden');
        return this;
    }

    DesignEditor.prototype._showDragged = function () {
        if (!this._dragged) {
            return this;
        }
        this._dragged.css('visibility', 'visible');
        this._dragged = null;
        return this;
    }

    DesignEditor.prototype._resizeHelperSameAsDragged = function (helper, dragged) {
        helper.height(dragged.height())
            .width(dragged.width());
        return this;
    }

    DesignEditor.prototype._putPlaceholder = function () {
        if (!this._placeholder) {
            this._placeholder = $(this._templatePlaceholder);
        }
        this._placeholder.css('height', this._dragged.outerHeight() + 'px')
            .css('width', this._dragged.outerWidth() + 'px');
        this._placeholder.insertBefore(this._dragged);
        return this;
    }

    DesignEditor.prototype._removePlaceholder = function () {
        if (!this._placeholder) {
            return this;
        }
        this._placeholder.remove();
        return this;
    }

    DesignEditor.prototype.highlight = function (isOn) {
        if (isOn) {
            this._turnHighlightingOn();
        } else {
            this._turnHighlightingOff();
        }
        return this;
    }

    DesignEditor.prototype._turnHighlightingOn = function () {
        $('.vde_element_wrapper').each(function () {
            var elem = $(this);
            var children = elem.prop('vdeChildren');
            elem.show().append(children).removeProp('vdeChildren');
            elem.children('.vde_element_title').slideDown('fast');
        });
        return this;
    }

    DesignEditor.prototype._turnHighlightingOff = function () {
        $('.vde_element_wrapper').each(function () {
            var elem = $(this);
            elem.children('.vde_element_title').slideUp('fast', function () {
                var children = elem.children(':not(.vde_element_title)');
                elem.after(children).hide().prop('vdeChildren', children);
            });
        });
        return this;
    }
})(jQuery);
