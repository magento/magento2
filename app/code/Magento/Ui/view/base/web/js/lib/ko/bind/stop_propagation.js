/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates stopPropagation binding and registers in to ko.bindingHandlers object */
define(['ko'], function (ko) {
    'use strict';

    ko.bindingHandlers.stopPropagation = {

        /**
         * Stops propagation on element
         * @param  {HTMLElement} element - element to apply binding to
         */
        init: function (element) {
          ko.utils.registerEventHandler(element, 'click', function (event) {
              event.cancelBubble = true;
              if (event.stopPropagation) {
                 event.stopPropagation(); 
              }
          });
        }
    };
});