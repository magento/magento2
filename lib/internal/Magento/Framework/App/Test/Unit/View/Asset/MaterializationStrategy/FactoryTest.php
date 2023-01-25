<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\View\Asset\MaterializationStrategy;

use Magento\Framework\App\View\Asset\MaterializationStrategy\Copy;
use Magento\Framework\App\View\Asset\MaterializationStrategy\Factory;
use Magento\Framework\App\View\Asset\MaterializationStrategy\StrategyInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\LocalInterface;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();
    }

    public function testCreateEmptyStrategies()
    {
        $asset = $this->getAsset();
        $copyStrategy = $this->getMockBuilder(Copy::class)
            ->setMethods([])
            ->getMock();
        $copyStrategy->expects($this->once())
            ->method('isSupported')
            ->with($asset)
            ->willReturn(true);

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(Factory::DEFAULT_STRATEGY)
            ->willReturn($copyStrategy);

        $factory = new Factory($this->objectManager, []);
        $this->assertSame($copyStrategy, $factory->create($asset));
    }

    public function testCreateSupported()
    {
        $asset = $this->getAsset();
        $copyStrategy = $this->getMockBuilder(Copy::class)
            ->setMethods([])
            ->getMock();
        $copyStrategy->expects($this->once())
            ->method('isSupported')
            ->with($asset)
            ->willReturn(false);

        $supportedStrategy = $this->getMockBuilder(
            StrategyInterface::class
        )
            ->setMethods([])
            ->getMock();
        $supportedStrategy->expects($this->once())
            ->method('isSupported')
            ->with($asset)
            ->willReturn(true);

        $factory = new Factory($this->objectManager, [$copyStrategy, $supportedStrategy]);
        $this->assertSame($supportedStrategy, $factory->create($asset));
    }

    public function testCreateException()
    {
        $asset = $this->getAsset();
        $copyStrategy = $this->getMockBuilder(Copy::class)
            ->setMethods([])
            ->getMock();
        $copyStrategy->expects($this->once())
            ->method('isSupported')
            ->with($asset)
            ->willReturn(false);

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(Factory::DEFAULT_STRATEGY)
            ->willReturn($copyStrategy);

        $factory = new Factory($this->objectManager, []);

        $this->expectException('LogicException');
        $this->expectExceptionMessage('No materialization strategy is supported');
        $factory->create($asset);
    }

    /**
     * @return LocalInterface|MockObject
     */
    private function getAsset()
    {
        return $this->getMockBuilder(LocalInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();
    }
}
