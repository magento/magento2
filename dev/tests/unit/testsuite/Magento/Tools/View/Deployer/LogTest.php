<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\View\Deployer;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param int $verbosity
     * @param string $expectedMsg
     * @dataProvider logDataProvider
     */
    public function testLog($method, $verbosity, $expectedMsg)
    {
        $object = new Log($verbosity);
        $object->$method('foo');
        $this->expectOutputString($expectedMsg);
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

    /**
     * @param int $verbosity
     * @param string $expectedMsg
     *
     * @dataProvider logDebugAltDataProvider
     */
    public function testLogDebugAlt($verbosity, $expectedMsg)
    {
        $object = new Log($verbosity);
        $object->logDebug('foo', '[alt]');
        $this->expectOutputString($expectedMsg);
    }

    /**
     * @return array
     */
    public function logDebugAltDataProvider()
    {
        return[
            'debug mode' => [Log::DEBUG, "foo\n"],
            'default mode' => [Log::ERROR, '[alt]'],
            'silent mode' => [Log::SILENT, '']
        ];
    }
}
