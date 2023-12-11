<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\RouteParamsResolverFactory;
use Magento\Framework\Url\RouteParamsResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouteParamsResolverFactoryTest extends TestCase
{
    /** @var RouteParamsResolverFactory */
    protected $object;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            RouteParamsResolverFactory::class,
            ['objectManager' => $this->objectManager]
        );
    }

    public function testCreate()
    {
        $producedInstance = $this->getMockForAbstractClass(RouteParamsResolverInterface::class);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(RouteParamsResolverInterface::class)
            ->willReturn($producedInstance);

        $this->assertSame($producedInstance, $this->object->create([]));
    }
}
