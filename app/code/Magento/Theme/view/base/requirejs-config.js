/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    "waitSeconds": 0,
    "shim": {
        "jquery/jquery-migrate": ["jquery"],
        "jquery/jquery.hashchange": ["jquery"],
        "jquery/jstree/jquery.hotkeys": ["jquery"],
        "jquery/hover-intent": ["jquery"],
        "mage/adminhtml/backup": ["prototype"],
        "mage/adminhtml/tools": ["prototype"],
        "mage/adminhtml/varienLoader": ["prototype"],
        "mage/captcha": ["prototype"],
        "mage/common": ["jquery"],
        "mage/requirejs/plugin/id-normalizer": ["jquery"],
        "mage/webapi": ["jquery"],
        "jquery/ui": ["jquery"],
        "tinymce": {
            "exports": "tinymce"
        },
        "ko": {
            "exports": "ko"
        },
        "moment": {
            "exports": "moment"
        },
        "matchMedia": {
            "exports": "mediaCheck"
        }
    },
    "paths": {
        "jquery/validate": "jquery/jquery.validate",
        "jquery/hover-intent": "jquery/jquery.hoverIntent",
        "jquery/template": "jquery/jquery.tmpl.min",
        "jquery/file-uploader": "jquery/fileUploader/jquery.fileupload-fp",
        "handlebars": "jquery/handlebars/handlebars-v1.3.0",
        "jquery/jquery.hashchange": "jquery/jquery.ba-hashchange.min",
        "prototype": "prototype/prototype-amd",
        "text": "requirejs/text",
        "domReady": "requirejs/domReady",
        "ko": "ko/ko",
        "tinymce": "tiny_mce/tiny_mce_src"
    },
    "deps": [
        "jquery/jquery-migrate"
    ]
};