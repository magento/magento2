<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Bootstrap\Profiler.
 */
namespace Magento\Test\Bootstrap;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap\Profiler
     */
    protected $_object;

    /**
     * @var \Magento\Framework\Profiler\Driver\Standard|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_driver;

    protected function setUp()
    {
        $this->expectOutputString('');
        $this->_driver = $this->getMock('Magento\Framework\Profiler\Driver\Standard', ['registerOutput']);
        $this->_object = new \Magento\TestFramework\Bootstrap\Profiler($this->_driver);
    }

    protected function tearDown()
    {
        $this->_driver = null;
        $this->_object = null;
    }

    public function testRegisterFileProfiler()
    {
        $this->_driver->expects(
            $this->once()
        )->method(
            'registerOutput'
        )->with(
            $this->isInstanceOf('Magento\Framework\Profiler\Driver\Standard\Output\Csvfile')
        );
        $this->_object->registerFileProfiler('php://output');
    }

    public function testRegisterBambooProfiler()
    {
        $this->_driver->expects(
            $this->once()
        )->method(
            'registerOutput'
        )->with(
            $this->isInstanceOf('Magento\TestFramework\Profiler\OutputBamboo')
        );
        $this->_object->registerBambooProfiler('php://output', __DIR__ . '/_files/metrics.php');
    }
}
