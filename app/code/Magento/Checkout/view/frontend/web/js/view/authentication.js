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
        'text!./templates/authentication.html',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/action/login'
    ],
    function($, template, customer, login) {
        var formKey;
        var root;
        wrapped = customer.setIsLoggedIn;
        var object = {
            setFormKey: function(key) {
                formKey = key;
            },
            render: function (newRoot) {
                root = newRoot || root;
                customer.setIsLoggedIn = function (value) {
                    wrapped(value);
                    value ? object.hide() : object.show();
                };
                if (!customer.isLoggedIn()) {
                    root.html(template);
                }
                root.find('#login-form').on('submit', function (e) {
                    e.preventDefault();
                    login(root.find('#login').val(), root.find('#password').val(), formKey);
                });
            },
            hide: function () {
                root.hide(1000);
            },
            show: function () {
                root.show(1000);
            }
        };
        return object;
    }
);
