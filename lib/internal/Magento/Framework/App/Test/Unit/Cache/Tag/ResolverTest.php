<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache\Tag;

use \Magento\Framework\App\Cache\Tag\Resolver;

class ResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Tag\Strategy\Factory
     */
    private $strategyFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\Tag\StrategyInterface
     */
    private $strategy;

    /**
     * @var Resolver
     */
    private $model;

    protected function setUp()
    {
        $this->strategyFactory = $this->createMock(\Magento\Framework\App\Cache\Tag\Strategy\Factory::class);

        $this->strategy = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\Tag\StrategyInterface::class);

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
        $object = new \stdClass;
        $this->strategy->expects($this->once())
            ->method('getTags')
            ->with($object)
            ->willReturn($strategyReturnValue);

        $this->assertEquals($strategyReturnValue, $this->model->getTags($object));
    }
}
