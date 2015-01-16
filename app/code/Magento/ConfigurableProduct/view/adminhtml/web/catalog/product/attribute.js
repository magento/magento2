/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
	"jquery",
	"jquery/ui",
	"Magento_Catalog/catalog/product"
], function($){
    "use strict";
    
    $.widget("mage.configurableAttribute", $.mage.productAttributes, {
        _prepareUrl: function() {
            var name = $('#configurable-attribute-selector').val();
            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?') +
                'set=' + window.encodeURIComponent($('#attribute_set_id').val()) +
                '&attribute[frontend_label]=' +
                window.encodeURIComponent(name);
        }
    });

    return $.mage.configurableAttribute;
});