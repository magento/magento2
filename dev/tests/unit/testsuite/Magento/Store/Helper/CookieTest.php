<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Helper;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Helper\Cookie
     */
    protected $_object;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Core\Helper\Context
     */
    protected $_context;

    public function testIsUserNotAllowSaveCookie()
    {
        $this->_initMock()->_getCookieStub([1 => 1]);
        $this->assertFalse($this->_object->isUserNotAllowSaveCookie());
        $request = $this->getMock('\Magento\Framework\App\Request\Http', ['getCookie'], [], '', false, false);
        $request->expects($this->any())->method('getCookie')->will($this->returnValue(json_encode([])));
        $context =
            $this->getMock('Magento\Framework\App\Helper\Context', ['getRequest'], [], '', false, false);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $this->_object = new \Magento\Store\Helper\Cookie(
            $context,
            $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false, false),
            $this->_getConfigStub(),
            ['current_store' => $this->_getStoreStub(), 'website' => $this->_getWebsiteStub()]
        );
        $this->assertTrue($this->_object->isUserNotAllowSaveCookie());
    }

    public function testGetAcceptedSaveCookiesWebsiteIds()
    {
        $this->_initMock()->_getCookieStub([1 => 1]);
        $this->assertEquals($this->_object->getAcceptedSaveCookiesWebsiteIds(), json_encode([1 => 1]));
    }

    public function testGetCookieRestrictionLifetime()
    {
        $this->_request =
            $this->getMock('\Magento\Framework\App\Request\Http', ['getCookie'], [], '', false, false);
        $this->_context =
            $this->getMock('Magento\Framework\App\Helper\Context', ['getRequest'], [], '', false, false);
        $this->_context->expects($this->once())->method('getRequest')->will($this->returnValue($this->_request));
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $storeStub = $this->_getStoreStub();
        $scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->will(
            $this->returnCallback([$this, 'getConfigMethodStub'])
        )->with(
            $this->equalTo('web/cookie/cookie_restriction_lifetime')
        );
        $this->_object = new \Magento\Store\Helper\Cookie(
            $this->_context,
            $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false, false),
            $scopeConfig,
            ['current_store' => $storeStub, 'website' => $this->_getWebsiteStub()]
        );
        $this->assertEquals($this->_object->getCookieRestrictionLifetime(), 60 * 60 * 24 * 365);
    }

    protected function _initMock()
    {
        $this->_request =
            $this->getMock('\Magento\Framework\App\Request\Http', ['getCookie'], [], '', false, false);
        $this->_context =
            $this->getMock('Magento\Framework\App\Helper\Context', ['getRequest'], [], '', false, false);
        $this->_context->expects($this->once())->method('getRequest')->will($this->returnValue($this->_request));
        $this->_object = new \Magento\Store\Helper\Cookie(
            $this->_context,
            $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false, false),
            $this->_getConfigStub(),
            ['current_store' => $this->_getStoreStub(), 'website' => $this->_getWebsiteStub()]
        );
        return $this;
    }

    /**
     * Create store stub
     * @return \Magento\Store\Model\Store
     */
    protected function _getStoreStub()
    {
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        return $store;
    }

    /**
     * Create config stub
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getConfigStub()
    {
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnCallback([$this, 'getConfigMethodStub'])
        );

        return $scopeConfig;
    }

    /**
     * Generate getCookie stub for mock request object
     *
     * @param array $cookieString
     */
    protected function _getCookieStub($cookieString = [])
    {
        $this->_request->expects(
            $this->any()
        )->method(
            'getCookie'
        )->will(
            $this->returnValue(json_encode($cookieString))
        );
    }

    /**
     * Create Website Stub
     * @return \Magento\Store\Model\Website
     */
    protected function _getWebsiteStub()
    {
        $websiteMock = $this->getMock('Magento\Store\Model\Website', [], [], '', false);

        $websiteMock->expects($this->any())->method('getId')->will($this->returnValue(1));

        return $websiteMock;
    }

    /**
     * Mock get config method
     * @static
     * @param string $hashName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getConfigMethodStub($hashName)
    {
        $defaultConfig = [
            'web/cookie/cookie_restriction' => 1,
            'web/cookie/cookie_restriction_lifetime' => 60 * 60 * 24 * 365,
        ];

        if (array_key_exists($hashName, $defaultConfig)) {
            return $defaultConfig[$hashName];
        }

        throw new \InvalidArgumentException('Unknow id = ' . $hashName);
    }
}
