<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const SHARE_APPLIED_CART_RULES = 'promo/graphql/share_applied_cart_rule';

    /**
     * Config Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Get share currently applied cart rule flag value
     *
     * @return bool
     */
    public function isShareAppliedCartRulesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::SHARE_APPLIED_CART_RULES, ScopeInterface::SCOPE_STORE);
    }
}
