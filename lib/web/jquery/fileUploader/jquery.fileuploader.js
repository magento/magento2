/**
 * Custom Uploader
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global define, require */

(function (factory) {
  'use strict';
  if (typeof define === 'function' && define.amd) {
    // Register as an anonymous AMD module:
    define([
      'jquery',
      'jquery/fileUploader/jquery.fileupload-image',
      'jquery/fileUploader/jquery.fileupload-audio',
      'jquery/fileUploader/jquery.fileupload-video',
      'jquery/fileUploader/jquery.iframe-transport',
    ], factory);
  } else if (typeof exports === 'object') {
    // Node/CommonJS:
    factory(
      require('jquery'),
      require('jquery/fileUploader/jquery.fileupload-image'),
      require('jquery/fileUploader/jquery.fileupload-audio'),
      require('jquery/fileUploader/jquery.fileupload-video'),
      require('jquery/fileUploader/jquery.iframe-transport')
    );
  } else {
    // Browser globals:
    factory(window.jQuery);
  }
})();
