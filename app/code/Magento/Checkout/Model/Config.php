<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public const CART_PREFERENCE_CUSTOMER = "customer";
    public const CART_PREFERENCE_GUEST = "guest";
    private const XML_PATH_CART_MERGE_PREFERENCE = 'checkout/cart/cart_merge_preference';

    /**
     * Config Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Get Cart Merge Preference config to update cart quantities
     *
     * @return string
     */
    public function getCartMergePreference(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CART_MERGE_PREFERENCE);
    }
}
