<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * provides limits for checkout functionality.
 */
class CheckoutLimitConfigManager implements LimitConfigManagerInterface
{
    public const REQUEST_TYPE_ID = 'checkout';

    private ScopeConfigInterface $config;

    private LoggerInterface $logger;

    /**
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(ScopeConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function readLimit(ContextInterface $context): LimitConfig
    {
        if ($context->getIdentityType() === ContextInterface::IDENTITY_TYPE_IP) {
            $limit = $this->fetchGuestLimit();
        } else {
            $limit = $this->fetchAuthenticatedLimit();
        }

        return new LimitConfig($limit, $this->fetchPeriod());
    }

    /**
     * Is enforcement enabled for current store?
     *
     * @return bool
     */
    public function isEnforcementEnabled(): bool
    {
        $enabled = $this->config->isSetFlag('checkout/backpressure/enabled', ScopeInterface::SCOPE_STORE);
        if (!$enabled) {
            return false;
        }

        try {
            $this->fetchPeriod();
            $this->fetchAuthenticatedLimit();
            $this->fetchGuestLimit();
        } catch (\RuntimeException $ex) {
            return false;
        }

        return true;
    }

    /**
     * Limit for authenticated customers.
     *
     * @return int
     */
    private function fetchAuthenticatedLimit(): int
    {
        $value = (int) $this->config->getValue('checkout/backpressure/limit', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new \RuntimeException("Invalid checkout backpressure config");
        }

        return $value;
    }

    /**
     * Limit for guests.
     *
     * @return int
     */
    private function fetchGuestLimit(): int
    {
        $value = (int) $this->config->getValue('checkout/backpressure/guest_limit', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new \RuntimeException("Invalid checkout backpressure config");
        }

        return $value;
    }

    /**
     * Counter reset preiod.
     *
     * @return int
     */
    private function fetchPeriod(): int
    {
        $value = (int) $this->config->getValue('checkout/backpressure/period', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new \RuntimeException("Invalid checkout backpressure config");
        }

        return $value;
    }
}
