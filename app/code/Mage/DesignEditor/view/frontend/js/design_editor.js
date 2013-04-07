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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

(function($) {
    /**
     * Widget block
     */
    $.widget( "vde.block", { _create: function() {}} );

    /**
     * Widget container
     */
    $.widget('vde.vde_container', $.ui.sortable, {
        options: {
            tolerance: 'pointer',
            revert: true,
            connectWithSelector: '.vde_element_wrapper.vde_container',
            placeholder: 'vde_placeholder',
            forcePlaceholderSize: true,
            hoverClass: 'vde_container_hover',
            items: '.vde_element_wrapper.vde_draggable',
            helper: 'clone',
            appendTo: 'body',
            containerSelector: '.vde_container',
            highlightClass: 'vde_highlight',
            opacityClass: 'vde_opacity_enabled'
        },
        _create: function() {
            var self = this;
            this.element.data('sortable', this);
            self.options =  $.extend({}, self.options, {
                start: function(event, ui) {
                    self._highlightEmptyContainers(ui.helper);
                    self.element.vde_container('option', 'connectWith', $(self.options.connectWithSelector)
                        .not(ui.item)).vde_container('refresh');

                    self.element.addClass(self.options.hoverClass).addClass(self.options.highlightClass);
                    $(self.options.items).addClass(self.options.opacityClass);
                    ui.helper.removeClass(self.options.opacityClass);
                },
                over: function(event, ui) {
                    $(self.options.containerSelector).removeClass(self.options.hoverClass);
                    self.element.addClass(self.options.hoverClass);

                    self._highlightEmptyContainers(ui.helper);
                },
                stop: function(event, ui) {
                    $(self.options.containerSelector).removeClass(self.options.hoverClass);
                    $('.' + self.options.highlightClass).removeClass(self.options.highlightClass);
                    $(self.options.items).removeClass(self.options.opacityClass);

                    self._disableEmptyContainers();
                }
            });
            $.ui.sortable.prototype._create.apply(this, arguments);
        },
        _highlightEmptyContainers: function(originalElement) {
            var self = this;
            $(this.options.containerSelector).each(function (index, element) {
                if ($(element).find(self.options.items + ':visible').length == 0) {
                    $(element).addClass(self.options.highlightClass)
                        .css('min-height', originalElement.outerHeight(true));
                }
            })
        },
        _disableEmptyContainers: function(originalElement) {
            var self = this;
            $(this.options.containerSelector).each(function (index, element) {
                if ($(element).find(':visible').length == 0) {
                    $(element).removeClass(self.options.highlightClass).css('min-height', '0px');
                }
            })
        }
    });

    /**
     * Widget container with ability to log "move" operations
     */
    var containerBasePrototype = $.vde.vde_container.prototype;
    $.widget( "vde.vde_container", $.extend({}, containerBasePrototype, {
        history: null,
        _onDragElementStart: function(event, ui) {
            var block = ui.item;
            if (this._isBlockDeeplyNested(block)) {
                return;
            }

            if (this._getContainer(block).data('name') != this.element.data('name')) {
                throw Error('Invalid container. Event "start" should be handled only for closest container');
            }

            this.element.bind( this.getEventName('stop', 'history'), {
                position: block.index()
            }, $.proxy(this._onDragElementStop, this));
        },
        _onDragElementStop: function(event, ui) {
            var block = ui.item;
            var originContainer = this.element.data('name');
            var originPosition = event.data.position - 1;
            var destinationContainer = this._getContainer(block).data('name');
            var destinationPosition = block.index() - 1;

            var containerChanged = destinationContainer != originContainer;
            var sortingOrderChanged = destinationPosition != originPosition;
            if (containerChanged || sortingOrderChanged) {
                var change = $.fn.changeFactory.getInstance('layout');
                change.setData({
                    action: 'move',
                    block: block.data('name'),
                    origin: {
                        container: originContainer,
                        order: originPosition
                    },
                    destination: {
                        container: destinationContainer,
                        order: destinationPosition
                    }
                });

                // This is the dependency of Container on History
                this.getHistory().addItem(change);
            }

            this.element.unbind( this.getEventName('stop', 'history'), $.proxy(this._onDragElementStop, this));
        },
        _getContainer: function(item) {
            return item.parent().closest(this.options.containerSelector);
        },
        _isBlockDeeplyNested: function(block) {
            return this._getContainer(block).attr('id') != this.element.attr('id');
        },
        getEventName: function(type, namespace) {
            var name = this.widgetEventPrefix + type;
            if (namespace) {
                name =  name + '.' + namespace;
            }
            return name;
        },
        setHistory: function(history) {
            this.history = history;
            this.element.bind( this.getEventName('start', 'history'), $.proxy(this._onDragElementStart, this));
        },
        getHistory: function() {
            if (!this.history) {
                throw Error('History element should be set before usage');
            }
            return this.history;
        }
    }));

    /**
     * Widget history
     *
     * @TODO can we make this not a widget but global object?
     */
    $.widget( "vde.vde_history" , {
        widgetEventPrefix: 'history/',
        options:{},
        items: [],
        _create: function() {},
        getEventName: function(type, namespace) {
            var name = this.widgetEventPrefix + type;
            if (namespace) {
                name =  name + '.' + namespace;
            }
            return name;
        },
        addItem: function(change) {
            this.items.push(change);
            this._trigger('add', null, change);
        },
        getItems: function() {
            return this.items;
        },
        deleteItems: function() {
            this.items = [];
        }
    });

    /**
     * Widget history toolbar
     *
     * @todo move out from history toolbar send POST data functionality
     */
    $.widget( "vde.vde_historyToolbar" , {
        options: {},
        _history: null,
        _create: function() {
            this._initToolbar();
        },
        _initToolbar : function() {},
        _initEventObservers: function() {
            this._history.element.bind(
                this._history.getEventName('add'),
                $.proxy(this._onHistoryAddItem, this)
            );
        },
        _onHistoryAddItem: function(e, change) {
            this.addItem(change);
        },
        setHistory: function(history) {
            this._history = history;
            this._initEventObservers();
        },
        setItems: function(items) {
            //this.deleteItems();
            $.each(items, function(index, item){this.addItem(item)});
        },
        deleteItems: function() {
            this.element.find('ul').empty();
        },
        addItem: function(change) {
            this.element.find('ul').append('<li>' + change.getTitle() + '</li>');
        },
        _preparePostItems: function(items) {
            var postData = {};
            $.each(items, function(index, item){
                postData[index] = item.getPostData();
            });
            return postData;
        },
        _post: function(action, data) {
            var postResult;
            $.ajax({
                url: action,
                type: 'POST',
                dataType: 'JSON',
                data: data,
                async: false,
                success: function(data) {
                    if (data.error) {
                        /** @todo add error validator */
                        throw Error($.mage.__('Some problem with save action'));
                    }
                    postResult = data.success;
                },
                error: function() {
                    throw Error($.mage.__('Some problem with save action'));
                }
            });
            return postResult;
        }
    });

    /**
     * Widget page
     */
    $.widget('vde.vde_connector', {
        options: {
            containerSelector: '.vde_element_wrapper.vde_container',
            highlightElementSelector: '.vde_element_wrapper',
            highlightElementTitleSelector: '.vde_element_title',
            highlightCheckboxSelector: '#vde_highlighting',
            historyToolbarSelector: '.vde_history_toolbar'
        },
        _create: function () {
            this._initContainers();
        },
        _initContainers: function () {
            $(this.options.containerSelector)
                .vde_container().disableSelection();
        }
    });

    /**
     * Widget page history init
     */
    var pagePrototype = $.vde.vde_connector.prototype;
    $.widget( "vde.vde_connector", $.extend({}, pagePrototype, {
        _create: function() {
            pagePrototype._create.apply(this, arguments);
            var history = this._initHistory();
            this._initHistoryToolbar(history);
            this._initRemoveOperation(history);
            this._setHistoryForContainers(history);
        },
        _initHistory: function() {
            // @TODO can we make this not a widget but global object?
            window.vdeHistoryObject = $( window ).vde_history().data('vde_history');
            return window.vdeHistoryObject;
        },
        _initHistoryToolbar: function(history) {
            if (!history) {
                throw new Error('History object is not set');
            }
            if ($( this.options.historyToolbarSelector )) {
                var toolbar = $( this.options.historyToolbarSelector).vde_historyToolbar().data('vde_historyToolbar');
                if (toolbar) {
                    toolbar.setHistory(history);
                }
            }
        },
        _initRemoveOperation : function(history) {
            $( this.options.highlightElementSelector ).each(function(i, element) {
                var widget = $(element).vde_removable().data('vde_removable');
                widget.setHistory(history);
            });
        },
        _setHistoryForContainers: function(history) {
            $( this.options.containerSelector ).each(function(i, element) {
                var widget = $(element).data('vde_container');
                widget.setHistory(history);
            });
        },
        _destroy: function() {
            //DOM structure can be missed when test executed
            var toolbarContainer = $(this.options.historyToolbarSelector);
            if (toolbarContainer.length) {
                toolbarContainer.vde_historyToolbar('destroy');
            }
            $(window).vde_history('destroy');
            if($(this.options.highlightElementSelector).is(':vde-vde_removable')) {
                $(this.options.highlightElementSelector).vde_removable('destroy');
            }
            if($(this.options.containerSelector).is(':vde-vde_container')) {
                $(this.options.containerSelector).vde_container('destroy');
            }
            pagePrototype._destroy.call(this);
        }
    }));

    /**
     * Widget removable
     */
    $.widget( "vde.vde_removable", {
        options: {
            relativeButtonSelector: '.vde_element_remove',
            containerSelector: '.vde_container'
        },
        history: null,
        _create: function() {
            this._initButtons();
        },
        _initButtons: function() {
            var self = this;
            // Remember that container can have block inside with their own remove buttons
            this.element.children(this.options.relativeButtonSelector)
                .css('display', 'block')
                .find('a').bind('click', $.proxy(self._onRemoveButtonClick, self));
        },
        _onRemoveButtonClick: function() {
            var change = $.fn.changeFactory.getInstance('layout');
            change.setData({
                action: 'remove',
                block: this.element.data('name'),
                container: this.element.parent().closest(this.options.containerSelector)
            });

            // This is the dependency of Removable on History
            this.history.addItem(change);
            this.remove();
        },
        setHistory: function(history) {
            this.history = history;
        },
        remove: function () {
            this.element.remove();
        }
    });

    $(document).ready(function() {
        $(window).vde_connector();

        if (window.parent) {
            (function($) {
                var eventData = {
                    content: 'iframe',
                    elements: {mousedown: ['.vde_removable .vde_element_remove img', '.vde_draggable']}
                };
                $('body').trigger('registerElements', eventData);
            })(window.parent.jQuery);
        }
    });
})( jQuery );
