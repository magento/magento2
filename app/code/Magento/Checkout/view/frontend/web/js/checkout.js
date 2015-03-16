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
        'mage/url',
        './view/authentication',
        'Magento_Customer/js/model/customer'
    ],
    function( url, authentication, customer) {
        return function (element, options) {
            return {
                launch: function (root, baseUrl, formKey, isLoggedIn) {
                    authentication.setFormKey(formKey);
                    url.setBaseUrl(baseUrl);
                    customer.setIsLoggedIn(isLoggedIn);
                    return first.render(root);
                }
            };
        };
    }
);
