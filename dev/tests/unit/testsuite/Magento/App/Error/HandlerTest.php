<?php
/**
 * Unit Test for \Magento\App\Error\Handler
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
 */
namespace Magento\App\Error;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Logger mock
     *
     * @var \Magento\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * Filesystem mock
     *
     * @var \Magento\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * App state mock
     *
     * @var  \Magento\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    /**
     * Handler instance
     *
     * @var \Magento\App\Error\Handler
     */
    protected $handler;

    protected function setUp()
    {
        $this->logger = $this->getMock('Magento\Logger', array(), array(), '', false);
        $this->filesystem = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $this->appState = $this->getMock('Magento\App\State', array(), array(), '', false);
        $this->handler = new \Magento\App\Error\Handler($this->logger, $this->filesystem, $this->appState);
    }

    /**
     * Test for processException method print
     *
     * @covers \Magento\Error\Handler::processException
     */
    public function testProcessExceptionPrint()
    {
        $this->appState->expects(
            $this->any()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\App\State::MODE_DEVELOPER)
        );
        $exception = new \Exception('TestMessage');

        ob_start();
        $this->handler->processException($exception);
        $actualResult = ob_get_contents();
        ob_end_clean();
        $this->assertRegExp('/TestMessage/', $actualResult);
    }

    /**
     * Test for processException method report
     *
     * @covers \Magento\Error\Handler::processException
     * @runInSeparateProcess
     */
    public function testProcessExceptionReport()
    {
        $this->appState->expects(
            $this->any()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\App\State::MODE_DEFAULT)
        );
        $this->filesystem->expects(
            $this->atLeastOnce()
        )->method(
            'getPath'
        )->with(
            \Magento\App\Filesystem::PUB_DIR
        )->will(
            $this->returnValue(dirname(__DIR__) . '/../_files')
        );

        $exception = new \Exception('TestMessage');
        $this->handler->processException($exception);
    }

    /**
     * Test for setting error handler and logging
     *
     * @covers \Magento\Error\Handler::handler
     * @throws \Exception
     */
    public function testErrorHandlerLogging()
    {
        $this->appState->expects(
            $this->any()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\App\State::MODE_DEFAULT)
        );
        $this->logger->expects(
            $this->once()
        )->method(
            'log'
        )->with(
            $this->stringContains('testErrorHandlerLogging'),
            \Zend_Log::ERR
        );
        set_error_handler(array($this->handler, 'handler'));
        try {
            trigger_error('testErrorHandlerLogging', E_USER_NOTICE);
            restore_error_handler();
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }
    }

    /**
     * Test for setting error handler and printing
     *
     * @covers \Magento\Error\Handler::handler
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testErrorHandlerPrint()
    {
        $this->appState->expects(
            $this->any()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\App\State::MODE_DEVELOPER)
        );
        set_error_handler(array($this->handler, 'handler'));
        try {
            trigger_error('testErrorHandlerPrint', E_USER_NOTICE);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }
    }
}
