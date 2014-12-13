/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/cookies"
], function($){
     "use strict";
    
    /**
     * MsgBox Widget checks if message box is displayed and sets cookie
     */
    $.widget('mage.msgBox', {
        options: {
            msgBoxCookieName: 'message_box_display',
            msgBoxSelector: '.main div.messages'
        },
        _create: function() {
            if ($.mage.cookies.get(this.options.msgBoxCookieName)) {
                $.mage.cookies.set(this.options.msgBoxCookieName, null, {expires: new Date(), path: "/"});
            } else {
                $(this.options.msgBoxSelector).hide();
            }
        }
    });

    $(document).ready(function($){
        $('body').msgBox();
    });

});