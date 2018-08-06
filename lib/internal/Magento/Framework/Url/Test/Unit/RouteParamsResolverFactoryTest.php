<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RouteParamsResolverFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Url\RouteParamsResolverFactory */
    protected $object;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Framework\Url\RouteParamsResolverFactory::class,
            ['objectManager' => $this->objectManager]
        );
    }

    public function testCreate()
    {
        $producedInstance = $this->createMock(\Magento\Framework\Url\RouteParamsResolverInterface::class);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Url\RouteParamsResolverInterface::class)
            ->will($this->returnValue($producedInstance));

        $this->assertSame($producedInstance, $this->object->create([]));
    }
}
