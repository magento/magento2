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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    'jquery',
    'prototype',
    'mage/adminhtml/events'
], function(jQuery){

    varienTabs = new Class.create();

    varienTabs.prototype = {
        initialize : function(containerId, destElementId,  activeTabId, shadowTabs){
            this.containerId    = containerId;
            this.destElementId  = destElementId;
            this.activeTab = null;

            this.tabOnClick     = this.tabMouseClick.bindAsEventListener(this);

            this.tabs = $$('#'+this.containerId+' li a.tab-item-link');

            this.hideAllTabsContent();
            for (var tab=0; tab<this.tabs.length; tab++) {
                Event.observe(this.tabs[tab],'click',this.tabOnClick);
                // move tab contents to destination element
                if($(this.destElementId)){
                    var tabContentElement = $(this.getTabContentElementId(this.tabs[tab]));
                    if(tabContentElement && tabContentElement.parentNode.id != this.destElementId){
                        $(this.destElementId).appendChild(tabContentElement);
                        tabContentElement.container = this;
                        tabContentElement.statusBar = this.tabs[tab];
                        tabContentElement.tabObject  = this.tabs[tab];
                        this.tabs[tab].contentMoved = true;
                        this.tabs[tab].container = this;
                        this.tabs[tab].show = function(){
                            this.container.showTabContent(this);
                        }
                        if(varienGlobalEvents){
                            varienGlobalEvents.fireEvent('moveTab', {tab:this.tabs[tab]});
                        }
                    }
                }
    /*
                // this code is pretty slow in IE, so lets do it in tabs*.phtml
                // mark ajax tabs as not loaded
                if (Element.hasClassName($(this.tabs[tab].id), 'ajax')) {
                    Element.addClassName($(this.tabs[tab].id), 'notloaded');
                }
    */
                // bind shadow tabs
                if (this.tabs[tab].id && shadowTabs && shadowTabs[this.tabs[tab].id]) {
                    this.tabs[tab].shadowTabs = shadowTabs[this.tabs[tab].id];
                }
            }

            this.displayFirst = activeTabId;
            Event.observe(window,'load',this.moveTabContentInDest.bind(this));
            Event.observe(window,'load',this.bindOnbeforeSubmit.bind(this));
            Event.observe(window,'load',this.bindOnInvalid.bind(this));
        },

        bindOnInvalid: function() {
            jQuery.each(this.tabs, jQuery.proxy(function(i, tab) {
                jQuery('#' + this.getTabContentElementId(tab))
                    .on('highlight.validate', function() {
                        jQuery(tab).addClass('error').find('.error').show();
                    })
                    .on('focusin', jQuery.proxy(function() {
                        this.showTabContentImmediately(tab);
                    }, this));
            }, this));
        },

        bindOnbeforeSubmit: function() {
            jQuery('#' + this.destElementId).on('beforeSubmit', jQuery.proxy(function(e, data) {
                var tabsIdValue = this.activeTab.id;
                if (this.tabsBlockPrefix) {
                    if (this.activeTab.id.startsWith(this.tabsBlockPrefix)) {
                        tabsIdValue = tabsIdValue.substr(this.tabsBlockPrefix.length);
                    }
                }
                jQuery(this.tabs).removeClass('error');
                var options = {action: {args: {}}};
                options.action.args[this.tabIdArgument || 'tab'] = tabsIdValue;
                data = data ? jQuery.extend(data, options) : options;
            }, this));
        },

        setSkipDisplayFirstTab : function(){
            this.displayFirst = null;
        },

        moveTabContentInDest : function(){
            for(var tab=0; tab<this.tabs.length; tab++){
                if($(this.destElementId) &&  !this.tabs[tab].contentMoved){
                    var tabContentElement = $(this.getTabContentElementId(this.tabs[tab]));
                    if(tabContentElement && tabContentElement.parentNode.id != this.destElementId){
                        $(this.destElementId).appendChild(tabContentElement);
                        tabContentElement.container = this;
                        tabContentElement.statusBar = this.tabs[tab];
                        tabContentElement.tabObject  = this.tabs[tab];
                        this.tabs[tab].container = this;
                        this.tabs[tab].show = function(){
                            this.container.showTabContent(this);
                        }
                        if(varienGlobalEvents){
                            varienGlobalEvents.fireEvent('moveTab', {tab:this.tabs[tab]});
                        }
                    }
                }
            }
            if (this.displayFirst) {
                this.showTabContent($(this.displayFirst));
                this.displayFirst = null;
            }
        },

        getTabContentElementId : function(tab){
            if(tab){
                return tab.id+'_content';
            }
            return false;
        },

        tabMouseClick : function(event) {
            var tab = Event.findElement(event, 'a');

            // go directly to specified url or switch tab
            if ((tab.href.indexOf('#') != tab.href.length-1)
                && !(Element.hasClassName(tab, 'ajax'))
            ) {
                location.href = tab.href;
            }
            else {
                this.showTabContent(tab);
            }
            Event.stop(event);
        },

        hideAllTabsContent : function(){
            for(var tab in this.tabs){
                this.hideTabContent(this.tabs[tab]);
            }
        },

        // show tab, ready or not
        showTabContentImmediately : function(tab) {
            this.hideAllTabsContent();
            var tabContentElement = $(this.getTabContentElementId(tab));
            if (tabContentElement) {
                Element.show(tabContentElement);
                Element.addClassName(tab, 'active');
                // load shadow tabs, if any
                if (tab.shadowTabs && tab.shadowTabs.length) {
                    for (var k in tab.shadowTabs) {
                        this.loadShadowTab($(tab.shadowTabs[k]));
                    }
                }
                if (!Element.hasClassName(tab, 'ajax only')) {
                    Element.removeClassName(tab, 'notloaded');
                }
                this.activeTab = tab;
            }
            if (varienGlobalEvents) {
                varienGlobalEvents.fireEvent('showTab', {tab:tab});
            }
        },

        // the lazy show tab method
        showTabContent : function(tab) {
            var tabContentElement = $(this.getTabContentElementId(tab));
            if (tabContentElement) {
                if (this.activeTab != tab) {
                    if (varienGlobalEvents) {
                        var defaultTab = this.tabs[0];
                        var eventData = {
                            from: this.activeTab ? this.activeTab.getAttribute('id') : null,
                            to: tab ? tab.getAttribute('id') : null,
                            first: defaultTab && tab && tab.getAttribute('id') == defaultTab.getAttribute('id')
                        };
                        if (varienGlobalEvents.fireEvent('tabChangeBefore', eventData) === false) {
                            return;
                        };
                    }
                }
                // wait for ajax request, if defined
                var isAjax = Element.hasClassName(tab, 'ajax');
                var isEmpty = tabContentElement.innerHTML=='' && tab.href.indexOf('#')!=tab.href.length-1;
                var isNotLoaded = Element.hasClassName(tab, 'notloaded');

                if ( isAjax && (isEmpty || isNotLoaded) )
                {
                    new Ajax.Request(tab.href, {
                        parameters: {form_key: FORM_KEY},
                        evalScripts: true,
                        onSuccess: function(transport) {
                            try {
                                if (transport.responseText.isJSON()) {
                                    var response = transport.responseText.evalJSON()
                                    if (response.error) {
                                        alert(response.message);
                                    }
                                    if(response.ajaxExpired && response.ajaxRedirect) {
                                        setLocation(response.ajaxRedirect);
                                    }
                                } else {
                                    $(tabContentElement.id).update(transport.responseText);
                                    this.showTabContentImmediately(tab)
                                }
                            }
                            catch (e) {
                                $(tabContentElement.id).update(transport.responseText);
                                this.showTabContentImmediately(tab)
                            }
                        }.bind(this)
                    });
                }
                else {
                    this.showTabContentImmediately(tab);
                }
            }
        },

        loadShadowTab : function(tab) {
            var tabContentElement = $(this.getTabContentElementId(tab));
            if (tabContentElement && Element.hasClassName(tab, 'ajax') && Element.hasClassName(tab, 'notloaded')) {
                new Ajax.Request(tab.href, {
                    parameters: {form_key: FORM_KEY},
                    evalScripts: true,
                    onSuccess: function(transport) {
                        try {
                            if (transport.responseText.isJSON()) {
                                var response = transport.responseText.evalJSON()
                                if (response.error) {
                                    alert(response.message);
                                }
                                if(response.ajaxExpired && response.ajaxRedirect) {
                                    setLocation(response.ajaxRedirect);
                                }
                            } else {
                                $(tabContentElement.id).update(transport.responseText);
                                if (!Element.hasClassName(tab, 'ajax only')) {
                                    Element.removeClassName(tab, 'notloaded');
                                }
                            }
                        }
                        catch (e) {
                            $(tabContentElement.id).update(transport.responseText);
                            if (!Element.hasClassName(tab, 'ajax only')) {
                                Element.removeClassName(tab, 'notloaded');
                            }
                        }
                    }.bind(this)
                });
            }
        },

        hideTabContent : function(tab){
            var tabContentElement = $(this.getTabContentElementId(tab));
            if($(this.destElementId) && tabContentElement){
               Element.hide(tabContentElement);
               Element.removeClassName(tab, 'active');
            }
            if(varienGlobalEvents){
                varienGlobalEvents.fireEvent('hideTab', {tab:tab});
            }
        }
    };

});