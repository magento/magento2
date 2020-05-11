<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    /**
     * @var Info
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $authorizationMock;

    /**
     * @var MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var MockObject
     */
    protected $orderMock;

    /**
     * @var MockObject
     */
    protected $groupMock;

    /**
     * @var MockObject
     */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->contextMock
            = $this->createPartialMock(Context::class, ['getAuthorization']);
        $this->authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->contextMock
            ->expects($this->any())->method('getAuthorization')->willReturn($this->authorizationMock);
        $this->groupRepositoryMock = $this->getMockForAbstractClass(
            GroupRepositoryInterface::class
        );
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $methods = ['getCustomerGroupId'];
        $this->orderMock = $this->createPartialMock(Order::class, $methods);
        $this->groupMock = $this->getMockForAbstractClass(
            GroupInterface::class,
            [],
            '',
            false
        );
        $arguments = [
            'context' => $this->contextMock,
            'groupRepository' => $this->groupRepositoryMock,
            'registry' => $this->coreRegistryMock,
        ];

        $helper = new ObjectManager($this);
        /** @var Info $block */
        $this->block = $helper->getObject(Info::class, $arguments);
    }

    public function testGetAddressEditLink()
    {
        $contextMock = $this->createPartialMock(Context::class, ['getAuthorization']);
        $authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $contextMock->expects($this->any())->method('getAuthorization')->willReturn($authorizationMock);
        $arguments = ['context' => $contextMock];

        $helper = new ObjectManager($this);
        /** @var Info $block */
        $block = $helper->getObject(Info::class, $arguments);

        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Sales::actions_edit')
            ->willReturn(false);

        $address = new DataObject();
        $this->assertEmpty($block->getAddressEditLink($address));
    }

    public function testGetCustomerGroupNameWhenGroupIsNotExist()
    {
        $this->coreRegistryMock
            ->expects($this->any())
            ->method('registry')
            ->with('current_order')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getCustomerGroupId')->willReturn(4);
        $this->groupRepositoryMock
            ->expects($this->once())->method('getById')->with(4)->willReturn($this->groupMock);
        $this->groupMock
            ->expects($this->once())
            ->method('getCode')
            ->willThrowException(new NoSuchEntityException());
        $this->assertEquals('', $this->block->getCustomerGroupName());
    }

    public function testGetCustomerGroupNameWhenGroupExists()
    {
        $this->coreRegistryMock
            ->expects($this->any())
            ->method('registry')
            ->with('current_order')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getCustomerGroupId')->willReturn(4);
        $this->groupRepositoryMock
            ->expects($this->once())->method('getById')->with(4)->willReturn($this->groupMock);
        $this->groupMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn('group_code');
        $this->assertEquals('group_code', $this->block->getCustomerGroupName());
    }
}
