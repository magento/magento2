<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sendfriend\Model;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Test Sendfriend
 *
 */
class SendfriendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sendfriend\Model\Sendfriend
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sendfriendDataMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->sendfriendDataMock = $this->getMockBuilder('Magento\Sendfriend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');

        $this->model = $objectManager->getObject(
            'Magento\Sendfriend\Model\Sendfriend',
            [
                'sendfriendData' => $this->sendfriendDataMock,
                'cookieManager' => $this->cookieManagerMock,
            ]
        );
    }

    public function testGetSentCountWithCheckCookie()
    {
        $cookieName = 'testCookieName';
        $this->sendfriendDataMock->expects($this->once())->method('getLimitBy')->with()->will(
            $this->returnValue(\Magento\Sendfriend\Helper\Data::CHECK_COOKIE)
        );
        $this->sendfriendDataMock->expects($this->once())->method('getCookieName')->with()->will(
            $this->returnValue($cookieName)
        );

        $this->cookieManagerMock->expects($this->once())->method('getCookie')->with($cookieName);
        $this->assertEquals(0, $this->model->getSentCount());
    }

    public function testSentCountByCookies()
    {
        $cookieName = 'testCookieName';
        $this->sendfriendDataMock->expects($this->once())->method('getCookieName')->with()->will(
            $this->returnValue($cookieName)
        );

        $this->cookieManagerMock->expects($this->once())->method('getCookie')->with($cookieName);
        $this->cookieManagerMock->expects($this->once())->method('setSensitiveCookie');
        $sendFriendClass = new \ReflectionClass('Magento\Sendfriend\Model\Sendfriend');
        $method = $sendFriendClass->getMethod('_sentCountByCookies');
        $method->setAccessible(true);
        $method->invokeArgs($this->model, [true]);
    }
}
