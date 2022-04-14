<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\UserAuthorized;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Is user authorized test
 */
class UserAuthorizedTest extends TestCase
{
    /**
     * @var UserContextInterface|MockObject $userContext
     */
    private $userContext;

    /**
     * @var UserProfileRepositoryInterface| MockObject $userProfile
     */
    private $userProfile;

    /**
     * @var UserAuthorized $userAuthorized
     */
    private $userAuthorized;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->userProfile = $this->createMock(UserProfileRepositoryInterface::class);
        $this->userAuthorized = new UserAuthorized(
            $this->userContext,
            $this->userProfile
        );
    }

    /**
     * Ensure that user authorized or not
     */
    public function testExecute(): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $userProfileMock = $this->createMock(UserProfileInterface::class);
        $this->userProfile->expects($this->exactly(1))
            ->method('getByUserId')
            ->willReturn($userProfileMock);
        $userProfileMock->expects($this->once())->method('getId')->willReturn(1);
        $userProfileMock->expects($this->once())->method('getAccessToken')->willReturn('token');
        $userProfileMock->expects($this->exactly(2))
            ->method('getAccessTokenExpiresAt')
            ->willReturn(date('Y-m-d H:i:s'));

        $this->assertTrue($this->userAuthorized->execute());
    }
}
