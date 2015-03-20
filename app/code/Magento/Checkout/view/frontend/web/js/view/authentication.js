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
        'ko',
        'Magento_Ui/js/form/component',
        'Magento_Customer/js/action/login',
        'Magento_Customer/js/model/customer'
    ],
    function(ko, Component, login, customer) {
        return Component.extend({
            isLoggedIn: customer.isLoggedIn(),
            isAllowedGuestCheckout: true,
            isRegistrationAllowed: true,
            isMethodRegister: false,
            isCustomerMustBeLoged: false,
            registerUrl: '',
            forgotPasswordUrl: '',
            username: '',
            password: '',
            defaults: {
                template: 'Magento_Checkout/authentication'
            },
            login: function() {
                login(this.username, this.password);
            }
        });
    }
);
