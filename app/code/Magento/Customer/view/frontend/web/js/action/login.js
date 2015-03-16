/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['mage/storage', '../model/customer', 'Magento_Core/js/model/errorlist'],
    function(storage, customer, errorlist) {
        return function(login, password, formKey) {
            return storage.post(
                'customer/account/loginpost',
                {'login': {'username': login, 'password': password}, 'form_key': formKey}
            ).done(function (response) {
                var error;
                if (response) {
                    var result = $.parseJSON(response);
                    if (result.error) {
                        error = result.error;
                    }
                }
                if (error) {
                    errorlist.add(result.error);
                } else {
                    customer.setIsLoggedIn(true);
                }
            }).fail(function () {
                errorlist.add('Server doesn\'t respond');
            });
        }
    }
);
