<?php
/**
 * Returns an array of Javascript files that should be loaded first by JsTestDriver in the
 * order that they appear in the array when the Javascript unit tests are run.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @return array
 */
return array(
    '/lib/web/jquery/jquery-1.8.2.js',
    '/lib/web/jquery/jquery-ui-1.9.2.js',
    '/dev/tests/js/framework/requirejs-util.js',
    '/lib/web/jquery/jquery.cookie.js',
    '/lib/web/mage/mage.js',
    '/lib/web/mage/decorate.js',
    '/lib/web/jquery/jquery.validate.js',
    '/lib/web/jquery/jquery.metadata.js',
    '/lib/web/mage/translate.js',
    '/lib/web/mage/validation.js',
    '/lib/web/mage/requirejs/plugin/id-normalizer.js',
);
