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
     * Widget tree
     */
    $.widget( "Mage.tree" , {
        options:{
            ui: {
                select_limit: 1,
                selected_parent_close: false
            },
            themes: {
                dots:  false,
                icons: false
            },
            callback: {
                onselect: function( e, data ) {
                    var leaf = $( data.rslt.obj ),
                        cellElement = $( this ).parents( '.vde_toolbar_cell' );
                    if ( cellElement.hasClass( 'active' ) ){
                        cellElement.removeClass( 'active' );
                        $( this ).trigger( 'changetitle.menu', [ $.trim( leaf.find( 'a:first' ).text() ) ] );
                        window.location = leaf.find( 'a:first' ).attr( 'href' );
                    }
                }
            }
        },
        _create: function() {
            var self = this;
            this.element.on( 'loaded.jstree' , function ( e, data ) {
                self.element.data( 'selected' ) ?
                    self.element.jstree( 'select_node' , self.element.find( self.element.data( 'selected' ) ) ):
                    $.noop();
            });
            this.element.jstree( this.options );
            this.element.on( 'select_node.jstree', this.options.callback.onselect );
        }
    });

    /**
     * Widget sScroll
     */
    $.widget( "Mage.sScroll", {
        options:{
            color: '#cccccc',
            alwaysVisible: true,
            opacity: 1,
            height: 'auto',
            size: 9
        },
        _create: function() {
            this.element.slimScroll( this.options );
        }
    });

    /**
     * Widget menu
     */
    $.widget( "Mage.menu", {
        options:{
            type: 'popup',
            titleSelector: ':first-child',
            titleTextSelector : '.vde_toolbar_cell_value',
            activeClass : 'active',
            activeEventName: 'activate_toolbar_cell.vde'
        },
        _create: function() {
            this._bind();
        },
        _bind: function(){
            var self = this;
            this.element
                .on( 'hide.menu', function( e ){self.hide( e )} )
                .on( 'changetitle.menu', function( e, title ) {
                    self.element.find( self.options.titleTextSelector ).text( title );
                })
                .find( this.options.titleSelector ).first()
                .on( 'click.menu', function( e ){
                    self.element.hasClass( self.options.activeClass ) ?
                        self.hide( e ):
                        self.show( e );
                })
            $( 'body' ).on( 'click', function( e ) {
                $( ':'+self.namespace+'-'+self.widgetName ).not( $( e.target )
                    .parents( ':'+self.namespace+'-'+self.widgetName ).first()).menu( 'hide' );
            })
        },
        show: function( e ){
            this.element.addClass( this.options.activeClass ).trigger( this.options.activeEventName );
        },
        hide:function( e ){
            this.element.removeClass( this.options.activeClass );
        }
    });

    /**
     * Widget menu - tree view
     */
    var menuBasePrototype = $.Mage.menu.prototype;
    $.widget( "Mage.menu", $.extend({}, menuBasePrototype, {
        _create: function() {
            menuBasePrototype._create.apply(this, arguments);
            if ( this.options.treeSelector ) {
                this.element.find( this.options.treeSelector ).size() ?
                this.element.find( this.options.treeSelector ).tree() :
                $.noop();
            }
        }
    }));

    /**
     * Widget menu - slimScroll view
     */
    var menuTreePrototype = $.Mage.menu.prototype;
    $.widget( "Mage.menu", $.extend({}, menuTreePrototype, {
        _create: function() {
            var self = this;
            menuTreePrototype._create.apply(this, arguments);
            if(this.options.slimScroll) {
                this.options.treeSelector && this.element.find( this.options.treeSelector ).size() ?
                    this.element
                        .one( 'activate_toolbar_cell.vde', function () {
                            self.element.find( ':Mage-tree' ).sScroll();
                    }) :
                    $.noop();
            }
        }
    }));

    /**
     * Widget checkbox
     */
    $.widget( "Mage.checkbox", {
        options:{
            checkedClass : 'checked'
        },
        _create: function() {
            this._bind();
        },
        _bind: function(){
            var self = this;
            this.element.on( 'click', function(){
                self._click();
            })
        },
        _click: function(){
            if ( this.element.hasClass( this.options.checkedClass ) ) {
                this.element.removeClass( this.options.checkedClass );
                this.options.unCheckedEvent ? this.element.trigger(this.options.unCheckedEvent) : $.noop();
            } else {
                this.element.addClass( this.options.checkedClass );
                this.options.checkedEvent ? this.element.trigger(this.options.checkedEvent) : $.noop();
            }
        }
    });

})( jQuery );