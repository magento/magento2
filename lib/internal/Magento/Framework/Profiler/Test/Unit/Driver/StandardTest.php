<?php declare(strict_types=1);
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver;

use Magento\Framework\Profiler;
use Magento\Framework\Profiler\Driver\Standard;
use Magento\Framework\Profiler\Driver\Standard\Output\Factory;
use Magento\Framework\Profiler\Driver\Standard\OutputInterface;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StandardTest extends TestCase
{
    /**
     * @var Stat|MockObject
     */
    protected $_stat;

    /**
     * @var Standard
     */
    protected $_driver;

    protected function setUp(): void
    {
        $this->_stat = $this->createMock(Stat::class);
        $this->_driver = new Standard(['stat' => $this->_stat]);
    }

    protected function tearDown(): void
    {
        Profiler::reset();
    }

    /**
     * Test __construct method with no arguments
     */
    public function testDefaultConstructor()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $driver = new Standard();
        $this->assertAttributeInstanceOf(Stat::class, '_stat', $driver);
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
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $outputFactory = $this->createMock(Factory::class);
        $config = [
            'outputs' => [
                'outputTypeOne' => ['baseDir' => '/custom/base/dir'],
                'outputTypeTwo' => ['type' => 'specificOutputTypeTwo'],
            ],
            'baseDir' => '/base/dir',
            'outputFactory' => $outputFactory,
        ];

        $outputOne = $this->getMockForAbstractClass(OutputInterface::class);
        $outputTwo = $this->getMockForAbstractClass(OutputInterface::class);

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

        $driver = new Standard($config);
        $this->assertAttributeCount(2, '_outputs', $driver);
        $this->assertAttributeEquals([$outputOne, $outputTwo], '_outputs', $driver);
    }

    /**
     * Test display method
     */
    public function testDisplayAndRegisterOutput()
    {
        $outputOne = $this->getMockForAbstractClass(OutputInterface::class);
        $outputOne->expects($this->once())->method('display')->with($this->_stat);
        $outputTwo = $this->getMockForAbstractClass(OutputInterface::class);
        $outputTwo->expects($this->once())->method('display')->with($this->_stat);

        $this->_driver->registerOutput($outputOne);
        $this->_driver->registerOutput($outputTwo);
        Profiler::enable();
        $this->_driver->display();
        Profiler::disable();
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
            Factory::class,
            $method->invoke($this->_driver)
        );
    }
}
