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
     * This plugin is required, because \Magento\User\Model\User::_getEncodedPassword
     * will throw an error, when password is null
     *
     * @param User $subject
     * @param $result
     * @return string
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterGetPassword(User $subject, $result): string
    {
        if ($this->imsConfig->enabled() !== true) {
            return $result;
        }

        return $result ?? '';
    }
}
