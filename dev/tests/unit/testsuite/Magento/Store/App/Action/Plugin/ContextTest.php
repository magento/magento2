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

namespace Magento\Store\App\Action\Plugin;

/**
 * Class ContextPluginTest
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\App\Action\Plugin\Context
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\Http\Context $httpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContextMock;

    /**
     * @var \Magento\Framework\App\Request\Http $httpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpRequestMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;


    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->sessionMock = $this->getMock('Magento\Framework\Session\Generic',
            ['getCurrencyCode'], [], '', false);
        $this->httpContextMock = $this->getMock('Magento\Framework\App\Http\Context',
            [], [], '', false);
        $this->httpRequestMock = $this->getMock('Magento\Framework\App\Request\Http',
            ['getParam'], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager',
            ['getWebsite', '__wakeup'], [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->currencyMock = $this->getMock('Magento\Directory\Model\Currency',
            ['getCode', '__wakeup'], [], '', false);
        $this->websiteMock = $this->getMock('Magento\Store\Model\Website',
            ['getDefaultStore', '__wakeup'], [], '', false);
        $this->closureMock = function () {
            return 'ExpectedValue';
        };
        $this->subjectMock = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->plugin = new \Magento\Store\App\Action\Plugin\Context(
            $this->sessionMock,
            $this->httpContextMock,
            $this->httpRequestMock,
            $this->storeManagerMock
        );
    }

    /**
     * Test aroundDispatch
     */
    public function testAroundDispatch()
    {
        $this->storeManagerMock->expects($this->exactly(2))
            ->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));
        $this->websiteMock->expects($this->exactly(2))
            ->method('getDefaultStore')
            ->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())
            ->method('getDefaultCurrency')
            ->will($this->returnValue($this->currencyMock));
        $this->storeMock->expects($this->once())
            ->method('getStoreCodeFromCookie')
            ->will($this->returnValue('storeCookie'));
        $this->currencyMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('UAH'));
        $this->sessionMock->expects($this->once())
            ->method('getCurrencyCode')
            ->will($this->returnValue('UAH'));

        $this->httpRequestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('___store'))
            ->will($this->returnValue('default'));

        $this->httpContextMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->will($this->returnValueMap([
                [\Magento\Core\Helper\Data::CONTEXT_CURRENCY, 'UAH', 'UAH', $this->httpContextMock],
                [\Magento\Core\Helper\Data::CONTEXT_STORE, 'default', 'default', $this->httpContextMock],
            ]));
        $this->assertEquals(
            'ExpectedValue',
            $this->plugin->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }
}
