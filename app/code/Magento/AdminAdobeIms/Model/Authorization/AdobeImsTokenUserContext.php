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
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Request;

/**
 * A user context determined by Adobe IMS tokens in a HTTP request Authorization header.
 */
class AdobeImsTokenUserContext implements UserContextInterface
{
    private const AUTHORIZATION_METHOD_HEADER_BEARER = 'bearer';

    /**
     * @var int
     */
    private $userId;

    /**
     * @var bool
     */
    private $isRequestProcessed;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var TokenReaderInterface
     */
    private TokenReaderInterface $tokenReader;

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

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
     * @param Request $request
     * @param TokenReaderInterface $tokenReader
     * @param ImsConnection $imsConnection
     * @param ImsConfig $imsConfig
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param UserProfileInterfaceFactory $userProfileFactory
     * @param User $adminUser
     */
    public function __construct(
        Request $request,
        TokenReaderInterface $tokenReader,
        ImsConnection $imsConnection,
        ImsConfig $imsConfig,
        UserProfileRepositoryInterface $userProfileRepository,
        UserProfileInterfaceFactory $userProfileFactory,
        User $adminUser
    ) {
        $this->request = $request;
        $this->tokenReader = $tokenReader;
        $this->imsConnection = $imsConnection;
        $this->imsConfig = $imsConfig;
        $this->userProfileRepository = $userProfileRepository;
        $this->userProfileFactory = $userProfileFactory;
        $this->adminUser = $adminUser;
    }

    /**
     * @inheritdoc
     */
    public function getUserId()
    {
        $this->processRequest();
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_ADMIN;
    }

    /**
     * Finds the bearer token and looks up the value.
     *
     * @return void
     * @throws AuthorizationException
     * @throws CouldNotSaveException
     * @throws InvalidArgumentException
     */
    private function processRequest()
    {
        if (!$this->imsConfig->enabled() || $this->isRequestProcessed) {
            return;
        }

        if (!$bearerToken = $this->getRequestedToken()) {
            return;
        }

        try {
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
        } catch (AuthenticationException $e) {
            $this->isRequestProcessed = true;
            return;
        }

        $this->userId = $adminUserId;
        $this->isRequestProcessed = true;
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
     * Getting requested token
     *
     * @return false|string
     */
    private function getRequestedToken()
    {
        $authorizationHeaderValue = $this->request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            $this->isRequestProcessed = true;
            return false;
        }

        $headerPieces = explode(" ", $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            $this->isRequestProcessed = true;
            return false;
        }

        $tokenType = strtolower($headerPieces[0]);
        if ($tokenType !== self::AUTHORIZATION_METHOD_HEADER_BEARER) {
            $this->isRequestProcessed = true;
            return false;
        }

        return $headerPieces[1];
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
