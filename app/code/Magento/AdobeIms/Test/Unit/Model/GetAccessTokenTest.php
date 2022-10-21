<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\GetAccessToken;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Provides tests for getting the user access token
 */
class GetAccessTokenTest extends TestCase
{
    /**
     * @var UserContextInterface|MockObject
     */
    private $userContext;

    /**
     * @var UserProfileRepositoryInterface|MockObject
     */
    private $userProfile;

    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptor;

    /**
     * @var GetAccessToken
     */
    private $getAccessToken;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->userProfile = $this->createMock(UserProfileRepositoryInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);

        $this->getAccessToken = new GetAccessToken(
            $this->userContext,
            $this->userProfile,
            $this->encryptor
        );
    }

    /**
     * Test save.
     *
     * @param string|null $token
     * @dataProvider expectedDataProvider
     */
    public function testExecute(?string $token): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $userProfileMock = $this->createMock(UserProfileInterface::class);
        $this->userProfile->expects($this->exactly(1))
            ->method('getByUserId')
            ->willReturn($userProfileMock);
        $userProfileMock->expects($this->once())->method('getAccessToken')->willReturn($token);

        $decryptedToken = $token ?? '';

        $this->encryptor->expects($this->once())
            ->method('decrypt')
            ->with($token)
            ->willReturn($decryptedToken);

        $this->assertEquals($token, $this->getAccessToken->execute());
    }

    /**
     * Test execute with exception
     */
    public function testExecuteWIthException(): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $this->userProfile->expects($this->exactly(1))
            ->method('getByUserId')
            ->willThrowException(new NoSuchEntityException());

        $this->getAccessToken->execute();
    }

    /**
     * Data provider for get acces token method.
     *
     * @return array
     */
    public function expectedDataProvider(): array
    {
        return
            [
                [
                    'token' => 'kladjflakdjf3423rfzddsf'
                ],
                [
                    'null_token' => null
                ]
            ];
    }
}
