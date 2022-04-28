<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;

class AdminReauthProcessService extends AbstractAdminBaseProcessService
{
    /**
     * Handle Adobe reAuth
     *
     * @param TokenResponseInterface $tokenResponse
     * @param array $profile
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function execute(TokenResponseInterface $tokenResponse, array $profile = []): void
    {
        try {
            $session = $this->auth->getAuthStorage();
            $session->setAdobeReAuthToken($tokenResponse->getAccessToken());
            $session->setReAuthTokenLastCheckTime($this->dateTime->gmtTimestamp());
        } catch (Exception $exception) {
            $this->externalLogout($tokenResponse->getAccessToken());
            throw new AdobeImsAuthorizationException(
                __($exception->getMessage())
            );
        }
    }
}
