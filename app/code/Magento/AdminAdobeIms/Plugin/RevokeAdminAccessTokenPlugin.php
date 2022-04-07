<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Exception;
use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\LogOut;
use Magento\AdminAdobeIms\Model\UserProfileRepository;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\AdminTokenService;

class RevokeAdminAccessTokenPlugin
{
    /**
     * @var LogOut
     */
    private LogOut $logOut;

    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var UserProfileRepository
     */
    private UserProfileRepository $userProfileRepository;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @param LogOut $logOut
     * @param ImsConfig $imsConfig
     * @param UserProfileRepository $userProfileRepository
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        LogOut $logOut,
        ImsConfig $imsConfig,
        UserProfileRepository $userProfileRepository,
        EncryptorInterface $encryptor
    ) {
        $this->logOut = $logOut;
        $this->imsConfig = $imsConfig;
        $this->userProfileRepository = $userProfileRepository;
        $this->encryptor = $encryptor;
    }

    /**
     * Get access_token from session and logout user from Adobe IMS
     *
     * @param AdminTokenService $subject
     * @param bool $result
     * @param int $adminId
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRevokeAdminAccessToken(
        AdminTokenService $subject,
        bool $result,
        int $adminId
    ): bool {

        if ($this->imsConfig->enabled() !== true) {
            return $result;
        }

        try {
            $entity = $this->userProfileRepository->getByUserId($adminId);
            $this->logOut->execute(
                $this->encryptor->decrypt($entity->getAccessToken()),
                $adminId
            );
        } catch (Exception $exception) {
            throw new LocalizedException(__('The tokens couldn\'t be revoked.'), $exception);
        }

        return $result;
    }
}
