<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->authorizationMock = $this->createMock(AuthorizationInterface::class);
        $this->contextMock
            ->expects($this->any())->method('getAuthorization')->will($this->returnValue($this->authorizationMock));
        $this->groupRepositoryMock = $this->getMockForAbstractClass(
            GroupRepositoryInterface::class
        );
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $methods = ['getCustomerGroupId', '__wakeUp'];
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
        $authorizationMock = $this->createMock(AuthorizationInterface::class);
        $contextMock->expects($this->any())->method('getAuthorization')->will($this->returnValue($authorizationMock));
        $arguments = ['context' => $contextMock];

        $helper = new ObjectManager($this);
        /** @var Info $block */
        $block = $helper->getObject(Info::class, $arguments);

        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Sales::actions_edit')
            ->will($this->returnValue(false));

        $address = new DataObject();
        $this->assertEmpty($block->getAddressEditLink($address));
    }

    public function testGetCustomerGroupNameWhenGroupIsNotExist()
    {
        $this->coreRegistryMock
            ->expects($this->any())
            ->method('registry')
            ->with('current_order')
            ->will($this->returnValue($this->orderMock));
        $this->orderMock->expects($this->once())->method('getCustomerGroupId')->will($this->returnValue(4));
        $this->groupRepositoryMock
            ->expects($this->once())->method('getById')->with(4)->will($this->returnValue($this->groupMock));
        $this->groupMock
            ->expects($this->once())
            ->method('getCode')
            ->will($this->throwException(new NoSuchEntityException()));
        $this->assertEquals('', $this->block->getCustomerGroupName());
    }

    public function testGetCustomerGroupNameWhenGroupExists()
    {
        $this->coreRegistryMock
            ->expects($this->any())
            ->method('registry')
            ->with('current_order')
            ->will($this->returnValue($this->orderMock));
        $this->orderMock->expects($this->once())->method('getCustomerGroupId')->will($this->returnValue(4));
        $this->groupRepositoryMock
            ->expects($this->once())->method('getById')->with(4)->will($this->returnValue($this->groupMock));
        $this->groupMock
            ->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('group_code'));
        $this->assertEquals('group_code', $this->block->getCustomerGroupName());
    }
}
