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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\RuntimeException;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides backpressure limits for ordering
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
     *
     * @throws RuntimeException
     */
    public function readLimit(ContextInterface $context): LimitConfig
    {
        switch ($context->getIdentityType()) {
            case ContextInterface::IDENTITY_TYPE_ADMIN:
            case ContextInterface::IDENTITY_TYPE_CUSTOMER:
                $limit = $this->fetchAuthenticatedLimit();
                break;
            case ContextInterface::IDENTITY_TYPE_IP:
                $limit = $this->fetchGuestLimit();
                break;
            default:
                throw new RuntimeException(__("Identity type not found"));
        }

        return new LimitConfig($limit, $this->fetchPeriod());
    }

    /**
     * Checks if enforcement enabled for the current store
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
        } catch (RuntimeException $ex) {
            return false;
        }

        return true;
    }

    /**
     * Limit for authenticated customers
     *
     * @return int
     * @throws RuntimeException
     */
    private function fetchAuthenticatedLimit(): int
    {
        $value = (int)$this->config->getValue('sales/backpressure/limit', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new RuntimeException(__("Invalid order backpressure limit config"));
        }

        return $value;
    }

    /**
     * Limit for guests
     *
     * @return int
     * @throws RuntimeException
     */
    private function fetchGuestLimit(): int
    {
        $value = (int)$this->config->getValue(
            'sales/backpressure/guest_limit',
            ScopeInterface::SCOPE_STORE
        );
        if ($value <= 0) {
            throw new RuntimeException(__("Invalid order backpressure guest limit config"));
        }

        return $value;
    }

    /**
     * Counter reset period
     *
     * @return int
     * @throws RuntimeException
     */
    private function fetchPeriod(): int
    {
        $value = (int)$this->config->getValue('sales/backpressure/period', ScopeInterface::SCOPE_STORE);
        if ($value <= 0) {
            throw new RuntimeException(__("Invalid order backpressure counter reset period config"));
        }

        return $value;
    }
}
