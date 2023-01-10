/*
 * JavaScript Load Image
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, module, Promise */

;(function ($) {
  'use strict'

  var urlAPI = $.URL || $.webkitURL

  /**
   * Creates an object URL for a given File object.
   *
   * @param {Blob} blob Blob object
   * @returns {string|boolean} Returns object URL if API exists, else false.
   */
  function createObjectURL(blob) {
    return urlAPI ? urlAPI.createObjectURL(blob) : false
  }

  /**
   * Revokes a given object URL.
   *
   * @param {string} url Blob object URL
   * @returns {undefined|boolean} Returns undefined if API exists, else false.
   */
  function revokeObjectURL(url) {
    return urlAPI ? urlAPI.revokeObjectURL(url) : false
  }

  /**
   * Helper function to revoke an object URL
   *
   * @param {string} url Blob Object URL
   * @param {object} [options] Options object
   */
  function revokeHelper(url, options) {
    if (url && url.slice(0, 5) === 'blob:' && !(options && options.noRevoke)) {
      revokeObjectURL(url)
    }
  }

  /**
   * Loads a given File object via FileReader interface.
   *
   * @param {Blob} file Blob object
   * @param {Function} onload Load event callback
   * @param {Function} [onerror] Error/Abort event callback
   * @param {string} [method=readAsDataURL] FileReader method
   * @returns {FileReader|boolean} Returns FileReader if API exists, else false.
   */
  function readFile(file, onload, onerror, method) {
    if (!$.FileReader) return false
    var reader = new FileReader()
    reader.onload = function () {
      onload.call(reader, this.result)
    }
    if (onerror) {
      reader.onabort = reader.onerror = function () {
        onerror.call(reader, this.error)
      }
    }
    var readerMethod = reader[method || 'readAsDataURL']
    if (readerMethod) {
      readerMethod.call(reader, file)
      return reader
    }
  }

  /**
   * Cross-frame instanceof check.
   *
   * @param {string} type Instance type
   * @param {object} obj Object instance
   * @returns {boolean} Returns true if the object is of the given instance.
   */
  function isInstanceOf(type, obj) {
    // Cross-frame instanceof check
    return Object.prototype.toString.call(obj) === '[object ' + type + ']'
  }

  /**
   * @typedef { HTMLImageElement|HTMLCanvasElement } Result
   */

  /**
   * Loads an image for a given File object.
   *
   * @param {Blob|string} file Blob object or image URL
   * @param {Function|object} [callback] Image load event callback or options
   * @param {object} [options] Options object
   * @returns {HTMLImageElement|FileReader|Promise<Result>} Object
   */
  function loadImage(file, callback, options) {
    /**
     * Promise executor
     *
     * @param {Function} resolve Resolution function
     * @param {Function} reject Rejection function
     * @returns {HTMLImageElement|FileReader} Object
     */
    function executor(resolve, reject) {
      var img = document.createElement('img')
      var url
      /**
       * Callback for the fetchBlob call.
       *
       * @param {HTMLImageElement|HTMLCanvasElement} img Error object
       * @param {object} data Data object
       * @returns {undefined} Undefined
       */
      function resolveWrapper(img, data) {
        if (resolve === reject) {
          // Not using Promises
          if (resolve) resolve(img, data)
          return
        } else if (img instanceof Error) {
          reject(img)
          return
        }
        data = data || {} // eslint-disable-line no-param-reassign
        data.image = img
        resolve(data)
      }
      /**
       * Callback for the fetchBlob call.
       *
       * @param {Blob} blob Blob object
       * @param {Error} err Error object
       */
      function fetchBlobCallback(blob, err) {
        if (err && $.console) console.log(err) // eslint-disable-line no-console
        if (blob && isInstanceOf('Blob', blob)) {
          file = blob // eslint-disable-line no-param-reassign
          url = createObjectURL(file)
        } else {
          url = file
          if (options && options.crossOrigin) {
            img.crossOrigin = options.crossOrigin
          }
        }
        img.src = url
      }
      img.onerror = function (event) {
        revokeHelper(url, options)
        if (reject) reject.call(img, event)
      }
      img.onload = function () {
        revokeHelper(url, options)
        var data = {
          originalWidth: img.naturalWidth || img.width,
          originalHeight: img.naturalHeight || img.height
        }
        try {
          loadImage.transform(img, options, resolveWrapper, file, data)
        } catch (error) {
          if (reject) reject(error)
        }
      }
      if (typeof file === 'string') {
        if (loadImage.requiresMetaData(options)) {
          loadImage.fetchBlob(file, fetchBlobCallback, options)
        } else {
          fetchBlobCallback()
        }
        return img
      } else if (isInstanceOf('Blob', file) || isInstanceOf('File', file)) {
        url = createObjectURL(file)
        if (url) {
          img.src = url
          return img
        }
        return readFile(
          file,
          function (url) {
            img.src = url
          },
          reject
        )
      }
    }
    if ($.Promise && typeof callback !== 'function') {
      options = callback // eslint-disable-line no-param-reassign
      return new Promise(executor)
    }
    return executor(callback, callback)
  }

  // Determines if metadata should be loaded automatically.
  // Requires the load image meta extension to load metadata.
  loadImage.requiresMetaData = function (options) {
    return options && options.meta
  }

  // If the callback given to this function returns a blob, it is used as image
  // source instead of the original url and overrides the file argument used in
  // the onload and onerror event callbacks:
  loadImage.fetchBlob = function (url, callback) {
    callback()
  }

  loadImage.transform = function (img, options, callback, file, data) {
    callback(img, data)
  }

  loadImage.global = $
  loadImage.readFile = readFile
  loadImage.isInstanceOf = isInstanceOf
  loadImage.createObjectURL = createObjectURL
  loadImage.revokeObjectURL = revokeObjectURL

  if (typeof define === 'function' && define.amd) {
    define(function () {
      return loadImage
    })
  } else if (typeof module === 'object' && module.exports) {
    module.exports = loadImage
  } else {
    $.loadImage = loadImage
  }
})((typeof window !== 'undefined' && window) || this)
