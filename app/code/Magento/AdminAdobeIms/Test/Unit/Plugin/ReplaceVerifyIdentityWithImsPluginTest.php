<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Plugin;

use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Plugin\ReplaceVerifyIdentityWithImsPlugin;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeIms\Model\UserProfileRepository;
use Magento\Framework\Encryption\EncryptorInterface;
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
     * @var ImsConfig|MockObject
     */
    private $imsConfigMock;

    /**
     * @var ImsConnection|MockObject
     */
    private $imsConnectionMock;

    /**
     * @var UserProfileRepository|MockObject
     */
    private $userProfileRepository;

    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptor;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->imsConfigMock = $this->createMock(ImsConfig::class);
        $this->imsConnectionMock = $this->createMock(ImsConnection::class);
        $this->userProfileRepository = $this->createMock(UserProfileRepository::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);

        $this->plugin = $objectManagerHelper->getObject(
            ReplaceVerifyIdentityWithImsPlugin::class,
            [
                'imsConfig' => $this->imsConfigMock,
                'imsConnection' => $this->imsConnectionMock,
                'userProfileRepository' => $this->userProfileRepository,
                'encryptor' => $this->encryptor,
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
        $this->imsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(false);

        $subject = $this->createMock(User::class);

        $expectedResult = true;

        /**
         * @param $request
         * @return bool
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };

        $this->imsConnectionMock
            ->expects($this->never())
            ->method('verifyToken');

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
        $this->imsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $subject = $this->createMock(User::class);

        $this->encryptor
            ->expects($this->once())
            ->method('decrypt')
            ->willReturn('accessToken');

        $this->imsConnectionMock
            ->expects($this->once())
            ->method('verifyToken')
            ->willReturn(true);

        $expectedResult = true;

        /**
         * @param $request
         * @return bool
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        $proceed = function ($request) use ($expectedResult) {
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
        $this->imsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $subject = $this->createMock(User::class);

        $this->encryptor
            ->expects($this->once())
            ->method('decrypt')
            ->willReturn('accessToken');

        $this->imsConnectionMock
            ->expects($this->once())
            ->method('verifyToken')
            ->willReturn(false);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.');

        $expectedResult = true;

        /**
         * @param $request
         * @return bool
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };

        $this->assertEquals($expectedResult, $this->plugin->aroundVerifyIdentity($subject, $proceed, ''));
    }
}
