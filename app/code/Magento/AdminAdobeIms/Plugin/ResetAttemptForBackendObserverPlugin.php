<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Captcha\Observer\ResetAttemptForBackendObserver;
use Magento\Framework\Event\Observer;

class ResetAttemptForBackendObserverPlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @param ImsConfig $imsConfig
     */
    public function __construct(ImsConfig $imsConfig)
    {
        $this->imsConfig = $imsConfig;
    }

    /**
     * @param ResetAttemptForBackendObserver $subject
     * @param callable $proceed
     * @param Observer $observer
     */
    public function aroundExecute(ResetAttemptForBackendObserver $subject, callable $proceed, Observer $observer)
    {
        if (!$this->imsConfig->enabled()) {
            return $proceed($observer);
        }
    }
}