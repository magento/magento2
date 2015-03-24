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
        'mage/storage',
        '../model/customer',
        'Magento_Ui/js/model/errorlist',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function($, storage, customer, errorlist, navigator) {
        return function(login, password) {
            return storage.post(
                'customer/ajax/login',
                JSON.stringify({'username': login, 'password': password})
            ).done(function (response) {
                if (response) {
                    customer.setIsLoggedIn(true);
                    navigator.toStep('billingAddress');
                } else {
                    errorlist.add('Server returned no response');
                }
            }).fail(function (response) {
                if (response.status == 401) {
                    errorlist.add('Invalid login or password');
                } else {
                    errorlist.add('Could not authenticate. Please try again later');
                }
            });
        }
    }
);
