<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Layout;

/**
 * Class DepersonalizePluginTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DepersonalizePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Layout\DepersonalizePlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\Session\Generic|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\Customer\Model\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitorMock;

    /**
     * @var \Magento\PageCache\Model\DepersonalizeChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $depersonalizeCheckerMock;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->sessionMock = $this->getMock(
            'Magento\Framework\Session\Generic',
            ['clearStorage', 'setData', 'getData'],
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->getMock(
            'Magento\Customer\Model\Session',
            ['getCustomerGroupId', 'setCustomerGroupId', 'clearStorage', 'setCustomer'],
            [],
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            'Magento\Customer\Model\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->customerMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            ['setGroupId', '__wakeup'],
            [],
            '',
            false
        );
        $this->visitorMock = $this->getMock('Magento\Customer\Model\Visitor', [], [], '', false);
        $this->customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->customerMock));
        $this->depersonalizeCheckerMock = $this->getMock(
            'Magento\PageCache\Model\DepersonalizeChecker',
            [],
            [],
            '',
            false
        );

        $this->plugin = new \Magento\Customer\Model\Layout\DepersonalizePlugin(
            $this->depersonalizeCheckerMock,
            $this->sessionMock,
            $this->customerSessionMock,
            $this->customerFactoryMock,
            $this->visitorMock
        );
    }

    public function testBeforeGenerateXml()
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId');
        $this->sessionMock
            ->expects($this->once())
            ->method('getData')
            ->with($this->equalTo(\Magento\Framework\Data\Form\FormKey::FORM_KEY));
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    public function testBeforeGenerateXmlNoDepersonalize()
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->customerSessionMock->expects($this->never())->method('getCustomerGroupId');
        $this->sessionMock
            ->expects($this->never())
            ->method('getData');
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    public function testAfterGenerateXml()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->visitorMock->expects($this->once())->method('setSkipRequestLogging')->with($this->equalTo(true));
        $this->visitorMock->expects($this->once())->method('unsetData');
        $this->sessionMock->expects($this->once())->method('clearStorage');
        $this->customerSessionMock->expects($this->once())->method('clearStorage');
        $this->customerSessionMock->expects($this->once())->method('setCustomerGroupId')->with($this->equalTo(null));
        $this->customerMock->expects($this->once())->method('setGroupId')->with($this->equalTo(null))->willReturnSelf();
        $this->sessionMock
            ->expects($this->once())
            ->method('setData')
            ->with(
                $this->equalTo(\Magento\Framework\Data\Form\FormKey::FORM_KEY),
                $this->equalTo(null)
            );
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomer')
            ->with($this->equalTo($this->customerMock));
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testAfterGenerateXmlNoDepersonalize()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->visitorMock->expects($this->never())->method('setSkipRequestLogging');
        $this->visitorMock->expects($this->never())->method('unsetData');
        $this->sessionMock->expects($this->never())->method('clearStorage');
        $this->customerSessionMock->expects($this->never())->method('clearStorage');
        $this->customerSessionMock->expects($this->never())->method('setCustomerGroupId');
        $this->customerMock->expects($this->never())->method('setGroupId');
        $this->sessionMock->expects($this->never())->method('setData');
        $this->customerSessionMock->expects($this->never())->method('setCustomer');
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertSame($expectedResult, $actualResult);
    }
}
