<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\GetAccessTokenInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Represent the get user access token functionality
 */
class GetAccessToken implements GetAccessTokenInterface
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
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param UserContextInterface $userContext
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        UserContextInterface $userContext,
        UserProfileRepositoryInterface $userProfileRepository,
        EncryptorInterface $encryptor
    ) {
        $this->userContext = $userContext;
        $this->userProfileRepository = $userProfileRepository;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): ?string
    {
        try {
            $adminUserId = $adminUserId ?? (int) $this->userContext->getUserId();
            return $this->encryptor->decrypt(
                $this->userProfileRepository->getByUserId($adminUserId)->getAccessToken()
            );
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}
