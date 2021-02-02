<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\Test\Unit\View;

use \Magento\Framework\Mview\View\SubscriptionFactory;

class SubscriptionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mview\View\SubscriptionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new SubscriptionFactory($this->objectManagerMock);
    }

    public function testCreate()
    {
        $subscriptionInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\View\SubscriptionInterface::class,
            [],
            '',
            false
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Mview\View\SubscriptionInterface::class, ['some_data'])
            ->willReturn($subscriptionInterfaceMock);
        $this->assertEquals($subscriptionInterfaceMock, $this->model->create(['some_data']));
    }
}
