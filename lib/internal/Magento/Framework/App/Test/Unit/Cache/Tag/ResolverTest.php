<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Tag;

use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\Cache\Tag\Strategy\Factory;
use Magento\Framework\App\Cache\Tag\StrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResolverTest extends TestCase
{
    /**
     * @var MockObject|Factory
     */
    private $strategyFactory;

    /**
     * @var MockObject|StrategyInterface
     */
    private $strategy;

    /**
     * @var Resolver
     */
    private $model;

    protected function setUp(): void
    {
        $this->strategyFactory = $this->createMock(Factory::class);

        $this->strategy = $this->getMockForAbstractClass(StrategyInterface::class);

        $this->strategyFactory->expects($this->any())
            ->method('getStrategy')
            ->willReturn($this->strategy);

        $this->model = new Resolver($this->strategyFactory);
    }

    public function testGetTagsForNotObject()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided argument is not an object');
        $this->model->getTags('some scalar');
    }

    public function testGetTagsForObject()
    {
        $strategyReturnValue = ['test tag'];
        $object = new \stdClass();
        $this->strategy->expects($this->once())
            ->method('getTags')
            ->with($object)
            ->willReturn($strategyReturnValue);

        $this->assertEquals($strategyReturnValue, $this->model->getTags($object));
    }
}
