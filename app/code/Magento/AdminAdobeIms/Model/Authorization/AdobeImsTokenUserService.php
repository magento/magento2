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
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdobeImsTokenUserService
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private const ACCESS_TOKEN_INTERVAL_CHECK = 600;

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
    private ImsConnection $adminImsConnection;

    /**
     * @var ImsWebapiRepositoryInterface
     */
    private ImsWebapiRepositoryInterface $imsWebapiRepository;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param TokenReaderInterface $tokenReader
     * @param ImsWebapiRepositoryInterface $imsWebapiRepository
     * @param ImsWebapiInterfaceFactory $imsWebapiFactory
     * @param User $adminUser
     * @param ImsConnection $adminImsConnection
     * @param EncryptorInterface $encryptor
     * @param DateTime $dateTime
     */
    public function __construct(
        TokenReaderInterface $tokenReader,
        ImsWebapiRepositoryInterface $imsWebapiRepository,
        ImsWebapiInterfaceFactory $imsWebapiFactory,
        User $adminUser,
        ImsConnection $adminImsConnection,
        EncryptorInterface $encryptor,
        DateTime $dateTime
    ) {
        $this->tokenReader = $tokenReader;
        $this->imsWebapiFactory = $imsWebapiFactory;
        $this->adminUser = $adminUser;
        $this->adminImsConnection = $adminImsConnection;
        $this->imsWebapiRepository = $imsWebapiRepository;
        $this->encryptor = $encryptor;
        $this->dateTime = $dateTime;
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
     * @throws NoSuchEntityException
     */
    public function getAdminUserIdByToken(string $bearerToken): int
    {
        $imsWebapiEntity = $this->imsWebapiRepository->getByAccessTokenHash(
            $this->encryptor->getHash($bearerToken)
        );
        $this->validateToken($bearerToken, $imsWebapiEntity);
        $dataFromToken = $this->tokenReader->read($bearerToken);

        if ($imsWebapiEntity->getId()) {
            $adminUserId = $imsWebapiEntity->getAdminUserId();
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
            $profile['created_at'] = $dataFromToken['created_at'] ?? 0;
            $profile['expires_in'] = $dataFromToken['expires_in'] ?? 0;

            $imsWebapiInterface = $this->createImsWebapiInterface($adminUserId);
            $this->imsWebapiRepository->save($this->setImsWebapiData($imsWebapiInterface, $profile));
        }

        return $adminUserId;
    }

    /**
     * Always validate new tokens and validate existing token with interval
     *
     * @param string $token
     * @param ImsWebapiInterface $imsWebapiEntity
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws CouldNotSaveException
     */
    private function validateToken(string $token, ImsWebapiInterface $imsWebapiEntity)
    {
        $isTokenValid = true;
        if ($imsWebapiEntity->getId()) {
            $lastCheckTimestamp = $this->dateTime->gmtTimestamp($imsWebapiEntity->getLastCheckTime());
            if (($lastCheckTimestamp + self::ACCESS_TOKEN_INTERVAL_CHECK) <= $this->dateTime->gmtTimestamp()) {
                $isTokenValid = $this->adminImsConnection->validateToken($token);
                $imsWebapiEntity->setLastCheckTime($this->dateTime->gmtDate(self::DATE_FORMAT));
                $this->imsWebapiRepository->save($imsWebapiEntity);
            }
        } else {
            $isTokenValid = $this->adminImsConnection->validateToken($token);
        }

        if (!$isTokenValid) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }
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
            return $this->adminImsConnection->getProfile($bearerToken);
        } catch (\Exception $exception) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }
    }

    /**
     * Create new ims webapi entity
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
     * Update admin adobe ims webapi entity
     *
     * @param ImsWebapiInterface $imsWebapiInterface
     * @param array $profile
     * @return ImsWebapiInterface
     */
    private function setImsWebapiData(
        ImsWebapiInterface $imsWebapiInterface,
        array $profile
    ): ImsWebapiInterface {
        $imsWebapiInterface->setAccessTokenHash($this->encryptor->getHash($profile['access_token']));
        $imsWebapiInterface->setAccessToken($this->encryptor->encrypt($profile['access_token']));
        $imsWebapiInterface->setLastCheckTime($this->dateTime->gmtDate(self::DATE_FORMAT));
        $imsWebapiInterface->setAccessTokenExpiresAt(
            $this->getExpiresTime($profile['created_at'], $profile['expires_in'])
        );

        return $imsWebapiInterface;
    }

    /**
     * Retrieve token expires date
     *
     * @param int $createdAt
     * @param int $expiresIn
     * @return string
     */
    private function getExpiresTime(int $createdAt, int $expiresIn): string
    {
        return $this->dateTime->gmtDate(
            self::DATE_FORMAT,
            round(($createdAt + $expiresIn) / 1000)
        );
    }
}
