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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
require.config({
    "waitSeconds":"0",
    "shim": {
        "jquery/jquery.hashchange": ["jquery"],
        "jquery/jquery.mousewheel": ["jquery"],
        "jquery/jquery.popups": ["jquery"],
        "jquery/jquery-migrate": ["jquery"],
        "jquery/jstree/jquery.hotkeys": ["jquery"],
        "jquery/hover-intent": ["jquery"],
        "mage/adminhtml/backup": ["prototype"],
        "mage/adminhtml/tools": ["prototype"],
        "mage/adminhtml/varienLoader": ["prototype"],
        "mage/captcha": ["prototype"],
        "mage/common": ["jquery"],
        "mage/jquery-no-conflict": ["jquery"],
        "mage/requirejs/plugin/id-normalizer": ["jquery"],
        "mage/webapi": ["jquery"],
        "ko": { exports: 'ko' },
        "moment": { exports: 'moment' }
    },
    "paths":{
        "baseImage": 'Magento_Catalog/catalog/base-image-uploader',
        "jquery/validate": "jquery/jquery.validate",
        "jquery/hover-intent": "jquery/jquery.hoverIntent",
        "jquery/template": "jquery/jquery.tmpl.min",
        "jquery/farbtastic": "jquery/farbtastic/jquery.farbtastic",
        "jquery/file-uploader": "jquery/fileUploader/jquery.fileupload-fp",
        "handlebars": "jquery/handlebars/handlebars-v1.3.0",
        "jquery/jquery.hashchange": "jquery/jquery.ba-hashchange.min",
        "prototype": "prototype/prototype-amd",
        "text": "requirejs/text",
        "ko": "ko/ko"
    },
    "deps": [
        "bootstrap"
    ]
});

require([
    'jquery',
    'mage/components',
    'mage/mage'
], function($, components){
    $.mage.component( components );
});
