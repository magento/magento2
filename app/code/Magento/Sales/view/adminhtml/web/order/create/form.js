/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

define([
	"jquery",
    "Magento_Sales/order/create/scripts"
], function(jQuery){
	var $el = jQuery('#edit_form'),
		config,
		baseUrl,
        order,
        payment;

    if( !$el.length || !$el.data('order-config') ){
        return;
    }

    config = $el.data('order-config');
    baseUrl = $el.data('load-base-url');

    order = new AdminOrder(config);
    order.setLoadBaseUrl(baseUrl);
    
    payment = {
        switchMethod: order.switchPaymentMethod.bind(order)
    };

    window.order = order;
    window.payment = payment;
});