<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Captcha\Observer\CheckUserLoginBackendObserver;
use Magento\Framework\Event\Observer;

class CheckUserLoginBackendObserverPlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(ImsConfig $adminImsConfig)
    {
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Disable login captcha when AdminAdobeIMS Module is enabled
     *
     * @param CheckUserLoginBackendObserver $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return CheckUserLoginBackendObserver|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        CheckUserLoginBackendObserver $subject,
        callable $proceed,
        Observer $observer
    ) {
        if (!$this->adminImsConfig->enabled()) {
            return $proceed($observer);
        }
    }
}
