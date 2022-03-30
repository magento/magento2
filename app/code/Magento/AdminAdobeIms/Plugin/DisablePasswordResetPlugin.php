<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\User\Model\Backend\Config\ObserverConfig;

class DisablePasswordResetPlugin
{
    /** @var ImsConfig */
    private ImsConfig $imsConfig;

    /**
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        ImsConfig $imsConfig
    ) {
        $this->imsConfig = $imsConfig;
    }

    /**
     * Since the password reset module treats 0 as disabled we can just return 0 when our module is enabled
     *
     * @param ObserverConfig $subject
     * @param int $result
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAdminPasswordLifetime(ObserverConfig $subject, int $result): int
    {
        if ($this->imsConfig->enabled() === false) {
            return $result;
        }
        return 0;
    }
}
