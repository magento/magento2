/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    "jquery",
    "jquery/ui"
], function($){
	'use strict';
	
	//Widget Wrapper
	$.widget('mage.tooltip', $.ui.tooltip, {
	});

    return $.mage.tooltip;
});
