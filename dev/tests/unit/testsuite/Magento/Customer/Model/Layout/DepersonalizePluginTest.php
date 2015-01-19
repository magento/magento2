<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Layout;

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
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\Log\Model\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitorMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheConfigMock;
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
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->customerMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            ['setGroupId', '__wakeup'],
            [],
            '',
            false
        );
        $this->moduleManagerMock = $this->getMock('Magento\Framework\Module\Manager', [], [], '', false);
        $this->visitorMock = $this->getMock('Magento\Customer\Model\Visitor', [], [], '', false);
        $this->customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->customerMock));
        $this->cacheConfigMock = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);

        $this->plugin = new DepersonalizePlugin(
            $this->sessionMock,
            $this->customerSessionMock,
            $this->customerFactoryMock,
            $this->requestMock,
            $this->moduleManagerMock,
            $this->visitorMock,
            $this->cacheConfigMock
        );
    }

    /**
     * Test method beforeGenerateXml with enabled module PageCache
     */
    public function testBeforeGenerateXmlPageCacheEnabled()
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->will($this->returnValue(true));
        $this->cacheConfigMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock
            ->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock
            ->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId');
        $this->sessionMock->expects($this->once())
            ->method('getData')
            ->with($this->equalTo(\Magento\Framework\Data\Form\FormKey::FORM_KEY));
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    /**
     * Test method beforeGenerateXml with disabled module PageCache
     */
    public function testBeforeGenerateXmlPageCacheDisabled()
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->will($this->returnValue(false));
        $this->requestMock
            ->expects($this->never())
            ->method('isAjax');
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    /**
     * Test beforeGenerateXml method with enabled module PageCache and request is Ajax
     */
    public function testBeforeGenerateXmlRequestIsAjax()
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->will($this->returnValue(true));
        $this->cacheConfigMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock
            ->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(true));
        $this->layoutMock->expects($this->never())
            ->method('isCacheable');
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    /**
     * Test beforeGenerateXml method with enabled module PageCache and request is Ajax and Layout is not cacheable
     */
    public function testBeforeGenerateXmlLayoutIsNotCacheable()
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->will($this->returnValue(true));
        $this->cacheConfigMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock
            ->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(false));
        $this->customerSessionMock->expects($this->never())
            ->method('getCustomerGroupId');
        $output = $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEquals([], $output);
    }

    /**
     * Test method afterGenerateXml with enabled module PageCache
     */
    public function testAfterGenerateXmlPageCacheEnabled()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->will($this->returnValue(true));
        $this->cacheConfigMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock
            ->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock
            ->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));
        $this->visitorMock
            ->expects($this->once())
            ->method('setSkipRequestLogging')
            ->with($this->equalTo(true));
        $this->visitorMock
            ->expects($this->once())
            ->method('unsetData');
        $this->sessionMock
            ->expects($this->once())
            ->method('clearStorage');
        $this->customerSessionMock
            ->expects($this->once())
            ->method('clearStorage');
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($this->equalTo(null));
        $this->customerMock
            ->expects($this->once())
            ->method('setGroupId')
            ->with($this->equalTo(null))
            ->willReturnSelf();
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

    /**
     * Test afterGenerateXml method with disabled module PageCache
     */
    public function testAfterGenerateXmlPageCacheDisabled()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->will($this->returnValue(false));
        $this->requestMock
            ->expects($this->never())
            ->method('isAjax');
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Test afterGenerateXml method with enabled module PageCache and request is Ajax
     */
    public function testAfterGenerateXmlRequestIsAjax()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->cacheConfigMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock
            ->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(true));
        $this->layoutMock->expects($this->never())
            ->method('isCacheable');
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Test afterGenerateXml method with enabled module PageCache and request is Ajax and Layout is not cacheable
     */
    public function testAfterGenerateXmlLayoutIsNotCacheable()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->cacheConfigMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock
            ->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(false));
        $this->visitorMock
            ->expects($this->never())
            ->method('setSkipRequestLogging');
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertSame($expectedResult, $actualResult);
    }
}
