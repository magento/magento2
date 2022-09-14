<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\User\Model\Backend\Config\ObserverConfig;

class DisableForcedPasswordChangePlugin
{
    /** @var ImsConfig */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(
        ImsConfig $adminImsConfig
    ) {
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Disable forced password change when our module is active
     *
     * @param ObserverConfig $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsPasswordChangeForced(ObserverConfig $subject, bool $result): bool
    {
        if ($this->adminImsConfig->enabled() === false) {
            return $result;
        }
        return false;
    }
}
