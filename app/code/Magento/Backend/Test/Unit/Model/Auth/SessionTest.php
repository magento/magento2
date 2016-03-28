<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Auth;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SessionTest tests Magento\Backend\Model\Auth\Session
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
     * @var \Magento\Framework\Stdlib\CookieManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\Storage | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var \Magento\Framework\Acl\Builder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclBuilder;

    /**
     * @var Session
     */
    protected $session;

    protected function setUp()
    {
        $this->cookieMetadataFactory = $this->getMock(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory',
            ['createPublicCookieMetadata'],
            [],
            '',
            false
        );

        $this->config = $this->getMock('Magento\Backend\App\Config', ['getValue'], [], '', false);
        $this->cookieManager = $this->getMock(
            'Magento\Framework\Stdlib\Cookie\PhpCookieManager',
            ['getCookie', 'setPublicCookie'],
            [],
            '',
            false
        );
        $this->storage = $this->getMock(
            'Magento\Framework\Session\Storage',
            ['getUser', 'getAcl', 'setAcl'],
            [],
            '',
            false
        );
        $this->sessionConfig = $this->getMock(
            'Magento\Framework\Session\Config',
            ['getCookiePath', 'getCookieDomain', 'getCookieSecure', 'getCookieHttpOnly'],
            [],
            '',
            false
        );
        $this->aclBuilder = $this->getMockBuilder('Magento\Framework\Acl\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->session = $objectManager->getObject(
            'Magento\Backend\Model\Auth\Session',
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

    protected function tearDown()
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
        $aclMock = $this->getMockBuilder('Magento\Framework\Acl')->disableOriginalConstructor()->getMock();
        $this->aclBuilder->expects($this->any())->method('getAcl')->willReturn($aclMock);
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
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

    public function refreshAclDataProvider()
    {
        return [
            'User set via params' => [true],
            'User set to session object' => [false]
        ];
    }

    public function testIsLoggedInPositive()
    {
        $user = $this->getMock('Magento\User\Model\User', ['getId', '__wakeup'], [], '', false);
        $user->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->storage->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->assertTrue($this->session->isLoggedIn());
    }

    public function testProlong()
    {
        $name = session_name();
        $cookie = 'cookie';
        $path = '/';
        $domain = 'magento2';
        $secure = true;
        $httpOnly = true;

        $cookieMetadata = $this->getMock('Magento\Framework\Stdlib\Cookie\PublicCookieMetadata');
        $cookieMetadata->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->will($this->returnSelf());
        $cookieMetadata->expects($this->once())
            ->method('setDomain')
            ->with($domain)
            ->will($this->returnSelf());
        $cookieMetadata->expects($this->once())
            ->method('setSecure')
            ->with($secure)
            ->will($this->returnSelf());
        $cookieMetadata->expects($this->once())
            ->method('setHttpOnly')
            ->with($httpOnly)
            ->will($this->returnSelf());

        $this->cookieMetadataFactory->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->will($this->returnValue($cookieMetadata));

        $this->cookieManager->expects($this->once())
            ->method('getCookie')
            ->with($name)
            ->will($this->returnValue($cookie));
        $this->cookieManager->expects($this->once())
            ->method('setPublicCookie')
            ->with($name, $cookie, $cookieMetadata);

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
            $aclMock = $this->getMockBuilder('Magento\Framework\Acl')->disableOriginalConstructor()->getMock();
            $this->storage->expects($this->any())->method('getAcl')->willReturn($aclMock);
        }
        if ($isUserDefined) {
            $userMock = $this->getMockBuilder('Magento\User\Model\User')->disableOriginalConstructor()->getMock();
            $this->storage->expects($this->once())->method('getUser')->willReturn($userMock);
        }
        if ($isAclDefined && $isUserDefined) {
            $userMock->expects($this->any())->method('getAclRole')->willReturn($userAclRole);
            $aclMock->expects($this->once())->method('isAllowed')->with($userAclRole)->willReturn($isAllowed);
        }

        $this->assertEquals($expectedResult, $this->session->isAllowed('resource'));
    }

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

    public function firstPageAfterLoginDataProvider()
    {
        return [
            'First page after login' => [true],
            'Not first page after login' => [false],
        ];
    }
}
