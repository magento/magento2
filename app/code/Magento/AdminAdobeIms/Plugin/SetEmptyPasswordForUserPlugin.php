<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Exception;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\User\Model\User;

class SetEmptyPasswordForUserPlugin
{
    /**
     * @var ImsConfig
     */
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
     * Return current password or at least empty string and not null
     *
     * This plugin is required, because \Magento\User\Model\User::_getEncodedPassword
     * will throw an error, when password is null
     *
     * @param User $subject
     * @param string|null $result
     * @return string|null
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetPassword(User $subject, ?string $result): ?string
    {
        if ($this->imsConfig->enabled() !== true) {
            return $result;
        }

        return $result ?? '';
    }
}
