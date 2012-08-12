/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
var mage = {}; //top level mage namespace

// mage.event is a wrapper for jquery event
mage.event = (function () {
  return {
    trigger: function (customEvent, data) {
      $(document).triggerHandler(customEvent.toString(), data);
    },
    observe: function (customEvent, func) {
      $(document).on(customEvent.toString(), func);
    }
  };
}());
// load javascript by data attribute or mage.load
(function () {
  var syncQueue = [];
  var asyncQueue = [];

  function addToQueue(arr, queue) {
    for ( var i = 0; i < arr.length; i++ ) {
      if ( typeof arr[i] === 'string' && $.inArray(arr[i], queue) === -1 ) {
        queue.push(arr[i]);
      }
    }
  }

  function unique(arr) {
    var uniqueArr = [];
    for ( var i = arr.length; i--; ) {
      var val = arr[i];
      if ( $.inArray(val, uniqueArr) === -1 ) {
        uniqueArr.unshift(val);
      }
    }
    return uniqueArr;
  }

  function load_script() {
    //add sync load js file to syncQueue
    $('[data-js-sync]').each(function () {
      var jsFiles = $(this).attr('data-js-sync').split(" ");
      syncQueue = $.merge(jsFiles, syncQueue);
    });
    syncQueue = unique(syncQueue);
    if ( syncQueue.length > 0 ) {
      syncQueue.push(function () {
        async_load();
      });
      head.js.apply({}, syncQueue);
    } else {
      async_load();
    }
  }

  function async_load() {
    //add async load js file to asyncQueue
    $('[data-js]').each(function () {
      var jsFiles = $(this).attr('data-js').split(" ");
      asyncQueue = $.merge(jsFiles, asyncQueue);
    });
    asyncQueue = unique(asyncQueue);
    var x = document.getElementsByTagName('script')[0];
    for ( var i = 0; i < asyncQueue.length; i++ ) {
      var s = document.createElement('script');
      s.type = 'text/javascript';
      s.src = asyncQueue[i];
      x.parentNode.appendChild(s);
    }
  }

  $(window).on('load', load_script);
  mage.load = (function () {
    return {
      jsSync: function () {
        addToQueue(arguments, syncQueue);
        return syncQueue.length;
      },
      js: function () {
        addToQueue(arguments, asyncQueue);
        return asyncQueue.length;
      },
      language: function (lang) {
        var defaultLangauge = 'en';
        var cookieName = 'language';
        var language = (lang != null) ? lang : $.cookie(cookieName);
        if ( (language != null ) && (language !== defaultLangauge  ) ) {
          var mapping = {
            'localize': ['/pub/lib/globalize/globalize.js', '/pub/lib/globalize/cultures/globalize.culture.' + language + '.js', '/pub/lib/localization/json/translate_' + language + '.js',
              '/pub/lib/mage/localization/localize.js']
          };
          addToQueue(mapping.localize, syncQueue);
        }
        return syncQueue.length;
      }

    };
  }());
})();


