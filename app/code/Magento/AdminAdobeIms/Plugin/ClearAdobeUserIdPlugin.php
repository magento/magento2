<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;

class ClearAdobeUserIdPlugin
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
     * When access_token will be set with an empty value, also clear the adobe_user_id value
     *
     * @param UserProfileInterface $subject
     * @param void $result
     * @param string $value
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetAccessToken(UserProfileInterface $subject, $result, string $value): void
    {
        if ($this->imsConfig->enabled() === true
            && $value === ''
        ) {
            $subject->setData('adobe_user_id');
        }
    }
}
