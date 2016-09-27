<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache\Tag;

use \Magento\Framework\App\Cache\Tag\Resolver;

class ResolverTest extends \PHPUnit_Framework_TestCase
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
        $this->strategyFactory = $this->getMock(
            \Magento\Framework\App\Cache\Tag\Strategy\Factory::class,
            [],
            [],
            '',
            false
        );

        $this->strategy = $this->getMockForAbstractClass(\Magento\Framework\App\Cache\Tag\StrategyInterface::class);

        $this->strategyFactory->expects($this->any())
            ->method('getStrategy')
            ->willReturn($this->strategy);

        $this->model = new Resolver($this->strategyFactory);
    }

    public function testGetTagsForNotObject()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Provided argument is not an object');
        $this->model->getTags('some scalar');
    }

    public function testGetTagsForObject()
    {
        $strategyReturnValue = ['test tag'];
        $object = new \StdClass;
        $this->strategy->expects($this->once())
            ->method('getTags')
            ->with($object)
            ->willReturn($strategyReturnValue);

        $this->assertEquals($strategyReturnValue, $this->model->getTags($object));
    }
}
