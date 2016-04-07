<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver;

class StandardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Stat|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stat;

    /**
     * @var \Magento\Framework\Profiler\Driver\Standard
     */
    protected $_driver;

    protected function setUp()
    {
        $this->_stat = $this->getMock('Magento\Framework\Profiler\Driver\Standard\Stat');
        $this->_driver = new \Magento\Framework\Profiler\Driver\Standard(['stat' => $this->_stat]);
    }

    protected function tearDown()
    {
        \Magento\Framework\Profiler::reset();
    }

    /**
     * Test __construct method with no arguments
     */
    public function testDefaultConstructor()
    {
        $driver = new \Magento\Framework\Profiler\Driver\Standard();
        $this->assertAttributeInstanceOf('Magento\Framework\Profiler\Driver\Standard\Stat', '_stat', $driver);
    }

    /**
     * Test clear method
     */
    public function testClear()
    {
        $this->_stat->expects($this->once())->method('clear')->with('timer_id');
        $this->_driver->clear('timer_id');
    }

    /**
     * Test start method
     */
    public function testStart()
    {
        $this->_stat->expects(
            $this->once()
        )->method(
            'start'
        )->with(
            'timer_id',
            $this->greaterThanOrEqual(microtime(true)),
            $this->greaterThanOrEqual(0),
            $this->greaterThanOrEqual(0)
        );
        $this->_driver->start('timer_id');
    }

    /**
     * Test stop method
     */
    public function testStop()
    {
        $this->_stat->expects(
            $this->once()
        )->method(
            'stop'
        )->with(
            'timer_id',
            $this->greaterThanOrEqual(microtime(true)),
            $this->greaterThanOrEqual(0),
            $this->greaterThanOrEqual(0)
        );
        $this->_driver->stop('timer_id');
    }

    /**
     * Test _initOutputs method
     */
    public function testInitOutputs()
    {
        $outputFactory = $this->getMock('Magento\Framework\Profiler\Driver\Standard\Output\Factory');
        $config = [
            'outputs' => [
                'outputTypeOne' => ['baseDir' => '/custom/base/dir'],
                'outputTypeTwo' => ['type' => 'specificOutputTypeTwo'],
            ],
            'baseDir' => '/base/dir',
            'outputFactory' => $outputFactory,
        ];

        $outputOne = $this->getMock('Magento\Framework\Profiler\Driver\Standard\OutputInterface');
        $outputTwo = $this->getMock('Magento\Framework\Profiler\Driver\Standard\OutputInterface');

        $outputFactory->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            ['baseDir' => '/custom/base/dir', 'type' => 'outputTypeOne']
        )->will(
            $this->returnValue($outputOne)
        );

        $outputFactory->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            ['type' => 'specificOutputTypeTwo', 'baseDir' => '/base/dir']
        )->will(
            $this->returnValue($outputTwo)
        );

        $driver = new \Magento\Framework\Profiler\Driver\Standard($config);
        $this->assertAttributeCount(2, '_outputs', $driver);
        $this->assertAttributeEquals([$outputOne, $outputTwo], '_outputs', $driver);
    }

    /**
     * Test display method
     */
    public function testDisplayAndRegisterOutput()
    {
        $outputOne = $this->getMock('Magento\Framework\Profiler\Driver\Standard\OutputInterface');
        $outputOne->expects($this->once())->method('display')->with($this->_stat);
        $outputTwo = $this->getMock('Magento\Framework\Profiler\Driver\Standard\OutputInterface');
        $outputTwo->expects($this->once())->method('display')->with($this->_stat);

        $this->_driver->registerOutput($outputOne);
        $this->_driver->registerOutput($outputTwo);
        \Magento\Framework\Profiler::enable();
        $this->_driver->display();
        \Magento\Framework\Profiler::disable();
        $this->_driver->display();
    }

    /**
     * Test _getOutputFactory method creating new object by default
     */
    public function testDefaultOutputFactory()
    {
        $method = new \ReflectionMethod($this->_driver, '_getOutputFactory');
        $method->setAccessible(true);
        $this->assertInstanceOf(
            'Magento\Framework\Profiler\Driver\Standard\Output\Factory',
            $method->invoke($this->_driver)
        );
    }
}
