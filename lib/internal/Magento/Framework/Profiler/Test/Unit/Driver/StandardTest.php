<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Profiler\Test\Unit\Driver;

use Magento\Framework\Profiler;
use Magento\Framework\Profiler\Driver\Standard;
use Magento\Framework\Profiler\Driver\Standard\Output\Factory;
use Magento\Framework\Profiler\Driver\Standard\OutputInterface;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class StandardTest extends TestCase
{
    /**
     * @var Stat|MockObject
     */
    private $stat;

    /**
     * @var Standard
     */
    private $driver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->stat = $this->createMock(Stat::class);
        $this->driver = new Standard(['stat' => $this->stat]);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        Profiler::reset();
    }

    /**
     * Test __construct method with no arguments.
     *
     * @return void
     */
    public function testDefaultConstructor(): void
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $driver = new Standard();
        $this->assertAttributeInstanceOf(Stat::class, '_stat', $driver);
    }

    /**
     * Test clear method.
     *
     * @return void
     */
    public function testClear(): void
    {
        $this->stat->expects($this->once())->method('clear')->with('timer_id');
        $this->driver->clear('timer_id');
    }

    /**
     * Test start method.
     *
     * @return void
     */
    public function testStart(): void
    {
        $this->stat->expects(
            $this->once()
        )->method(
            'start'
        )->with(
            'timer_id',
            $this->greaterThanOrEqual(microtime(true)),
            $this->greaterThanOrEqual(0),
            $this->greaterThanOrEqual(0)
        );
        $this->driver->start('timer_id');
    }

    /**
     * Test stop method.
     *
     * @return void
     */
    public function testStop(): void
    {
        $this->stat->expects(
            $this->once()
        )->method(
            'stop'
        )->with(
            'timer_id',
            $this->greaterThanOrEqual(microtime(true)),
            $this->greaterThanOrEqual(0),
            $this->greaterThanOrEqual(0)
        );
        $this->driver->stop('timer_id');
    }

    /**
     * Test _initOutputs method.
     *
     * @return void
     */
    public function testInitOutputs(): void
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $outputFactory = $this->createMock(Factory::class);
        $config = [
            'outputs' => [
                'outputTypeOne' => ['baseDir' => '/custom/base/dir'],
                'outputTypeTwo' => ['type' => 'specificOutputTypeTwo']
            ],
            'baseDir' => '/base/dir',
            'outputFactory' => $outputFactory
        ];

        $outputOne = $this->getMockForAbstractClass(OutputInterface::class);
        $outputTwo = $this->getMockForAbstractClass(OutputInterface::class);

        $outputFactory->method('create')
            ->willReturnCallback(
                function ($arg1) use ($outputOne, $outputTwo) {
                    if ($arg1['baseDir'] == '/custom/base/dir' && $arg1['type'] == 'outputTypeOne') {
                        return $outputOne;
                    } elseif ($arg1['type'] == 'specificOutputTypeTwo' && $arg1['baseDir'] == '/base/dir') {
                        return $outputTwo;
                    }
                }
            );

        $driver = new Standard($config);
        $this->assertAttributeCount(2, '_outputs', $driver);
        $this->assertAttributeEquals([$outputOne, $outputTwo], '_outputs', $driver);
    }

    /**
     * Test display method.
     *
     * @return void
     */
    public function testDisplayAndRegisterOutput(): void
    {
        $outputOne = $this->getMockForAbstractClass(OutputInterface::class);
        $outputOne->expects($this->once())->method('display')->with($this->stat);
        $outputTwo = $this->getMockForAbstractClass(OutputInterface::class);
        $outputTwo->expects($this->once())->method('display')->with($this->stat);

        $this->driver->registerOutput($outputOne);
        $this->driver->registerOutput($outputTwo);
        Profiler::enable();
        $this->driver->display();
        Profiler::disable();
        $this->driver->display();
    }

    /**
     * Test _getOutputFactory method creating new object by default.
     *
     * @return void
     */
    public function testDefaultOutputFactory(): void
    {
        $method = new ReflectionMethod($this->driver, '_getOutputFactory');
        $method->setAccessible(true);
        $this->assertInstanceOf(
            Factory::class,
            $method->invoke($this->driver)
        );
    }
}
