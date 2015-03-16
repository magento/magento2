/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint regexdash:true eqnull:true browser:true jquery:true*/
define([], function () {
    var baseUrl = '';
    return {
        getBaseUrl: function () {
            return this.values.baseUrl;
        },
        getFormKey: function() {
            return this.values.formKey;
        }
    }
});
