<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Test\Unit\Model;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SendFriend\Helper\Data;
use Magento\SendFriend\Model\SendFriend;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test SendFriend
 *
 */
class SendFriendTest extends TestCase
{
    /**
     * @var SendFriend
     */
    protected $model;

    /**
     * @var MockObject|CookieManagerInterface
     */
    protected $cookieManagerMock;

    /**
     * @var MockObject
     */
    protected $sendfriendDataMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->sendfriendDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);

        $this->model = $objectManager->getObject(
            SendFriend::class,
            [
                'sendfriendData' => $this->sendfriendDataMock,
                'cookieManager' => $this->cookieManagerMock,
            ]
        );
    }

    public function testGetSentCountWithCheckCookie()
    {
        $cookieName = 'testCookieName';
        $this->sendfriendDataMock->expects($this->once())->method('getLimitBy')->with()->willReturn(
            Data::CHECK_COOKIE
        );
        $this->sendfriendDataMock->expects($this->once())->method('getCookieName')->with()->willReturn(
            $cookieName
        );

        $this->cookieManagerMock->expects($this->once())->method('getCookie')->with($cookieName);
        $this->assertEquals(0, $this->model->getSentCount());
    }

    public function testSentCountByCookies()
    {
        $cookieName = 'testCookieName';
        $this->sendfriendDataMock->expects($this->once())->method('getCookieName')->with()->willReturn(
            $cookieName
        );

        $this->cookieManagerMock->expects($this->once())->method('getCookie')->with($cookieName);
        $this->cookieManagerMock->expects($this->once())->method('setSensitiveCookie');
        $sendFriendClass = new \ReflectionClass(SendFriend::class);
        $method = $sendFriendClass->getMethod('_sentCountByCookies');
        $method->setAccessible(true);
        $method->invokeArgs($this->model, [true]);
    }
}
