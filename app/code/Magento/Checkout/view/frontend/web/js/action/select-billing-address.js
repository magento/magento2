/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(['jquery', '../model/quote'], function($, quote) {
  'use strict';

  return function(billingAddress) {
    var address = null;

    if (
      quote.shippingAddress() &&
      billingAddress.getCacheKey() == quote.shippingAddress().getCacheKey() //eslint-disable-line eqeqeq
    ) {
      address = $.extend({}, billingAddress);
      address.saveInAddressBook = null;
    } else {
      address = billingAddress;
    }
    quote.billingAddress(address);
  };
});
