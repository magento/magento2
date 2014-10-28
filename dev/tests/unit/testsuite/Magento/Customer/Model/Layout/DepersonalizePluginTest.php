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
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
        $this->sessionMock = $this->getMock(
            'Magento\Framework\Session\Generic',
            array('clearStorage', 'setData', 'getData'),
            array(),
            '',
            false
        );
        $this->customerSessionMock = $this->getMock(
            'Magento\Customer\Model\Session',
            array('getCustomerGroupId', 'setCustomerGroupId', 'clearStorage', 'setCustomer'),
            array(),
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            'Magento\Customer\Model\CustomerFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->customerMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            array('setGroupId', '__wakeup'),
            array(),
            '',
            false
        );
        $this->moduleManagerMock = $this->getMock('Magento\Framework\Module\Manager', array(), array(), '', false);
        $this->visitorMock = $this->getMock('Magento\Customer\Model\Visitor', array(), array(), '', false);
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerMock));
        $this->cacheConfigMock = $this->getMock('Magento\PageCache\Model\Config', array(), array(), '', false);

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
        $this->assertEquals(array(), $output);
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
        $this->assertEquals(array(), $output);
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
        $this->assertEquals(array(), $output);
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
        $this->assertEquals(array(), $output);
    }

    /**
     * Test method afterGenerateXml with enabled module PageCache
     */
    public function testAfterGenerateXmlPageCacheEnabled()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
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
            ->with($this->equalTo(null));
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
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
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
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
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
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
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
