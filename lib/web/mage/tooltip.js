/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
