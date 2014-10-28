<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Order\View;

use Magento\Framework\Exception\NoSuchEntityException;

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
    protected $groupServiceMock;

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
            = $this->getMock('Magento\Backend\Block\Template\Context', ['getAuthorization'], [], '', false);
        $this->authorizationMock = $this->getMock('Magento\Framework\AuthorizationInterface', [], [], '', false);
        $this->contextMock
            ->expects($this->any())->method('getAuthorization')->will($this->returnValue($this->authorizationMock));
        $this->groupServiceMock = $this->getMock('Magento\Customer\Service\V1\CustomerGroupServiceInterface');
        $this->coreRegistryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $methods = ['getCustomerGroupId', '__wakeUp'];
        $this->orderMock = $this->getMock('\Magento\Sales\Model\Order', $methods, [], '', false);
        $this->groupMock = $this->getMock('Magento\Customer\Service\V1\Data\CustomerGroup', [], [], '', false);
        $arguments = [
            'context' => $this->contextMock,
            'groupService' => $this->groupServiceMock,
            'registry' => $this->coreRegistryMock
        ];

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $block */
        $this->block = $helper->getObject('Magento\Sales\Block\Adminhtml\Order\View\Info', $arguments);
    }

    public function testGetAddressEditLink()
    {
        $contextMock = $this->getMock('Magento\Backend\Block\Template\Context', ['getAuthorization'], [], '', false);
        $authorizationMock = $this->getMock('Magento\Framework\AuthorizationInterface', [], [], '', false);
        $contextMock->expects($this->any())->method('getAuthorization')->will($this->returnValue($authorizationMock));
        $arguments = ['context' => $contextMock];

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $block */
        $block = $helper->getObject('Magento\Sales\Block\Adminhtml\Order\View\Info', $arguments);

        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Sales::actions_edit')
            ->will($this->returnValue(false));

        $address = new \Magento\Framework\Object();
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
        $this->groupServiceMock
            ->expects($this->once())->method('getGroup')->with(4)->will($this->returnValue($this->groupMock));
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
        $this->groupServiceMock
            ->expects($this->once())->method('getGroup')->with(4)->will($this->returnValue($this->groupMock));
        $this->groupMock
            ->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('group_code'));
        $this->assertEquals('group_code', $this->block->getCustomerGroupName());
    }
}
