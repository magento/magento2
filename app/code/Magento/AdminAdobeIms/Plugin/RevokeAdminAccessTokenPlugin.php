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
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\AdminTokenService;

class RevokeAdminAccessTokenPlugin
{
    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var LogOut
     */
    private LogOut $logOut;

    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @param Auth $auth
     * @param LogOut $logOut
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        Auth $auth,
        LogOut $logOut,
        ImsConfig $imsConfig
    ) {
        $this->auth = $auth;
        $this->logOut = $logOut;
        $this->imsConfig = $imsConfig;
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
            $session = $this->auth->getAuthStorage();
            $accessToken = $session->getAdobeAccessToken();

            $this->logOut->execute($accessToken);
        } catch (Exception $exception) {
            throw new LocalizedException(__('The tokens couldn\'t be revoked.'), $exception);
        }

        return $result;
    }
}
