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
        setBaseUrl: function (url) {
            baseUrl = url;
        },
        build: function(path) {
            if (path.indexOf(baseUrl) != -1) {
                return path;
            }
            return baseUrl + path;
        }
    }
});
