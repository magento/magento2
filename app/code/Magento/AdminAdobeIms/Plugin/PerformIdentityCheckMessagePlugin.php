<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Exception\AuthenticationException;
use Magento\User\Model\User;

class PerformIdentityCheckMessagePlugin
{
    /**
     * @var ImsConfig
     */
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
     * Change Exception message when performIdentityCheck fails
     *
     * @param User $subject
     * @param callable $proceed
     * @param string $passwordString
     * @return mixed
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPerformIdentityCheck(User $subject, callable $proceed, string $passwordString)
    {
        if ($this->adminImsConfig->enabled() === false) {
            return $proceed($passwordString);
        }

        try {
            return $proceed($passwordString);
        } catch (AuthenticationException $exception) {
            throw new AuthenticationException(
                __('Please perform the AdobeIms reAuth and try again.')
            );
        }
    }
}
