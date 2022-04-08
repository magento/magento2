<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\Authorization;

use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterface;
use Magento\AdminAdobeIms\Api\ImsWebapiRepositoryInterface;
use Magento\AdminAdobeIms\Api\TokenReaderInterface;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Model\User;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterfaceFactory;
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
     * @var ImsWebapiInterfaceFactory
     */
    private ImsWebapiInterfaceFactory $imsWebapiFactory;

    /**
     * @var User
     */
    private User $adminUser;

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @var ImsWebapiRepositoryInterface
     */
    private ImsWebapiRepositoryInterface $imsWebapiRepository;

    /**
     * @param TokenReaderInterface $tokenReader
     * @param ImsWebapiRepositoryInterface $imsWebapiRepository
     * @param ImsWebapiInterfaceFactory $imsWebapiFactory
     * @param User $adminUser
     * @param ImsConnection $imsConnection
     */
    public function __construct(
        TokenReaderInterface $tokenReader,
        ImsWebapiRepositoryInterface $imsWebapiRepository,
        ImsWebapiInterfaceFactory $imsWebapiFactory,
        User $adminUser,
        ImsConnection $imsConnection
    ) {
        $this->tokenReader = $tokenReader;
        $this->imsWebapiFactory = $imsWebapiFactory;
        $this->adminUser = $adminUser;
        $this->imsConnection = $imsConnection;
        $this->imsWebapiRepository = $imsWebapiRepository;
    }

    /**
     * Retrieve admin user id by token
     *
     * @param string $bearerToken
     * @return int
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws CouldNotSaveException
     * @throws InvalidArgumentException
     */
    public function getAdminUserIdByToken(string $bearerToken): int
    {
        $this->tokenReader->read($bearerToken);
        $imsWebapiEntity = $this->imsWebapiRepository->getByAccessToken($bearerToken);

        if ($imsWebapiEntity->getId()) {
            $adminUserId = $imsWebapiEntity->getUserId();
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
            $profile['access_token'] = $bearerToken;

            $imsWebapiInterface = $this->createImsWebapiInterface($adminUserId);
            $this->imsWebapiRepository->save($this->updateImsWebapi($imsWebapiInterface, $profile));
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
     * Get ims webapi entity
     *
     * @param int $adminUserId
     * @return ImsWebapiInterface
     */
    private function createImsWebapiInterface(int $adminUserId): ImsWebapiInterface
    {
        return $this->imsWebapiFactory->create(
            [
                'data' => [
                    'admin_user_id' => $adminUserId
                ]
            ]
        );
    }

    /**
     * Update admin adobe ims webapi entry
     *
     * @param ImsWebapiInterface $imsWebapiInterface
     * @param array $profile
     * @return ImsWebapiInterface
     */
    private function updateImsWebapi(
        ImsWebapiInterface $imsWebapiInterface,
        array $profile
    ): ImsWebapiInterface {
        $imsWebapiInterface->setAccessTokenHash($this->encryptor->getHash($profile['access_token']));
        $imsWebapiInterface->setLastCheckTime($this->dateTime->gmtTimestamp());

        return $imsWebapiInterface;
    }
}
