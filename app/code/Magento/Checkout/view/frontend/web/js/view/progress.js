/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['text!./templates/progress.html'], function(template) {
    return {
        render: function (root) {
            return root.append(template);
        }
    }
});
