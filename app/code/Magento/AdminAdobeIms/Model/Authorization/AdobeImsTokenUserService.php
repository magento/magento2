<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\Authorization;

use Magento\AdminAdobeIms\Api\TokenReaderInterface;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Model\User;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;

class AdobeImsTokenUserService
{
    /**
     * @var TokenReaderInterface
     */
    private TokenReaderInterface $tokenReader;

    /**
     * @var UserProfileRepositoryInterface
     */
    private UserProfileRepositoryInterface $userProfileRepository;

    /**
     * @var UserProfileInterfaceFactory
     */
    private UserProfileInterfaceFactory $userProfileFactory;

    /**
     * @var User
     */
    private User $adminUser;

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @param TokenReaderInterface $tokenReader
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param UserProfileInterfaceFactory $userProfileFactory
     * @param User $adminUser
     * @param ImsConnection $imsConnection
     */
    public function __construct(
        TokenReaderInterface $tokenReader,
        UserProfileRepositoryInterface $userProfileRepository,
        UserProfileInterfaceFactory $userProfileFactory,
        User $adminUser,
        ImsConnection $imsConnection
    ) {
        $this->tokenReader = $tokenReader;
        $this->userProfileRepository = $userProfileRepository;
        $this->userProfileFactory = $userProfileFactory;
        $this->adminUser = $adminUser;
        $this->imsConnection = $imsConnection;
    }

    /**
     * Update adobe_user_id for admin user
     *
     * @param string $bearerToken
     * @return int
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws CouldNotSaveException
     * @throws InvalidArgumentException
     */
    public function updateAdminUserProfile(string $bearerToken): int
    {
        $tokenData = $this->tokenReader->read($bearerToken);

        $adobeUserId = $tokenData['adobe_user_id'] ?? '';

        $userProfile = $this->userProfileRepository->getByAdobeUserId($adobeUserId);

        if ($userProfile->getId()) {
            $adminUserId = (int) $userProfile->getData('admin_user_id');
        } else {
            $profile = $this->getUserProfile($bearerToken);
            if (empty($profile['email'])) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }
            $adminUser = $this->adminUser->loadByEmail($profile['email']);
            if (empty($adminUser['user_id'])) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }

            $adminUserId = (int) $adminUser['user_id'];
            $profile['adobe_user_id'] = $adobeUserId;

            $userProfileInterface = $this->getUserProfileInterface($adminUserId);
            $this->userProfileRepository->save($this->updateUserProfile($userProfileInterface, $profile));
        }

        return $adminUserId;
    }

    /**
     * Get adobe user profile
     *
     * @param string $bearerToken
     * @return array
     * @throws AuthenticationException
     */
    private function getUserProfile(string $bearerToken): array
    {
        try {
            return $this->imsConnection->getProfile($bearerToken);
        } catch (\Exception $exception) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }
    }

    /**
     * Get user profile entity
     *
     * @param int $adminUserId
     * @return UserProfileInterface
     */
    private function getUserProfileInterface(int $adminUserId): UserProfileInterface
    {
        try {
            return $this->userProfileRepository->getByUserId($adminUserId);
        } catch (NoSuchEntityException $exception) {
            return $this->userProfileFactory->create(
                [
                    'data' => [
                        'admin_user_id' => $adminUserId
                    ]
                ]
            );
        }
    }

    /**
     * Update user profile with the data from token
     *
     * @param UserProfileInterface $userProfileInterface
     * @param array $profile
     * @return UserProfileInterface
     */
    private function updateUserProfile(
        UserProfileInterface $userProfileInterface,
        array $profile
    ): UserProfileInterface {
        $userProfileInterface->setName($profile['name'] ?? '');
        $userProfileInterface->setEmail($profile['email'] ?? '');
        $userProfileInterface->setAdobeUserId($profile['adobe_user_id']);

        return $userProfileInterface;
    }
}
