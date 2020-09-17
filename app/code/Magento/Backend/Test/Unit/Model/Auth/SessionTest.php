<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Auth;

use Magento\Backend\App\Config;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Session\Storage;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SessionTest tests Magento\Backend\Model\Auth\Session
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var \Magento\Framework\Session\Config|MockObject
     */
    private $sessionConfig;

    /**
     * @var CookieManagerInterface|MockObject
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    private $cookieMetadataFactory;

    /**
     * @var Storage|MockObject
     */
    private $storage;

    /**
     * @var Builder|MockObject
     */
    private $aclBuilder;

    /**
     * @var Session
     */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->cookieMetadataFactory = $this->createPartialMock(
            CookieMetadataFactory::class,
            ['createPublicCookieMetadata']
        );

        $this->config = $this->createPartialMock(Config::class, ['getValue']);
        $this->cookieManager = $this->createPartialMock(
            PhpCookieManager::class,
            ['getCookie', 'setPublicCookie']
        );
        $this->storage = $this->getMockBuilder(Storage::class)
            ->addMethods(['getUser', 'getAcl', 'setAcl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionConfig = $this->createPartialMock(
            \Magento\Framework\Session\Config::class,
            ['getCookiePath', 'getCookieDomain', 'getCookieSecure', 'getCookieHttpOnly']
        );
        $this->aclBuilder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->session = $objectManager->getObject(
            Session::class,
            [
                'config' => $this->config,
                'sessionConfig' => $this->sessionConfig,
                'cookieManager' => $this->cookieManager,
                'cookieMetadataFactory' => $this->cookieMetadataFactory,
                'storage' => $this->storage,
                'aclBuilder' => $this->aclBuilder
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->config = null;
        $this->sessionConfig = null;
        $this->session = null;
    }

    /**
     * @dataProvider refreshAclDataProvider
     * @param $isUserPassedViaParams
     */
    public function testRefreshAcl($isUserPassedViaParams)
    {
        $aclMock = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->aclBuilder->expects($this->any())->method('getAcl')->willReturn($aclMock);
        $userMock = $this->getMockBuilder(User::class)
            ->setMethods(['getReloadAclFlag', 'setReloadAclFlag', 'unsetData', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $userMock->expects($this->any())->method('getReloadAclFlag')->willReturn(true);
        $userMock->expects($this->once())->method('setReloadAclFlag')->with('0')->willReturnSelf();
        $userMock->expects($this->once())->method('save');
        $this->storage->expects($this->once())->method('setAcl')->with($aclMock);
        $this->storage->expects($this->any())->method('getAcl')->willReturn($aclMock);
        if ($isUserPassedViaParams) {
            $this->session->refreshAcl($userMock);
        } else {
            $this->storage->expects($this->once())->method('getUser')->willReturn($userMock);
            $this->session->refreshAcl();
        }
        $this->assertSame($aclMock, $this->session->getAcl());
    }

    /**
     * @return array
     */
    public function refreshAclDataProvider()
    {
        return [
            'User set via params' => [true],
            'User set to session object' => [false]
        ];
    }

    public function testIsLoggedInPositive()
    {
        $user = $this->createPartialMock(User::class, ['getId', '__wakeup']);
        $user->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storage->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

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

        $this->config->expects($this->once())
            ->method('getValue')
            ->with(Session::XML_PATH_SESSION_LIFETIME)
            ->willReturn($lifetime);
        $cookieMetadata = $this->createMock(PublicCookieMetadata::class);
        $cookieMetadata->expects($this->once())
            ->method('setDuration')
            ->with($lifetime)->willReturnSelf();
        $cookieMetadata->expects($this->once())
            ->method('setPath')
            ->with($path)->willReturnSelf();
        $cookieMetadata->expects($this->once())
            ->method('setDomain')
            ->with($domain)->willReturnSelf();
        $cookieMetadata->expects($this->once())
            ->method('setSecure')
            ->with($secure)->willReturnSelf();
        $cookieMetadata->expects($this->once())
            ->method('setHttpOnly')
            ->with($httpOnly)->willReturnSelf();

        $this->cookieMetadataFactory->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadata);

        $this->cookieManager->expects($this->once())
            ->method('getCookie')
            ->with($name)
            ->willReturn($cookie);
        $this->cookieManager->expects($this->once())
            ->method('setPublicCookie')
            ->with($name, $cookie, $cookieMetadata);

        $this->sessionConfig->expects($this->once())
            ->method('getCookiePath')
            ->willReturn($path);
        $this->sessionConfig->expects($this->once())
            ->method('getCookieDomain')
            ->willReturn($domain);
        $this->sessionConfig->expects($this->once())
            ->method('getCookieSecure')
            ->willReturn($secure);
        $this->sessionConfig->expects($this->once())
            ->method('getCookieHttpOnly')
            ->willReturn($httpOnly);

        $this->session->prolong();

        $this->assertLessThanOrEqual(time(), $this->session->getUpdatedAt());
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param bool $isUserDefined
     * @param bool $isAclDefined
     * @param bool $isAllowed
     * @param true $expectedResult
     */
    public function testIsAllowed($isUserDefined, $isAclDefined, $isAllowed, $expectedResult)
    {
        $userAclRole = 'userAclRole';
        if ($isAclDefined) {
            $aclMock = $this->getMockBuilder(Acl::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->storage->expects($this->any())->method('getAcl')->willReturn($aclMock);
        }
        if ($isUserDefined) {
            $userMock = $this->getMockBuilder(User::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->storage->expects($this->once())->method('getUser')->willReturn($userMock);
        }
        if ($isAclDefined && $isUserDefined) {
            $userMock->expects($this->any())->method('getAclRole')->willReturn($userAclRole);
            $aclMock->expects($this->once())->method('isAllowed')->with($userAclRole)->willReturn($isAllowed);
        }

        $this->assertEquals($expectedResult, $this->session->isAllowed('resource'));
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return [
            "Negative: User not defined" => [false, true, true, false],
            "Negative: Acl not defined" => [true, false, true, false],
            "Negative: Permission denied" => [true, true, false, false],
            "Positive: Permission granted" => [true, true, false, false],
        ];
    }

    /**
     * @dataProvider firstPageAfterLoginDataProvider
     * @param bool $isFirstPageAfterLogin
     */
    public function testFirstPageAfterLogin($isFirstPageAfterLogin)
    {
        $this->session->setIsFirstPageAfterLogin($isFirstPageAfterLogin);
        $this->assertEquals($isFirstPageAfterLogin, $this->session->isFirstPageAfterLogin());
    }

    /**
     * @return array
     */
    public function firstPageAfterLoginDataProvider()
    {
        return [
            'First page after login' => [true],
            'Not first page after login' => [false],
        ];
    }
}
