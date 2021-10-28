<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides backpressure limits for ordering.
 */
class OrderLimitConfigManager implements LimitConfigManagerInterface
{
    public const REQUEST_TYPE_ID = 'quote-order';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
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
        $enabled = $this->config->isSetFlag('sales/backpressure/enabled', ScopeInterface::SCOPE_STORE);
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
        $value = (int) $this->config->getValue('sales/backpressure/limit', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new \RuntimeException("Invalid order backpressure config");
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
        $value = (int) $this->config->getValue('sales/backpressure/guest_limit', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new \RuntimeException("Invalid order backpressure config");
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
        $value = (int) $this->config->getValue('sales/backpressure/period', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new \RuntimeException("Invalid order backpressure config");
        }

        return $value;
    }
}
