<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\AdobeImsApi\Api\LogInInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\AdobeImsApi\Api\GetImageInterface;

/**
 * Login user to adobe account
 */
class LogIn implements LogInInterface
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var UserProfileRepositoryInterface
     */
    private $userProfileRepository;

    /**
     * @var UserProfileInterfaceFactory
     */
    private $userProfileFactory;

    /**
     * @var GetImageInterface
     */
    private $getUserImage;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param UserProfileInterfaceFactory $userProfileFactory
     * @param GetImageInterface $getImage
     * @param EncryptorInterface $encryptor
     * @param DateTime $dateTime
     */
    public function __construct(
        UserProfileRepositoryInterface $userProfileRepository,
        UserProfileInterfaceFactory $userProfileFactory,
        GetImageInterface $getImage,
        EncryptorInterface $encryptor,
        DateTime $dateTime
    ) {
        $this->userProfileRepository = $userProfileRepository;
        $this->userProfileFactory = $userProfileFactory;
        $this->getUserImage = $getImage;
        $this->encryptor = $encryptor;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $userId, TokenResponseInterface $tokenResponse): void
    {
        $this->userProfileRepository->save(
            $this->updateUserProfile(
                $this->getUserProfile($userId),
                $tokenResponse
            )
        );
    }

    /**
     * Update user profile with the data from token response
     *
     * @param UserProfileInterface $profile
     * @param TokenResponseInterface $response
     * @return UserProfileInterface
     */
    private function updateUserProfile(
        UserProfileInterface $profile,
        TokenResponseInterface $response
    ): UserProfileInterface {
        $profile->setName($response->getName());
        $profile->setEmail($response->getEmail());
        $profile->setImage($this->getUserImage->execute($response->getAccessToken()));
        $profile->setAccessToken($this->encryptor->encrypt($response->getAccessToken()));
        $profile->setRefreshToken($this->encryptor->encrypt($response->getRefreshToken()));
        $profile->setAccessTokenExpiresAt($this->getExpiresTime($response->getExpiresIn()));

        return $profile;
    }

    /**
     * Get user profile entity
     *
     * @param int $userId
     * @return UserProfileInterface
     */
    private function getUserProfile(int $userId): UserProfileInterface
    {
        try {
            return $this->userProfileRepository->getByUserId($userId);
        } catch (NoSuchEntityException $exception) {
            return $this->userProfileFactory->create(
                [
                    'data' => [
                        'admin_user_id' => $userId
                    ]
                ]
            );
        }
    }

    /**
     * Retrieve token expires date
     *
     * @param int $expiresIn
     * @return string
     */
    private function getExpiresTime(int $expiresIn): string
    {
        return $this->dateTime->gmtDate(
            self::DATE_FORMAT,
            $this->dateTime->gmtTimestamp() + (int)round($expiresIn / 1000)
        );
    }
}
