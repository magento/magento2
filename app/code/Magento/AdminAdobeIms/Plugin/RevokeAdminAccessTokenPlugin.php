<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Exception;
use Magento\AdminAdobeIms\Api\ImsTokenRepositoryInterface;
use Magento\AdminAdobeIms\Model\LogOut;
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
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var ImsTokenRepositoryInterface
     */
    private ImsTokenRepositoryInterface $imsWebApiRepository;

    /**
     * @param LogOut $logOut
     * @param ImsConfig $imsConfig
     * @param ImsTokenRepositoryInterface $imsWebApiRepository
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        LogOut $logOut,
        ImsConfig $imsConfig,
        ImsTokenRepositoryInterface $imsWebApiRepository,
        EncryptorInterface $encryptor
    ) {
        $this->logOut = $logOut;
        $this->imsConfig = $imsConfig;
        $this->encryptor = $encryptor;
        $this->imsWebApiRepository = $imsWebApiRepository;
    }

    /**
     * Get access token(s) by admin id and logout user from Adobe IMS
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
            $entities = $this->imsWebApiRepository->getByAdminId($adminId);
            foreach ($entities as $entity) {
                // TODO
                $this->logOut->execute(
                    $this->encryptor->decrypt($entity->getAccessTokenHash()),
                    $adminId
                );
            }
        } catch (Exception $exception) {
            throw new LocalizedException(__('The tokens couldn\'t be revoked.'), $exception);
        }

        return $result;
    }
}
