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
     * @var \Magento\Framework\ObjectManager
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
            'Magento\Persistent\Model\Session'
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
        $preSession = $this->objectManager->get('Magento\Persistent\Model\SessionFactory')
            ->create()
            ->loadByCookieKey();
        $this->assertNull($preSession->getCustomerId());

        $this->session->setCustomerId(1)->save();
        $this->session->setPersistentCookie(1000, '/');

        /** @var \Magento\Persistent\Model\Session $postSession */
        $postSession = $this->objectManager->get('Magento\Persistent\Model\SessionFactory')
            ->create()
            ->loadByCookieKey();
        $this->assertEquals(1, $postSession->getCustomerId());
    }
}
