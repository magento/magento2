<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Session model
     *
     * @var \Magento\Persistent\Model\Session
     */
    protected $session;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * The existing cookies
     *
     * @var array
     */
    protected $existingCookies;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->session = $this->objectManager->create(
            \Magento\Persistent\Model\Session::class
        );
        $this->existingCookies = $_COOKIE;
    }

    public function tearDown()
    {
        $_COOKIE = $this->existingCookies;
    }

    public function testSetPersistentCookie()
    {
        $this->assertArrayNotHasKey(Session::COOKIE_NAME, $_COOKIE);
        $key = 'sessionKey';
        $this->session->setKey($key);
        $this->session->setPersistentCookie(1000, '/');
        $this->assertEquals($key, $_COOKIE[Session::COOKIE_NAME]);
    }

    public function testRemovePersistendCookie()
    {
        $_COOKIE[Session::COOKIE_NAME] = 'cookieValue';
        $this->session->removePersistentCookie();
        $this->assertArrayNotHasKey(Session::COOKIE_NAME, $_COOKIE);
    }

    /**
     * @param int $duration
     * @param string $cookieValue
     * @dataProvider renewPersistentCookieDataProvider
     */
    public function testRenewPersistentCookie($duration, $cookieValue = 'cookieValue')
    {
        $_COOKIE[Session::COOKIE_NAME] = $cookieValue;
        $this->session->renewPersistentCookie($duration, '/');
        $this->assertEquals($cookieValue, $_COOKIE[Session::COOKIE_NAME]);
    }

    public function renewPersistentCookieDataProvider()
    {
        return [
            'no duration' => [null],
            'no cookie' => [1000, null],
            'all' => [1000],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testLoadByCookieKey()
    {
        /** @var \Magento\Persistent\Model\Session $preSession */
        $preSession = $this->objectManager->get(\Magento\Persistent\Model\SessionFactory::class)
            ->create()
            ->loadByCookieKey();
        $this->assertNull($preSession->getCustomerId());

        $this->session->setCustomerId(1)->save();
        $this->session->setPersistentCookie(1000, '/');

        /** @var \Magento\Persistent\Model\Session $postSession */
        $postSession = $this->objectManager->get(\Magento\Persistent\Model\SessionFactory::class)
            ->create()
            ->loadByCookieKey();
        $this->assertEquals(1, $postSession->getCustomerId());
    }
}
