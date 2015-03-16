/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
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
        'jquery',
        'mage/view/composite',
        'mage/url',
        'Magento_Ui/js/view/errors',
        './view/authentication',
        './view/billing-address',
        './view/shipping-method',
        './view/payment',
        './view/review',
        './view/progress',
        'Magento_Customer/js/model/customer'
    ],
    function($, composite, url, errors, authentication, billingAddress, shipping, payment, review, progress, customer) {
        var first = composite();
        var accordion = composite();
        accordion.addChild(authentication, 'authentication');
        accordion.addChild(billingAddress, 'billingAddress');
        accordion.addChild(shipping, 'shipping');
        accordion.addChild(payment, 'payment');
        accordion.addChild(review, 'review');
        first.addChild(errors);
        first.addChild(progress, 'progress');
        first.addChild(accordion, 'accordion');
        return function(options, element) {
            authentication.setFormKey(options.formKey);
            url.setBaseUrl(options.baseUrl);
            customer.setIsLoggedIn(options.isLoggedIn);
            return first.render($(element));
        }
    }
);
