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
 * @package     Mage_Page
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint evil:true */
// Temporary solution, will be replaced when plug-in "mage" will be merged to master
(function($){
	"use strict";
	var bootstrap = function() {
		$('[data-mage-init]').each(function(){
			var inits = $(this).data('mage-init') || {};
			// in case it's not well-formed JSON inside data attribute, evaluate it manually
			if (typeof inits === 'string') {
				try {
					inits = eval('(' + inits + ')');
				} catch (e) {
					inits = {};
				}
			}
			$.each(inits, $.proxy(function(key, args){
				$(this)[key].apply($(this), $.makeArray(args));
			}, this));
		});
	};
	$(document).ready(bootstrap);
})(jQuery);
