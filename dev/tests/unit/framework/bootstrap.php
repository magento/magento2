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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/tmp');
}
require __DIR__ . '/../../../../app/autoload.php';
require BP . '/app/functions.php';
(new \Magento\Framework\Autoload\IncludePath())->addIncludePath(
    array(
        __DIR__,
        realpath(__DIR__ . '/../testsuite'),
        realpath(BP . '/app'),
        realpath(BP . '/app/code'),
        realpath(BP . '/lib/internal')
    )
);
if (is_dir(TESTS_TEMP_DIR)) {
    $filesystemAdapter = new \Magento\Framework\Filesystem\Driver\File();
    $filesystemAdapter->deleteDirectory(TESTS_TEMP_DIR);
}
mkdir(TESTS_TEMP_DIR);

\Magento\Framework\Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());

function tool_autoloader($className)
{
    if (strpos($className, 'Magento\\Tools\\') === false) {
        return false;
    }
    $filePath = str_replace('\\', '/', $className);
    $filePath = BP . '/dev/tools/' . $filePath . '.php';

    if (file_exists($filePath)) {
        include_once $filePath;
    } else {
        return false;
    }
}
spl_autoload_register('tool_autoloader');
error_reporting(E_ALL);
ini_set('display_errors', 1);
