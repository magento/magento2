<?php
/**
 * Returns an array of Javascript files that should be loaded first by JsTestDriver in the
 * order that they appear in the array when the Javascript unit tests are run.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @return array
 */
return [
    '/lib/web/jquery.js',
    '/lib/web/jquery/jquery-migrate.js',
    '/lib/web/underscore.js',
    '/lib/web/mage/template.js',
    '/lib/web/jquery/jquery-ui-1.9.2.js',
    '/dev/tests/js/JsTestDriver/framework/requirejs-util.js',
    '/lib/web/jquery/jquery.cookie.js',
    '/lib/web/mage/apply/main.js',
    '/lib/web/mage/mage.js',
    '/lib/web/mage/decorate.js',
    '/lib/web/jquery/jquery.validate.js',
    '/lib/web/jquery/jquery.metadata.js',
    '/lib/web/mage/translate.js'
];
