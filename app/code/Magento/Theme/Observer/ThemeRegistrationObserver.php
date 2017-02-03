<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Theme\Model\Theme;

class ThemeRegistrationObserver implements ObserverInterface
{
    /**
     * @var \Magento\Theme\Model\Theme\Registration
     */
    protected $registration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Theme\Registration $registration
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Theme\Model\Theme\Registration $registration,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->registration = $registration;
        $this->logger = $logger;
    }

    /**
     * Theme registration
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $pathPattern = $observer->getEvent()->getPathPattern();
        try {
            $this->registration->register($pathPattern);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        }
        return $this;
    }
}
