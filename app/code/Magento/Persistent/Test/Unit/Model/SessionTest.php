<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Persistent\Model\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var CookieManagerInterface|MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    protected $cookieMetadataFactoryMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->configMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            CookieMetadataFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $resourceMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            false,
            true,
            ['__wakeup', 'getIdFieldName', 'getConnection', 'beginTransaction', 'delete', 'commit', 'rollBack']
        );

        $actionValidatorMock = $this->createMock(RemoveAction::class);
        $actionValidatorMock->expects($this->any())->method('isAllowed')->willReturn(true);

        $context = $helper->getObject(
            Context::class,
            [
                'actionValidator' => $actionValidatorMock,
            ]
        );

        $this->session = $helper->getObject(
            Session::class,
            [
                'sessionConfig' => $this->configMock,
                'cookieManager' => $this->cookieManagerMock,
                'context'       => $context,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'request' => $this->createMock(Http::class),
                'resource' => $resourceMock,
            ]
        );
    }

    public function testLoadByCookieKeyWithNull()
    {
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(Session::COOKIE_NAME)
            ->willReturn(null);
        $this->session->loadByCookieKey(null);
    }

    /**
     * @covers \Magento\Persistent\Model\Session::removePersistentCookie
     */
    public function testAfterDeleteCommit()
    {
        $cookiePath = 'some_path';
        $this->configMock->expects($this->once())->method('getCookiePath')->willReturn($cookiePath);
        $cookieMetadataMock = $this->getMockBuilder(SensitiveCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with($cookiePath)->willReturnSelf();
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createSensitiveCookieMetadata')
            ->willReturn($cookieMetadataMock);
        $this->cookieManagerMock->expects(
            $this->once()
        )->method(
            'deleteCookie'
        )->with(
            Session::COOKIE_NAME,
            $cookieMetadataMock
        );
        $this->session->afterDeleteCommit();
    }

    public function testSetPersistentCookie()
    {
        $cookiePath = 'some_path';
        $duration = 1000;
        $key = 'sessionKey';
        $this->session->setKey($key);
        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with($cookiePath)->willReturnSelf();
        $cookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->with($duration)->willReturnSelf();
        $cookieMetadataMock->expects($this->once())
            ->method('setSecure')
            ->with(false)->willReturnSelf();
        $cookieMetadataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)->willReturnSelf();
        $cookieMetadataMock->expects($this->once())
            ->method('setSameSite')
            ->with('Lax')->willReturnSelf();
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);
        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                Session::COOKIE_NAME,
                $key,
                $cookieMetadataMock
            );
        $this->session->setPersistentCookie($duration, $cookiePath);
    }

    /**
     * @param $numGetCookieCalls
     * @param $numCalls
     * @param int $cookieDuration
     * @param string $cookieValue
     * @param string $cookiePath
     * @dataProvider renewPersistentCookieDataProvider
     */
    public function testRenewPersistentCookie(
        $numGetCookieCalls,
        $numCalls,
        $cookieDuration = 1000,
        $cookieValue = 'cookieValue',
        $cookiePath = 'cookiePath'
    ) {
        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setPath')
            ->with($cookiePath)->willReturnSelf();
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setDuration')
            ->with($cookieDuration)->willReturnSelf();
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setSecure')
            ->with(false)->willReturnSelf();
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setHttpOnly')
            ->with(true)->willReturnSelf();
        $cookieMetadataMock->expects($this->exactly($numCalls))
            ->method('setSameSite')
            ->with('Lax')->willReturnSelf();
        $this->cookieMetadataFactoryMock->expects($this->exactly($numCalls))
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);
        $this->cookieManagerMock->expects($this->exactly($numGetCookieCalls))
            ->method('getCookie')
            ->with(Session::COOKIE_NAME)
            ->willReturn($cookieValue);
        $this->cookieManagerMock->expects($this->exactly($numCalls))
            ->method('setPublicCookie')
            ->with(
                Session::COOKIE_NAME,
                $cookieValue,
                $cookieMetadataMock
            );
        $this->session->renewPersistentCookie($cookieDuration, $cookiePath);
    }

    /**
     * Data provider for testRenewPersistentCookie
     *
     * @return array
     */
    public function renewPersistentCookieDataProvider()
    {
        return [
            'no duration' => [0, 0, null ],
            'no cookie' => [1, 0, 1000, null],
            'all' => [1, 1],
        ];
    }
}
