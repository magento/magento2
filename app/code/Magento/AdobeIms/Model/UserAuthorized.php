<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\UserAuthorizedInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Represent functionality for getting information is user authorised or not
 */
class UserAuthorized implements UserAuthorizedInterface
{
    /**
     * @var UserProfileRepositoryInterface
     */
    private $userProfileRepository;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * UserAuthorized constructor.
     *
     * @param UserContextInterface $userContext
     * @param UserProfileRepositoryInterface $userProfileRepository
     */
    public function __construct(
        UserContextInterface $userContext,
        UserProfileRepositoryInterface $userProfileRepository
    ) {
        $this->userContext = $userContext;
        $this->userProfileRepository = $userProfileRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): bool
    {
        try {
            $adminUserId = $adminUserId ?? (int) $this->userContext->getUserId();
            $userProfile = $this->userProfileRepository->getByUserId($adminUserId);

            return !empty($userProfile->getId())
                && !empty($userProfile->getAccessToken())
                && !empty($userProfile->getAccessTokenExpiresAt())
                && strtotime($userProfile->getAccessTokenExpiresAt()) >= strtotime('now');
        } catch (\Exception $exception) {
            return false;
        }
    }
}
