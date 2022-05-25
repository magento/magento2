<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;

class BackendAuthSessionPlugin
{
    /**
     * How often access_token has to be validated
     */
    public const ACCESS_TOKEN_INTERVAL_CHECK = 600;

    /**
     * @var ImsConnection
     */
    private ImsConnection $adminImsConnection;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConnection $adminImsConnection
     * @param DateTime $dateTime
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(
        ImsConnection $adminImsConnection,
        DateTime $dateTime,
        ImsConfig $adminImsConfig
    ) {
        $this->adminImsConnection = $adminImsConnection;
        $this->dateTime = $dateTime;
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Check if access token still valid
     *
     * @param Session $subject
     * @param callable $proceed
     * @return void
     * @throws \Magento\Framework\Exception\AuthorizationException
     */
    public function aroundProlong(Session $subject, callable $proceed): void
    {
        if ($this->adminImsConfig->enabled()) {
            $lastCheckTime = $subject->getTokenLastCheckTime();
            if ($lastCheckTime + self::ACCESS_TOKEN_INTERVAL_CHECK <= $this->dateTime->gmtTimestamp()) {
                $accessToken = $subject->getAdobeAccessToken();
                if ($this->adminImsConnection->validateToken($accessToken)) {
                    $subject->setTokenLastCheckTime($this->dateTime->gmtTimestamp());
                } else {
                    $subject->destroy();
                    return;
                }
            }
        }

        $proceed();
    }
}
