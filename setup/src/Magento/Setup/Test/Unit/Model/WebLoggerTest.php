<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\WebLogger;

class WebLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Write
     */
    private $directoryWriteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var string
     */
    private static $log;

    /**
     * @var WebLogger
     */
    private $webLogger;

    public function setUp()
    {
        self::$log = '';

        $this->directoryWriteMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->directoryWriteMock
            ->expects($this->any())
            ->method('readFile')
            ->with('install.log')
            ->will($this->returnCallback(['Magento\Setup\Test\Unit\Model\WebLoggerTest', 'readLog']));
        $this->directoryWriteMock
            ->expects($this->any())
            ->method('writeFile')
            ->with('install.log')
            ->will($this->returnCallback(['Magento\Setup\Test\Unit\Model\WebLoggerTest', 'writeToLog']));
        $this->directoryWriteMock
            ->expects($this->any())
            ->method('isExist')
            ->will($this->returnCallback(['Magento\Setup\Test\Unit\Model\WebLoggerTest', 'isExist']));

        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->directoryWriteMock));

        $this->webLogger = new WebLogger($this->filesystemMock);
    }

    public function testConstructorLogFileSpecified()
    {
        $logFile = 'custom.log';
        $directoryWriteMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $directoryWriteMock->expects($this->once())->method('readFile')->with($logFile);
        $directoryWriteMock->expects($this->once())->method('writeFile')->with($logFile);

        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($directoryWriteMock));

        $webLogger = new WebLogger($filesystemMock, $logFile);

        $webLogger->log('Message');
        $webLogger->get();
    }

    public function testLogSuccess()
    {
        $this->webLogger->logSuccess('Success1');
        $this->assertEquals('<span class="text-success">[SUCCESS] ' . 'Success1' . '</span><br>', self::$log);

        $this->webLogger->logSuccess('Success2');
        $this->assertEquals(
            '<span class="text-success">[SUCCESS] ' . 'Success1' . '</span><br>' .
            '<span class="text-success">[SUCCESS] ' . 'Success2' . '</span><br>',
            self::$log
        );
    }

    public function testLogError()
    {
        $e1 = new \Exception('Dummy Exception1');
        $e2 = new \Exception('Dummy Exception2');

        $this->webLogger->logError($e1);
        $this->assertContains('[ERROR]', self::$log);
        $this->assertContains('Exception', self::$log);
        $this->assertContains($e1->getMessage(), self::$log);

        $this->webLogger->logError($e2);
        $this->assertContains('[ERROR]', self::$log);
        $this->assertContains('Exception', self::$log);
        $this->assertContains($e1->getMessage(), self::$log);
        $this->assertContains($e2->getMessage(), self::$log);
    }

    public function testLog()
    {
        $this->webLogger->log('Message1');
        $this->assertEquals('<span class="text-info">Message1</span><br>', self::$log);

        $this->webLogger->log('Message2');
        $this->assertEquals(
            '<span class="text-info">Message1</span><br><span class="text-info">Message2</span><br>',
            self::$log
        );
    }

    public function testLogAfterInline()
    {
        $this->webLogger->logInline('*');
        $this->webLogger->log('Message');
        $this->assertEquals(
            '<span class="text-info">*</span><br><span class="text-info">Message</span><br>',
            self::$log
        );
    }

    public function testLogInline()
    {
        $this->webLogger->logInline('*');
        $this->assertEquals('<span class="text-info">*</span>', self::$log);

        $this->webLogger->logInline('*');
        $this->assertEquals('<span class="text-info">*</span><span class="text-info">*</span>', self::$log);
    }

    public function testLogMeta()
    {
        $this->webLogger->logMeta('Meta1');
        $this->assertEquals('<span class="hidden">Meta1</span><br>', self::$log);

        $this->webLogger->logMeta('Meta2');
        $this->assertEquals('<span class="hidden">Meta1</span><br><span class="hidden">Meta2</span><br>', self::$log);
    }

    public function testGet()
    {
        $this->webLogger->log('Message1' . PHP_EOL);
        $this->webLogger->log('Message2');

        $expected = [
            '<span class="text-info">Message1',
            '</span><br><span class="text-info">Message2</span><br>',
        ];

        $this->assertEquals($expected, $this->webLogger->get());
    }

    public function testClear()
    {
        $this->directoryWriteMock
            ->expects($this->once())
            ->method('delete')
            ->will($this->returnCallback(['Magento\Setup\Test\Unit\Model\WebLoggerTest', 'deleteLog']));

        $this->webLogger->log('Message1');
        $this->assertEquals('<span class="text-info">Message1</span><br>', self::$log);

        $this->webLogger->clear();
        $this->assertEquals('', self::$log);
    }

    public function testClearNotExist()
    {
        $this->directoryWriteMock
            ->expects($this->never())
            ->method('delete');

        $this->webLogger->clear();
    }

    public static function readLog()
    {
        return self::$log;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function writeToLog($logFile, $message)
    {
        self::$log .= $message;
    }

    public static function deleteLog()
    {
        self::$log = '';
    }

    public static function isExist()
    {
        return self::$log != '';
    }
}
