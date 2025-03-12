<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Coupon
{
    public const XML_PATH_PROMO_GRAPHQL_SHARE_ALL_RULES = 'promo/graphql/share_all_sales_rule';
    public const XML_PATH_PROMO_GRAPHQL_SHARE_APPLIED_RULES = 'promo/graphql/share_applied_sales_rule';

    /**
     * Coupon Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Get share all sales rule flag value
     *
     * @return bool
     */
    public function isShareAllSalesRulesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PROMO_GRAPHQL_SHARE_ALL_RULES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get share currently applied sales rule flag value
     *
     * @return bool
     */
    public function isShareAppliedSalesRulesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PROMO_GRAPHQL_SHARE_APPLIED_RULES,
            ScopeInterface::SCOPE_STORE
        );
    }
}
