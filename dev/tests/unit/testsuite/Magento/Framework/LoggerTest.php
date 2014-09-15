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
namespace Magento\Framework;

use Magento\Framework\Filesystem\Directory\Write;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model = null;

    /**
     * @var \ReflectionProperty
     */
    protected $loggersProperty = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var Write | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directory;

    /**
     * @var string
     */
    private static $logDir;

    public static function setUpBeforeClass()
    {
        self::$logDir = TESTS_TEMP_DIR . '/var/log';
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
    }

    public static function tearDownAfterClass()
    {
        $filesystemAdapter = new \Magento\Framework\Filesystem\Driver\File();
        $filesystemAdapter->deleteDirectory(self::$logDir);
    }

    protected function setUp()
    {
        $logDir = self::$logDir;
        $this->filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', [], [], '', false);
        $this->directory = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::LOG_DIR)
            ->will($this->returnValue($this->directory));
        $this->directory->expects($this->any())->method('create')->will($this->returnValue(true));
        $this->directory->expects($this->any())->method('getAbsolutePath')->will(
            $this->returnCallback(
                function ($path) use ($logDir) {
                    $path = ltrim($path, '\/');
                    return $logDir . '/' . $path;
                }
            )
        );

        $this->model = new \Magento\Framework\Logger($this->filesystemMock);
        $this->loggersProperty = new \ReflectionProperty($this->model, '_loggers');
        $this->loggersProperty->setAccessible(true);
    }

    protected function tearDown()
    {
        $this->model = null; // will cause __descruct() in the underlying log class, which will close the open log files
    }

    /**
     * @param string $key
     * @param string $fileOrWrapper
     * @dataProvider addStreamLogDataProvider
     */
    public function testAddStreamLog($key, $fileOrWrapper)
    {
        $this->assertFalse($this->model->hasLog($key));
        $this->model->addStreamLog($key, $fileOrWrapper);
        $this->assertTrue($this->model->hasLog($key));

        $loggers = $this->loggersProperty->getValue($this->model);
        $this->assertArrayHasKey($key, $loggers);
        $zendLog = $loggers[$key];
        $this->assertInstanceOf('Zend_Log', $zendLog);

        $writersProperty = new \ReflectionProperty($zendLog, '_writers');
        $writersProperty->setAccessible(true);
        $writers = $writersProperty->getValue($zendLog);
        $this->assertArrayHasKey(0, $writers);
        $stream = $writers[0];
        $this->assertInstanceOf('Zend_Log_Writer_Stream', $writers[0]);

        $streamProperty = new \ReflectionProperty($stream, '_stream');
        $streamProperty->setAccessible(true);
        $fileOrWrapper = $streamProperty->getValue($stream);
        $this->assertInternalType('resource', $fileOrWrapper);
        $this->assertEquals('stream', get_resource_type($fileOrWrapper));
    }

    /**
     * @return array
     */
    public function addStreamLogDataProvider()
    {
        return [['test', 'php://output'], ['test', 'custom_file.log'], ['test', '']];
    }

    /**
     * @covers \Magento\Framework\Logger::hasLog
     */
    public function testAddLogWithSpecificKey()
    {
        $key = uniqid();
        $this->model->addStreamLog($key);
        $this->assertTrue($this->model->hasLog($key));
    }

    public function testLog()
    {
        $messageOne = uniqid();
        $messageTwo = uniqid();
        $messageThree = uniqid();
        $this->expectOutputRegex(
            '/' . 'DEBUG \(7\).+?' . $messageTwo . '.+?' . 'CRIT \(2\).+?' . $messageThree . '/s'
        );
        $this->model->addStreamLog('test', 'php://output');
        $this->model->log($messageOne);
        $this->model->log($messageTwo, \Zend_Log::DEBUG, 'test');
        $this->model->log($messageThree, \Zend_Log::CRIT, 'test');
    }

    public function testLogComplex()
    {
        $this->expectOutputRegex('/Array\s\(\s+\[0\] => 1\s\).+stdClass Object/s');
        $this->model->addStreamLog(\Magento\Framework\Logger::LOGGER_SYSTEM, 'php://output');
        $this->model->log([1]);
        $this->model->log(new \StdClass());
        $this->model->log('key');
    }

    public function testLogNoKey()
    {
        $key = 'key';
        $this->model->log($key);
        $this->assertFalse($this->model->hasLog($key));
    }

    public function testLogDebug()
    {
        $message = uniqid();
        /** @var $model \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMock('Magento\Framework\Logger', ['log'], [], '', false);
        $model->expects($this->at(0))
            ->method('log')
            ->with($message, \Zend_Log::DEBUG, \Magento\Framework\Logger::LOGGER_SYSTEM);
        $model->expects($this->at(1))
            ->method('log')
            ->with(
                $message,
                \Zend_Log::DEBUG,
                \Magento\Framework\Logger::LOGGER_EXCEPTION
            );
        $model->logDebug($message);
        $model->logDebug($message, \Magento\Framework\Logger::LOGGER_EXCEPTION);
    }

    public function testLogException()
    {
        $exception = new \Exception();
        $expected = "\n{$exception}";
        /** @var $model \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMock('Magento\Framework\Logger', ['log'], [], '', false);
        $model->expects($this->at(0))
            ->method('log')
            ->with(
                $expected,
                \Zend_Log::ERR,
                \Magento\Framework\Logger::LOGGER_EXCEPTION
            );
        $model->logException($exception);
    }

    public function testUnsetLoggers()
    {
        $key = 'test';
        $fileOrWrapper = 'custom_file.log';
        $this->model->addStreamLog($key, $fileOrWrapper);
        $this->assertTrue($this->model->hasLog($key));
        $this->model->unsetLoggers();
        $this->assertFalse($this->model->hasLog($key));
    }

    public function testLogFile()
    {
        $message = ['Wrong file name', 'Avoid using special chars'];
        $filename = 'custom_file.log';
        $this->model->logFile($message, \Zend_Log::DEBUG);
        $this->model->logFile($message, \Zend_Log::DEBUG, $filename);
        $this->assertTrue($this->model->hasLog($filename));
    }
}
