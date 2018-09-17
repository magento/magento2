<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\TestFramework\Helper\Bootstrap;

class StoreCookieManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreCookieManager
     */
    protected $storeCookieManager;

    /**
     * @var array
     */
    protected $existingCookies;

    protected function setUp()
    {
        $this->storeCookieManager = Bootstrap::getObjectManager()->create('Magento\Store\Model\StoreCookieManager');
        $this->existingCookies = $_COOKIE;
    }

    protected function tearDown()
    {
        $_COOKIE = $this->existingCookies;
    }

    public function testSetCookie()
    {
        $storeCode = 'store code';
        $store = $this->getMock('Magento\Store\Model\Store', ['getStorePath', 'getCode'], [], '', false);
        $store->expects($this->once())->method('getStorePath')->willReturn('/');
        $store->expects($this->once())->method('getCode')->willReturn($storeCode);

        $this->assertArrayNotHasKey(StoreCookieManager::COOKIE_NAME, $_COOKIE);
        $this->storeCookieManager->setStoreCookie($store);
        $this->assertArrayHasKey(StoreCookieManager::COOKIE_NAME, $_COOKIE);
        $this->assertEquals($storeCode, $_COOKIE[StoreCookieManager::COOKIE_NAME]);
    }
}
