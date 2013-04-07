<?php
/**
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
 * @category    Magento
 * @package     unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/../../../../app/code/Mage/Core/functions.php';
require __DIR__ . '/../../../../app/autoload.php';
Magento_Autoload_IncludePath::addIncludePath(array(
    __DIR__,
    realpath(__DIR__ . '/../testsuite'),
    realpath(__DIR__ . '/../../../../app'),
    realpath(__DIR__ . '/../../../../app/code'),
    realpath(__DIR__ . '/../../../../lib'),
    realpath(__DIR__ . '/../../../../var/generation'),
));
define('BP', realpath(__DIR__ . '/../../../../'));
define('TESTS_TEMP_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp');
define('DS', DIRECTORY_SEPARATOR);
if (is_dir(TESTS_TEMP_DIR)) {
    Varien_Io_File::rmdirRecursive(TESTS_TEMP_DIR);
}
mkdir(TESTS_TEMP_DIR);

Mage::setIsSerializable(false);
