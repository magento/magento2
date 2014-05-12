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
namespace Magento\Backend\Model\Auth;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class SessionTest
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\Session\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookie;

    /**
     * @var \Magento\Framework\Session\Storage | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var Session
     */
    protected $session;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Backend\App\Config', ['getValue'], [], '', false);
        $this->cookie = $this->getMock('Magento\Framework\Stdlib\Cookie', ['get', 'set'], [], '', false);
        $this->storage = $this->getMock('Magento\Framework\Session\Storage', ['getUser'], [], '', false);
        $this->sessionConfig = $this->getMock(
            'Magento\Framework\Session\Config',
            ['getCookiePath', 'getCookieDomain', 'getCookieSecure', 'getCookieHttpOnly'],
            [],
            '',
            false
        );
        $objectManager= new ObjectManager($this);
        $this->session = $objectManager->getObject(
            'Magento\Backend\Model\Auth\Session',
            [
                'config' => $this->config,
                'sessionConfig' => $this->sessionConfig,
                'cookie' => $this->cookie,
                'storage' => $this->storage
            ]
        );
    }

    protected function tearDown()
    {
        $this->config = null;
        $this->sessionConfig = null;
        $this->session = null;
    }

    public function testIsLoggedInPositive()
    {
        $lifetime = 900;
        $user = $this->getMock('Magento\User\Model\User', ['getId', '__wakeup'], [], '', false);
        $user->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->session->setUpdatedAt(time() + $lifetime); // Emulate just updated session

        $this->storage->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->config->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME)
            ->will($this->returnValue($lifetime));

        $this->assertTrue($this->session->isLoggedIn());
    }

    public function testProlong()
    {
        $name = session_name();
        $cookie = 'cookie';
        $lifetime = 900;
        $path = '/';
        $domain = 'magento2';
        $secure = true;
        $httpOnly = true;

        $this->cookie->expects($this->once())
            ->method('get')
            ->with($name)
            ->will($this->returnValue($cookie));
        $this->cookie->expects($this->once())
            ->method('set')
            ->with($name, $cookie, $lifetime, $path, $domain, $secure, $httpOnly);

        $this->config->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME)
            ->will($this->returnValue($lifetime));
        $this->sessionConfig->expects($this->once())
            ->method('getCookiePath')
            ->will($this->returnValue($path));
        $this->sessionConfig->expects($this->once())
            ->method('getCookieDomain')
            ->will($this->returnValue($domain));
        $this->sessionConfig->expects($this->once())
            ->method('getCookieSecure')
            ->will($this->returnValue($secure));
        $this->sessionConfig->expects($this->once())
            ->method('getCookieHttpOnly')
            ->will($this->returnValue($httpOnly));

        $this->session->prolong();

        $this->assertLessThanOrEqual(time(), $this->session->getUpdatedAt());
    }
}
