/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'prototype'
], function(){

    varienAccordion = new Class.create();
    varienAccordion.prototype = {
        initialize : function(containerId, activeOnlyOne){
            this.containerId = containerId;
            this.activeOnlyOne = activeOnlyOne || false;
            this.container   = $(this.containerId);
            this.items       = $$('#'+this.containerId+' dt');
            this.loader      = new varienLoader(true);

            var links = $$('#'+this.containerId+' dt a');
            for(var i in links){
                if(links[i].href){
                    Event.observe(links[i],'click',this.clickItem.bind(this));
                    this.items[i].dd = this.items[i].next('dd');
                    this.items[i].link = links[i];
                }
            }

            this.initFromCookie();
        },
        initFromCookie : function () {
            var activeItemId, visibility;
            if (this.activeOnlyOne &&
                (activeItemId = Cookie.read(this.cookiePrefix() + 'active-item')) !== null) {
                this.hideAllItems();
                this.showItem(this.getItemById(activeItemId));
            } else if(!this.activeOnlyOne) {
                this.items.each(function(item){
                    if((visibility = Cookie.read(this.cookiePrefix() + item.id)) !== null) {
                        if(visibility == 0) {
                            this.hideItem(item);
                        } else {
                            this.showItem(item);
                        }
                    }
                }.bind(this));
            }
        },
        cookiePrefix: function () {
            return 'accordion-' + this.containerId + '-';
        },
        getItemById : function (itemId) {
            var result = null;

            this.items.each(function(item){
                if (item.id == itemId) {
                    result = item;
                    throw $break;
                }
            });

            return result;
        },
        clickItem : function(event){
            var item = Event.findElement(event, 'dt');
            if(this.activeOnlyOne){
                this.hideAllItems();
                this.showItem(item);
                Cookie.write(this.cookiePrefix() + 'active-item', item.id, 30*24*60*60);
            }
            else{
                if(this.isItemVisible(item)){
                    this.hideItem(item);
                    Cookie.write(this.cookiePrefix() + item.id, 0, 30*24*60*60);
                }
                else {
                    this.showItem(item);
                    Cookie.write(this.cookiePrefix() + item.id, 1, 30*24*60*60);
                }
            }
            Event.stop(event);
        },
        showItem : function(item){
            if(item && item.link){
                if(item.link.href){
                    this.loadContent(item);
                }

                Element.addClassName(item, 'open');
                Element.addClassName(item.dd, 'open');
            }
        },
        hideItem : function(item){
            Element.removeClassName(item, 'open');
            Element.removeClassName(item.dd, 'open');
        },
        isItemVisible : function(item){
            return Element.hasClassName(item, 'open');
        },
        loadContent : function(item){
            if(item.link.href.indexOf('#') == item.link.href.length-1){
                return;
            }
            if (Element.hasClassName(item.link, 'ajax')) {
                this.loadingItem = item;
                this.loader.load(item.link.href, {updaterId : this.loadingItem.dd.id}, this.setItemContent.bind(this));
                return;
            }
            location.href = item.link.href;
        },
        setItemContent : function(content){
            if (content.isJSON) {
                return;
            }
            this.loadingItem.dd.innerHTML = content;
        },
        hideAllItems : function(){
            for(var i in this.items){
                if(this.items[i].id){
                    Element.removeClassName(this.items[i], 'open');
                    Element.removeClassName(this.items[i].dd, 'open');
                }
            }
        }
    };

})
