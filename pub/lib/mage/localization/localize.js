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
 * @category    localization
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

mage = {};

mage.Localize = function(culture) {
    this.localize = Globalize;
    if (culture == null){
        this.localize.culture('en');
    }else{
        this.localize.culture(culture);
    }
    this.dateFormat = ['d', 'D', 'f', 'F', 'M', 'S', 't', 'T', 'Y'];
    this.numberFormat = ['n', 'n1', 'n3', 'd', 'd2', 'd3', 'p', 'p1', 'p3', 'c', 'c0'];
};

mage.Localize.prototype.name = function() {
    return this.localize.culture().name;
};

mage.Localize.prototype.date = function(dateParam, format) {
    if (this.dateFormat.indexOf(format.toString()) < 0){
        return 'Invalid date formatter'
    }
    if(dateParam instanceof Date){
        return this.localize.format(dateParam, format);
    }
    var d = new Date(dateParam.toString());
    if (d == null || d.toString === 'Invalid Date'){
        return d.toString;
    }else{
        return this.localize.format(d, format);
    }
};

mage.Localize.prototype.number = function(numberParam, format) {
    if (this.numberFormat.indexOf(format.toString()) < 0){
        return 'Invalid date formatter'
    }
    if(typeof numberParam === 'number'){
        return this.localize.format(numberParam, format);
    }
    var num = Number(numberParam);
    if (num == null || isNaN(num)){
        return numberParam;
    }else{
        return this.localize.format(num, format);
    }
};

mage.Localize.prototype.currency = function(currencyParam) {
    if(typeof currencyParam === 'number'){
        return this.localize.format(currencyParam, 'c');
    }
    var num = Number(currencyParam);
    if (num == null || isNaN(num)){
        return currencyParam;
    }else{
        return this.localize.format(num, 'c');
    }
};