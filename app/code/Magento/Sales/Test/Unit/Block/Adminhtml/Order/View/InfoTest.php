<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class InfoTests
 */
class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\View\Info
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    protected function setUp()
    {
        $this->contextMock
            = $this->getMock(\Magento\Backend\Block\Template\Context::class, ['getAuthorization'], [], '', false);
        $this->authorizationMock = $this->getMock(\Magento\Framework\AuthorizationInterface::class, [], [], '', false);
        $this->contextMock
            ->expects($this->any())->method('getAuthorization')->will($this->returnValue($this->authorizationMock));
        $this->groupRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\GroupRepositoryInterface::class
        );
        $this->coreRegistryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $methods = ['getCustomerGroupId', '__wakeUp'];
        $this->orderMock = $this->getMock(\Magento\Sales\Model\Order::class, $methods, [], '', false);
        $this->groupMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupInterface::class,
            [],
            '',
            false
        );
        $arguments = [
            'context' => $this->contextMock,
            'groupRepository' => $this->groupRepositoryMock,
            'registry' => $this->coreRegistryMock,
        ];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $block */
        $this->block = $helper->getObject(\Magento\Sales\Block\Adminhtml\Order\View\Info::class, $arguments);
    }

    public function testGetAddressEditLink()
    {
        $contextMock = $this->getMock(
            \Magento\Backend\Block\Template\Context::class,
            ['getAuthorization'],
            [],
            '',
            false
        );
        $authorizationMock = $this->getMock(\Magento\Framework\AuthorizationInterface::class, [], [], '', false);
        $contextMock->expects($this->any())->method('getAuthorization')->will($this->returnValue($authorizationMock));
        $arguments = ['context' => $contextMock];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $block */
        $block = $helper->getObject(\Magento\Sales\Block\Adminhtml\Order\View\Info::class, $arguments);

        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Sales::actions_edit')
            ->will($this->returnValue(false));

        $address = new \Magento\Framework\DataObject();
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
