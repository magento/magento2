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
            hoverClass: 'vde_container_hover',
            items: '.vde_element_wrapper.vde_draggable',
            helper: 'clone',
            appendTo: 'body',
            containerSelector: '.vde_container'
        },
        _create: function() {
            var self = this;
            this.element.data('sortable', this);
            self.options =  $.extend({}, self.options, {
                start: function( event, ui ) {
                    ui.placeholder.css( { height: $( ui.helper ).outerHeight( true ) } );
                    self.element.vde_container('option', 'connectWith', $(self.options.connectWithSelector).not(ui.item))
                        .vde_container('refresh');
                },
                over: function(event, ui) {
                    self.element.addClass(self.options.hoverClass);
                },
                out: function(event, ui) {
                    self.element.removeClass(self.options.hoverClass);
                }
            });
            $.ui.sortable.prototype._create.apply(this, arguments);
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
            var originPosition = event.data.position;
            var destinationContainer = this._getContainer(block).data('name');
            var destinationPosition = block.index();

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
     * Widget panel
     */
    $.widget('vde.vde_panel', {
        options: {
            cellSelector: '.vde_toolbar_cell',
            handlesHierarchySelector: '#vde_handles_hierarchy',
            treeSelector: '#vde_handles_tree'
        },
        _create: function() {
            this._initCells();
        },
        _initCells : function() {
            var self = this;
            this.element.find( this.options.cellSelector ).each( function(){
                $( this ).is( self.options.handlesHierarchySelector ) ?
                    $( this ).vde_menu( {treeSelector : self.options.treeSelector, slimScroll:true } ) :
                    $( this ).vde_menu();
            });
            this.element.find( this.options.cellSelector ).vde_menu();
        },
        destroy: function() {
            this.element.find( this.options.cellSelector ).each( function(i, element) {
                $(element).data('vde_menu').destroy();
            });
            $.Widget.prototype.destroy.call( this );
        }
    });

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
        options:{
            compactLogButtonSelector: '.compact-log',
            viewLayoutButtonSelector: '.view-layout',
            baseUrl: null,
            compactLogUrl: null,
            viewLayoutUrl: null
        },
        _history: null,
        _create: function() {
            this._initToolbar();
            this._initButtons();
        },
        _initToolbar : function() {},
        _initButtons : function() {
            $(this.options.compactLogButtonSelector).bind(
                'click', $.proxy(this._onCompactLogButtonClick, this)
            );

            $(this.options.viewLayoutButtonSelector).bind(
                'click', $.proxy(this._onViewLayoutButtonClick, this)
            );
        },
        _initEventObservers: function() {
            this._history.element.bind(
                this._history.getEventName('add'),
                $.proxy(this._onHistoryAddItem, this)
            );
        },
        _onHistoryAddItem: function(e, change) {
            this.addItem(change);
        },
        _onCompactLogButtonClick: function(e) {
            try {
                if (this._history.getItems().length == 0) {
                    /** @todo temporary report */
                    alert($.mage.__('No changes found.'));
                    return false;
                }
                var data = this._preparePostItems(this._history.getItems());
                var items = this._post(this.options.compactLogUrl, data);
                this._compactLogToHistory(items);
            } catch (e) {
                alert(e.message);
            } finally {
                return false;
            }
        },
        _onViewLayoutButtonClick: function(e) {
            try {
                if (this._history.getItems().length == 0) {
                    /** @todo temporary report */
                    alert($.mage.__('No changes found.'));
                    return false;
                }
                var data = this._preparePostItems(this._history.getItems());
                var compactXml = this._post(this.options.viewLayoutUrl, data);
                alert(compactXml);
            } catch (e) {
                alert(e.message);
            } finally {
                return false;
            }

        },
        setHistory: function(history) {
            this._history = history;
            this._initEventObservers();
        },
        setBaseUrl: function(baseUrl) {
            this.option('baseUrl', baseUrl);
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
        _compactLogToHistory: function(items) {
            this._history.deleteItems();
            this.deleteItems();
            var self = this;
            $.each(items[0], function(index, item) {
                var change = $.fn.changeFactory.getInstance('layout');
                change.setActionData(item);
                self._history.addItem(change);
            });
        },
        _preparePostItems: function(items) {
            var postData = {};
            $.each(items, function(index, item){
                postData[index] = item.getPostData();
            });
            return postData;
        },
        _post: function(action, data) {
            var url = action;
            var postResult;
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                data: data,
                async: false,
                success: function(data) {
                    if (data.error) {
                        /** @todo add error validator */
                        throw Error($.mage.__('Some problem with save action'));
                        return;
                    }
                    postResult = data.success;
                },
                error: function(data) {
                    throw Error($.mage.__('Some problem with save action'));
                }
            });
            return postResult;
        }
    });

    /**
     * Widget page
     */
    $.widget('vde.vde_page', {
        options: {
            containerSelector: '.vde_element_wrapper.vde_container',
            panelSelector: '#vde_toolbar',
            highlightElementSelector: '.vde_element_wrapper',
            highlightElementTitleSelector: '.vde_element_title',
            highlightCheckboxSelector: '#vde_highlighting',
            cookieHighlightingName: 'vde_highlighting',
            historyToolbarSelector: '.vde_history_toolbar',
            baseUrl: null,
            compactLogUrl: null,
            viewLayoutUrl: null
        },
        _create: function () {
            this._initContainers();
            this._initPanel();
        },
        _initContainers: function () {
            $(this.options.containerSelector)
                .vde_container().disableSelection();
        },
        _initPanel: function () {
            $(this.options.panelSelector).vde_panel();
        }
    });

    /**
     * Widget page highlight functionality
     */
    var pageBasePrototype = $.vde.vde_page.prototype;
    $.widget('vde.vde_page', $.extend({}, pageBasePrototype, {
        _create: function () {
            pageBasePrototype._create.apply(this, arguments);
            if (this.options.highlightElementSelector) {
                this._initHighlighting();
                this._bind();
            }
        },
        _bind: function () {
            var self = this;
            this.element
                .on('checked.vde_checkbox', function () {
                    self._highlight();
                })
                .on('unchecked.vde_checkbox', function () {
                    self._unhighlight();
                });
        },
        _initHighlighting: function () {
            if (this.options.highlightCheckboxSelector) {
                $(this.options.highlightCheckboxSelector)
                    .vde_checkbox();
            }
            this.highlightBlocks = {};
            if (Mage.Cookies.get(this.options.cookieHighlightingName) == 'off') {
                this._processMarkers();
            }

        },
        _highlight: function () {
            Mage.Cookies.clear(this.options.cookieHighlightingName);
            var self = this;
            $(this.options.highlightElementSelector).each(function () {
                $(this)
                    .append(self._getChildren($(this).attr('id')))
                    .show()
                    .children(self.options.highlightElementTitleSelector).slideDown('fast');
            });
            this.highlightBlocks = {};
        },
        _unhighlight: function () {
            Mage.Cookies.set(this.options.cookieHighlightingName, 'off');
            var self = this;
            $(this.options.highlightElementSelector).each(function () {
                var elem = $(this);
                elem.children(self.options.highlightElementTitleSelector).slideUp('fast', function () {
                    var children = elem.contents(':not(' + self.options.highlightElementTitleSelector + ')');
                    var parentId = elem.attr('id');
                    children.each(function () {
                        self._storeChild(parentId, this);
                    });
                    elem.after(children).hide();
                });
            });
        },
        _processMarkers: function () {
            var self = this,
                parentsIdsStack = [],
                currentParentId;
            $('*').contents().each(function () {
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
                    self._storeChild(currentParentId, this);
                }
            })
        },
        _storeChild: function(parentId, child) {
            if (!this.highlightBlocks[parentId]) {
                this.highlightBlocks[parentId] = [];
            }
            this.highlightBlocks[parentId].push(child);
        },
        _getChildren: function(parentId) {
            return (!this.highlightBlocks[parentId]) ? [] : this.highlightBlocks[parentId];
        }
    }));

    /**
     * Widget page history init
     */
    var pagePrototype = $.vde.vde_page.prototype;
    $.widget( "vde.vde_page", $.extend({}, pagePrototype, {
        _create: function() {
            pagePrototype._create.apply( this, arguments );
            var history = this._initHistory();
            this._initHistoryToolbar(history);
            this._initRemoveOperation(history);
            this._setHistoryForContainers(history);
        },
        _initHistory: function() {
            //@TODO can we make this not a widget but global object?
            return $( window ).vde_history().data('vde_history');
        },
        _initHistoryToolbar: function(history) {
            if (!history) {
                throw new Error('History object is not set');
            }
            if ($( this.options.historyToolbarSelector )) {
                var toolbar = $( this.options.historyToolbarSelector).vde_historyToolbar().data('vde_historyToolbar');
                if (toolbar) {
                    toolbar.setHistory(history);
                    toolbar.option('baseUrl', this.options.baseUrl);
                    toolbar.option('compactLogUrl', this.options.compactLogUrl);
                    toolbar.option('viewLayoutUrl', this.options.viewLayoutUrl);
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
        destroy: function() {
            //DOM structure can be missed when test executed
            var panelContainer = $(this.options.panelSelector);
            if (panelContainer.size()) {
                panelContainer.vde_panel('destroy');
            }
            var toolbarContainer = $(this.options.historyToolbarSelector);
            if (toolbarContainer.length) {
                toolbarContainer.vde_historyToolbar('destroy');
            }
            $(window).vde_history('destroy');
            $(this.options.highlightElementSelector).vde_removable('destroy');
            $(this.options.containerSelector).vde_container('destroy');

            pagePrototype.destroy.call(this);
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
        _onRemoveButtonClick: function(e) {
            var change = $.fn.changeFactory.getInstance('layout');
            var block = this.element;
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

})( jQuery );
