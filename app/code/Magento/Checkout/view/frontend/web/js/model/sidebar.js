/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [],
    function() {
        'use strict';
        return {
            popUp: false,
            setPopup: function(popUp) {
                this.popUp = popUp;
            },
            show: function() {
                if (this.popUp) {
                    this.popUp.modal('openModal');
                }
            },
            hide: function() {
                if (this.popUp) {
                    this.popUp.modal('closeModal');
                }
            }
        };
    }
);
