<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver;

class StandardTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Stat|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_stat;

    /**
     * @var \Magento\Framework\Profiler\Driver\Standard
     */
    protected $_driver;

    protected function setUp(): void
    {
        $this->_stat = $this->createMock(\Magento\Framework\Profiler\Driver\Standard\Stat::class);
        $this->_driver = new \Magento\Framework\Profiler\Driver\Standard(['stat' => $this->_stat]);
    }

    protected function tearDown(): void
    {
        \Magento\Framework\Profiler::reset();
    }

    /**
     * Test __construct method with no arguments
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testDefaultConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $driver = new \Magento\Framework\Profiler\Driver\Standard();
        //$this->assertAttributeInstanceOf(\Magento\Framework\Profiler\Driver\Standard\Stat::class, '_stat', $driver);
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testInitOutputs()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $outputFactory = $this->createMock(\Magento\Framework\Profiler\Driver\Standard\Output\Factory::class);
        $config = [
            'outputs' => [
                'outputTypeOne' => ['baseDir' => '/custom/base/dir'],
                'outputTypeTwo' => ['type' => 'specificOutputTypeTwo'],
            ],
            'baseDir' => '/base/dir',
            'outputFactory' => $outputFactory,
        ];

        $outputOne = $this->createMock(\Magento\Framework\Profiler\Driver\Standard\OutputInterface::class);
        $outputTwo = $this->createMock(\Magento\Framework\Profiler\Driver\Standard\OutputInterface::class);

        $outputFactory->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            ['baseDir' => '/custom/base/dir', 'type' => 'outputTypeOne']
        )->willReturn(
            $outputOne
        );

        $outputFactory->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            ['type' => 'specificOutputTypeTwo', 'baseDir' => '/base/dir']
        )->willReturn(
            $outputTwo
        );

        $driver = new \Magento\Framework\Profiler\Driver\Standard($config);
        //$this->assertAttributeCount(2, '_outputs', $driver);
        //$this->assertAttributeEquals([$outputOne, $outputTwo], '_outputs', $driver);
    }

    /**
     * Test display method
     */
    public function testDisplayAndRegisterOutput()
    {
        $outputOne = $this->createMock(\Magento\Framework\Profiler\Driver\Standard\OutputInterface::class);
        $outputOne->expects($this->once())->method('display')->with($this->_stat);
        $outputTwo = $this->createMock(\Magento\Framework\Profiler\Driver\Standard\OutputInterface::class);
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
            \Magento\Framework\Profiler\Driver\Standard\Output\Factory::class,
            $method->invoke($this->_driver)
        );
    }
}
