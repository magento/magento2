<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Test\Unit\Helper;

class CookieTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cookie\Helper\Cookie
     */
    protected $_object;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;

    public function testIsUserNotAllowSaveCookie()
    {
        $this->_initMock()->_getCookieStub([1 => 1]);
        $this->assertFalse($this->_object->isUserNotAllowSaveCookie());
        $request = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getCookie']);
        $request->expects($this->any())->method('getCookie')->will($this->returnValue(json_encode([])));
        $scopeConfig = $this->_getConfigStub();
        $context = $this->createPartialMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getRequest', 'getScopeConfig']
        );
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $this->_object = new \Magento\Cookie\Helper\Cookie(
            $context,
            $this->createMock(\Magento\Store\Model\StoreManager::class),
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
            $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getCookie']);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
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
        $this->_context = $this->createPartialMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getRequest', 'getScopeConfig']
        );
        $this->_context->expects($this->once())->method('getRequest')->will($this->returnValue($this->_request));
        $this->_context->expects($this->once())->method('getScopeConfig')->will($this->returnValue($scopeConfig));

        $this->_object = new \Magento\Cookie\Helper\Cookie(
            $this->_context,
            $this->createMock(\Magento\Store\Model\StoreManager::class),
            ['current_store' => $storeStub, 'website' => $this->_getWebsiteStub()]
        );
        $this->assertEquals($this->_object->getCookieRestrictionLifetime(), 60 * 60 * 24 * 365);
    }

    /**
     * @return $this
     */
    protected function _initMock()
    {
        $scopeConfig = $this->_getConfigStub();
        $this->_request =
            $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getCookie']);
        $this->_context = $this->createPartialMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getRequest', 'getScopeConfig']
        );
        $this->_context->expects($this->once())->method('getRequest')->will($this->returnValue($this->_request));
        $this->_context->expects($this->once())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $this->_object = new \Magento\Cookie\Helper\Cookie(
            $this->_context,
            $this->createMock(\Magento\Store\Model\StoreManager::class),
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
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        return $store;
    }

    /**
     * Create config stub
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getConfigStub()
    {
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
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
        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);

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
