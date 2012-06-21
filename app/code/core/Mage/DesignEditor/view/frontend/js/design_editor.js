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

( function ( $ ) {

    /**
     * Widget block
     */
    $.widget( "vde.block", { _create: function(){}} );

    /**
     * Widget container
     */
    $.widget( "vde.container", $.ui.sortable, {
        options : {
            tolerance: 'pointer',
            revert: true,
            connectWithSelector : '.vde_element_wrapper.vde_container',
            placeholder: 'vde_placeholder',
            hoverClass: 'vde_container_hover',
            items: '.vde_element_wrapper.vde_draggable',
            helper: 'clone',
            appendTo: 'body'
        },
        _create: function() {
            var self = this;
            this.element.data( "sortable", this );
            self.options =  $.extend( {}, self.options, {
                start: function( event, ui ) {
                    ui.placeholder.css( { height: $( ui.helper ).outerHeight( true ) } );
                    $(this).sortable('option', 'connectWith', $(self.options.connectWithSelector).not(ui.item))
                        .sortable('refresh');
                },
                over: function( event, ui ) {
                    self.element.addClass( self.options.hoverClass );
                },
                out: function( event, ui ) {
                    self.element.removeClass( self.options.hoverClass );
                }
            });
            $.ui.sortable.prototype._create.apply( this, arguments );
        }
    });

    /**
     * Widget panel
     */
    $.widget( "vde.panel" , {
        options:{
            cellSelector : '.vde_toolbar_cell',
            handlesHierarchySelector : '#vde_handles_hierarchy',
            treeSelector : '#vde_handles_tree'
        },
        _create: function() {
            this._initCells();
        },
        _initCells : function(){
            var self = this;
            this.element.find( this.options.cellSelector ).each( function(){
                $( this ).is( self.options.handlesHierarchySelector ) ?
                    $( this ).menu( {treeSelector : self.options.treeSelector, slimScroll:true } ) :
                    $( this ).menu();
            });
            this.element.find( this.options.cellSelector ).menu();
        }
    });

    /**
     * Widget page
     */
    $.widget( "vde.page", {
        options:{
            containerSelector : '.vde_element_wrapper.vde_container',
            panelSelector : '#vde_toolbar',
            highlightElementSelector : '.vde_element_wrapper',
            highlightElementTitleSelector : '.vde_element_title',
            highlightCheckboxSelector : '#vde_highlighting',
            cookieHighlightingName : 'vde_highlighting'
        },
        _create: function() {
            this._initContainers();
            this._initPanel();
        },
        _initContainers : function(){
            $( this.options.containerSelector )
                .container().disableSelection();
        },
        _initPanel : function(){
            $( this.options.panelSelector ).panel();
        }
    });

    /**
     * Widget page highlight functionality
     */
    var pageBasePrototype = $.vde.page.prototype;
    $.widget( "vde.page", $.extend({}, pageBasePrototype, {
        _create: function() {
            pageBasePrototype._create.apply( this, arguments );
            if( this.options.highlightElementSelector ) {
                this._initHighlighting();
                this._bind();
            }
        },
        _bind: function(){
            var self = this;
            this.element
                .on( 'highlightelements.vde', function(){ self._highlight(); })
                .on( 'unhighlightelements.vde', function(){ self._unhighlight(); });
        },
        _initHighlighting: function(){
            this.options.highlightCheckboxSelector ?
                $( this.options.highlightCheckboxSelector )
                    .checkbox({checkedEvent:'highlightelements.vde', unCheckedEvent:'unhighlightelements.vde'}) :
                $.noop();
            this.highlightBlocks = {};
            if (Mage.Cookies.get(this.options.cookieHighlightingName) == 'off') {
                this._processMarkers();
            }

        },
        _highlight: function(){
            var self = this;
            Mage.Cookies.clear( this.options.cookieHighlightingName );
            $( this.options.highlightElementSelector ).each(function () {
                $( this )
                    .append( self._getChildren( $( this ).attr( 'id' ) ) )
                    .show()
                    .children( self.options.highlightElementTitleSelector ).slideDown( 'fast' );
            });
            this.highlightBlocks = {};
        },
        _unhighlight: function(){
            var self = this;
            Mage.Cookies.set( this.options.cookieHighlightingName, "off" );
            $( this.options.highlightElementSelector ).each( function () {
                var elem = $( this );
                elem.children( self.options.highlightElementTitleSelector ).slideUp( 'fast', function () {
                    var children = elem.contents( ':not('+self.options.highlightElementTitleSelector+')' );
                    var parentId = elem.attr( 'id' );
                    children.each( function(){
                        self._storeChild( parentId, this );
                    });
                    elem.after( children ).hide();
                });
            });
        },
        _processMarkers : function () {
            var self = this,
                parentsIdsStack = [],
                currentParentId;
            $('*').contents().each(function(){
                if ( this.nodeType == Node.COMMENT_NODE ) {
                    if ( this.data.substr(0, 9) == 'start_vde' ) {
                        currentParentId = this.data.substr( 6, this.data.length );
                        parentsIdsStack.push( currentParentId );
                        this.parentNode.removeChild( this );
                    } else if ( this.data.substr( 0, 7 ) == 'end_vde' ) {
                        if ( this.data.substr( 4, this.data.length ) !== currentParentId ) {
                            throw "Could not find closing element for opened '" + currentParentId + "' element";
                        }
                        parentsIdsStack.pop();
                        currentParentId = parentsIdsStack[ parentsIdsStack.length - 1 ];
                        this.parentNode.removeChild( this );
                    }
                } else if ( currentParentId ) {
                    self._storeChild( currentParentId, this );
                }
            })
        },
        _storeChild : function ( parentId, child ) {
            ( !this.highlightBlocks[ parentId ] ) ? this.highlightBlocks[ parentId ] = [] : $.noop();
            this.highlightBlocks[ parentId ].push( child );
        },
        _getChildren : function ( parentId ) {
            return ( !this.highlightBlocks[parentId] ) ? [] : this.highlightBlocks[ parentId ];
        }
    }));

})( jQuery );
