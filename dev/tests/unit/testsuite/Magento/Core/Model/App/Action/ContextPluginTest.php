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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\App\Action;

/**
 * Class ContextPluginTest
 */
class ContextPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\App\Action\ContextPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\App\Http\Context $httpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContextMock;

    /**
     * @var \Magento\App\Request\Http $httpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpRequestMock;

    /**
     * @var \Magento\Core\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Core\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var \Magento\Core\Model\Website|\PHPUnit_Framework_MockObject_MockObject
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
        $this->sessionMock = $this->getMock('Magento\Core\Model\Session',
            array('getCurrencyCode'), array(), '', false);
        $this->httpContextMock = $this->getMock('Magento\App\Http\Context',
            array(), array(), '', false);
        $this->httpRequestMock = $this->getMock('Magento\App\Request\Http',
            array('getCookie', 'getParam'), array(), '', false);
        $this->storeManagerMock = $this->getMock('Magento\Core\Model\StoreManager',
            array('getWebsite', '__wakeup'), array(), '', false);
        $this->storeMock = $this->getMock('Magento\Core\Model\Store',
            array('__wakeup', 'getDefaultCurrency'), array(), '', false);
        $this->currencyMock = $this->getMock('Magento\Directory\Model\Currency',
            array('getCode', '__wakeup'), array(), '', false);
        $this->websiteMock = $this->getMock('Magento\Core\Model\Website',
            array('getDefaultStore', '__wakeup'), array(), '', false);
        $this->closureMock = function () {
            return 'ExpectedValue';
        };
        $this->subjectMock = $this->getMock('Magento\App\Action\Action', array(), array(), '', false);
        $this->requestMock = $this->getMock('Magento\App\RequestInterface');
        $this->plugin = new \Magento\Core\Model\App\Action\ContextPlugin(
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

        $this->httpRequestMock->expects($this->once())
            ->method('getCookie')
            ->with($this->equalTo(\Magento\Core\Model\Store::COOKIE_NAME))
            ->will($this->returnValue(null));

        $this->httpContextMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->will($this->returnValueMap(array(
                array(\Magento\Core\Helper\Data::CONTEXT_CURRENCY, 'UAH', 'UAH', $this->httpContextMock),
                array(\Magento\Core\Helper\Data::CONTEXT_STORE, 'default', 'default', $this->httpContextMock),
            )));
        $this->assertEquals(
            'ExpectedValue',
            $this->plugin->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }
}
