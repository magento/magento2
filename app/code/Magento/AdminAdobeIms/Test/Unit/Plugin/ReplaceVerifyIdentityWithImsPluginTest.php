<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Plugin;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Plugin\ReplaceVerifyIdentityWithImsPlugin;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\IsTokenValidInterface;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReplaceVerifyIdentityWithImsPluginTest extends TestCase
{
    /**
     * @var ReplaceVerifyIdentityWithImsPlugin
     */
    private $plugin;

    /**
     * @var MockObject|StorageInterface
     */
    private $storageMock;

    /**
     * @var MockObject|Auth
     */
    private $authMock;

    /**
     * @var ImsConfig|MockObject
     */
    private $adminImsConfigMock;

    /**
     * @var IsTokenValidInterface|MockObject
     */
    private $isTokenValid;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->storageMock = $this->getMockBuilder(StorageInterface::class)
            ->setMethods(['getAdobeAccessToken', 'getAdobeReAuthToken', 'setAdobeReAuthToken'])
            ->getMockForAbstractClass();

        $this->authMock = $this->getMockBuilder(Auth::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adminImsConfigMock = $this->createMock(ImsConfig::class);
        $this->isTokenValid = $this->createMock(IsTokenValidInterface::class);

        $this->plugin = $objectManagerHelper->getObject(
            ReplaceVerifyIdentityWithImsPlugin::class,
            [
                'adminImsConfig' => $this->adminImsConfigMock,
                'isTokenValid' => $this->isTokenValid,
                'auth' => $this->authMock,
            ]
        );
    }

    /**
     * Test plugin proceeds when AdminAdobeIms Module is disabled
     *
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function testAroundVerifyIdentityCallsProceedWhenModuleIsDisabled(): void
    {
        $this->authMock->expects($this->never())
            ->method('getAuthStorage');

        $this->adminImsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(false);

        $subject = $this->createMock(User::class);

        $expectedResult = true;

        $proceed = function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->isTokenValid
            ->expects($this->never())
            ->method('validateToken');

        $this->assertEquals($expectedResult, $this->plugin->aroundVerifyIdentity($subject, $proceed, ''));
    }

    /**
     * Test Plugin verifies access_token
     *
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function testAroundVerifyIdentityVerifiesAccessTokenWhenModuleIsEnabled(): void
    {
        $this->storageMock
            ->expects($this->once())
            ->method('getAdobeAccessToken')
            ->willReturn('accessToken');

        $this->storageMock
            ->expects($this->once())
            ->method('getAdobeReAuthToken')
            ->willReturn('reAuthToken');

        $this->authMock->expects($this->atLeastOnce())
        ->method('getAuthStorage')
        ->willReturn($this->storageMock);

        $this->adminImsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $subject = $this->createMock(User::class);

        $this->isTokenValid
            ->expects($this->once())
            ->method('validateToken')
            ->willReturn(true);

        $expectedResult = true;

        $proceed = function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->assertEquals($expectedResult, $this->plugin->aroundVerifyIdentity($subject, $proceed, ''));
    }

    /**
     * Test Plugin throws exception when access_token is invalid
     *
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function testAroundVerifyIdentityThrowsExceptionOnInvalidToken(): void
    {
        $this->storageMock
            ->expects($this->once())
            ->method('getAdobeAccessToken')
            ->willReturn('invalidToken');

        $this->storageMock
            ->expects($this->once())
            ->method('getAdobeReAuthToken')
            ->willReturn('invalidToken');

        $this->authMock->expects($this->atLeastOnce())
            ->method('getAuthStorage')
            ->willReturn($this->storageMock);

        $this->adminImsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $subject = $this->createMock(User::class);

        $this->isTokenValid
            ->expects($this->once())
            ->method('validateToken')
            ->willReturn(false);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.');

        $expectedResult = true;

        $proceed = function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->assertEquals($expectedResult, $this->plugin->aroundVerifyIdentity($subject, $proceed, ''));
    }

    /**
     * Test Plugin throws exception when access_token is invalid
     *
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function testAroundVerifyIdentityThrowsExceptionOnEmptyToken(): void
    {
        $this->storageMock
            ->expects($this->once())
            ->method('getAdobeAccessToken')
            ->willReturn(null);

        $this->storageMock
            ->expects($this->once())
            ->method('getAdobeReAuthToken')
            ->willReturn(null);

        $this->authMock->expects($this->once())
            ->method('getAuthStorage')
            ->willReturn($this->storageMock);

        $this->adminImsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $subject = $this->createMock(User::class);

        $this->isTokenValid
            ->expects($this->never())
            ->method('validateToken');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.');

        $expectedResult = true;

        $proceed = function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->assertEquals($expectedResult, $this->plugin->aroundVerifyIdentity($subject, $proceed, ''));
    }
}
