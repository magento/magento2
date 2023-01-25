<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\Mview\View\SubscriptionFactory;
use Magento\Framework\Mview\View\SubscriptionInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubscriptionFactoryTest extends TestCase
{
    /**
     * @var SubscriptionFactory|MockObject
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = new SubscriptionFactory($this->objectManagerMock);
    }

    public function testCreate()
    {
        $subscriptionInterfaceMock = $this->getMockForAbstractClass(
            SubscriptionInterface::class,
            [],
            '',
            false
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(SubscriptionInterface::class, ['some_data'])
            ->willReturn($subscriptionInterfaceMock);
        $this->assertEquals($subscriptionInterfaceMock, $this->model->create(['some_data']));
    }
}
