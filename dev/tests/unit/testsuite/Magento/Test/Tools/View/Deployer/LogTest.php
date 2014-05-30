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

namespace Magento\Test\Tools\View\Deployer;

use \Magento\Tools\View\Deployer\Log;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param int $verbosity
     * @param string $exepctedMsg
     * @dataProvider logDataProvider
     */
    public function testLog($method, $verbosity, $exepctedMsg)
    {
        $object = new Log($verbosity);
        $object->$method('foo');
        $this->expectOutputString($exepctedMsg);
    }

    /**
     * @return array
     */
    public function logDataProvider()
    {
        $foo = "foo\n";
        $err = "ERROR: {$foo}";
        return [
            ['logMessage', Log::SILENT, ''],
            ['logError',   Log::SILENT, ''],
            ['logDebug',   Log::SILENT, ''],
            ['logMessage', Log::ERROR, $foo],
            ['logError',   Log::ERROR, $err],
            ['logDebug',   Log::ERROR, ''],
            ['logMessage', Log::DEBUG, $foo],
            ['logError',   Log::DEBUG, ''],
            ['logDebug',   Log::DEBUG, $foo],
            ['logMessage', Log::ERROR | Log::DEBUG, $foo],
            ['logError',   Log::ERROR | Log::DEBUG, $err],
            ['logDebug',   Log::ERROR | Log::DEBUG, $foo],
        ];
    }
}
