<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Api\ImsWebapiRepositoryInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\FlushUserTokensInterface;
use Magento\Authorization\Model\UserContextInterface;

class FlushUserTokens implements FlushUserTokensInterface
{
    /**
     * @var ImsWebapiRepositoryInterface
     */
    private ImsWebapiRepositoryInterface $imsWebapiRepository;

    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * FlushUserTokens constructor.
     *
     * @param ImsWebapiRepositoryInterface $imsWebapiRepository
     * @param UserContextInterface $userContext
     */
    public function __construct(
        ImsWebapiRepositoryInterface $imsWebapiRepository,
        UserContextInterface $userContext
    ) {
        $this->imsWebapiRepository = $imsWebapiRepository;
        $this->userContext = $userContext;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): void
    {
        try {
            $adminUserId = $adminUserId ?? (int) $this->userContext->getUserId();
            $this->imsWebapiRepository->deleteByUserId($adminUserId);
        } catch (\Exception $exception) { //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            // User profile and tokens are not present in the system
        }
    }

    /**
     * Checks if the tokens are empty
     *
     * @param UserProfileInterface $userProfile
     * @return bool
     */
    private function isTokenDataEmpty(UserProfileInterface $userProfile) : bool
    {
        return empty($userProfile->getRefreshToken()) && empty($userProfile->getAccessToken());
    }
}
