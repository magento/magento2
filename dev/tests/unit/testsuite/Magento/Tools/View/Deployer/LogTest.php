<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\View\Deployer;

use Magento\Tools\View\Deployer\Log;

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
