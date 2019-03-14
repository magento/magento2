/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    'waitSeconds': 0,
    'map': {
        '*': {
            'ko': 'knockoutjs/knockout',
            'knockout': 'knockoutjs/knockout',
            'mageUtils': 'mage/utils/main',
            'rjsResolver': 'mage/requirejs/resolver'
        }
    },
    'shim': {
        'jquery/jquery-migrate': ['jquery'],
        'jquery/jstree/jquery.hotkeys': ['jquery'],
        'jquery/hover-intent': ['jquery'],
        'mage/adminhtml/backup': ['prototype'],
        'mage/captcha': ['prototype'],
        'mage/common': ['jquery'],
        'mage/new-gallery': ['jquery'],
        'mage/webapi': ['jquery'],
        'jquery/ui': ['jquery'],
        'MutationObserver': ['es6-collections'],
        'moment': {
            'exports': 'moment'
        },
        'matchMedia': {
            'exports': 'mediaCheck'
        },
        'jquery/jquery-storageapi': {
            'deps': ['jquery/jquery.cookie']
        }
    },
    'paths': {
        'jquery/validate': 'jquery/jquery.validate',
        'jquery/hover-intent': 'jquery/jquery.hoverIntent',
        'jquery/file-uploader': 'jquery/fileUploader/jquery.fileupload-fp',
        'prototype': 'legacy-build.min',
        'jquery/jquery-storageapi': 'jquery/jquery.storageapi.min',
        'text': 'mage/requirejs/text',
        'domReady': 'requirejs/domReady',
        'spectrum': 'jquery/spectrum/spectrum',
        'tinycolor': 'jquery/spectrum/tinycolor'
    },
    'deps': [
        'jquery/jquery-migrate'
    ],
    'config': {
        'mixins': {
            'jquery/jstree/jquery.jstree': {
                'mage/backend/jstree-mixin': true
            },
            'jquery': {
                'jquery/patches/jquery': true
            }
        },
        'text': {
            'headers': {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    }
};

require(['jquery'], function ($) {
    'use strict';

    $.noConflict();
});
