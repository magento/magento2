/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Ui/js/form/component',
    ],
    function (Component) {
        var ownClass = '';
        var columnTitle = '';
        return Component.extend({
            defaults: {
                ownClass: ownClass,
                columnTitle: columnTitle,
                template: 'Magento_Checkout/review/item/column'
            },
            getClass: function() {
                return 'col ' + this.ownClass;
            },
            getColName: function() {
                return this.columnTitle;
            },
            getValue: function(quoteItem) {
                return quoteItem.name;
            }
        });
    }
);
