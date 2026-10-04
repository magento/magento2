<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model;

use Magento\Catalog\Model\Config as Subject;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;

/**
 * Plugin to ensure special_price attribute is always loaded in product listings even when used_in_product_listing = 0
 */
class ConfigPlugin
{
    private const SPECIAL_PRICE_ATTR_CODE = 'special_price';

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(
        private readonly EavConfig $eavConfig
    ) {
    }

    /**
     * Add special_price attribute to the list of attributes used in product listing
     *
     * @param Subject $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributesUsedInProductListing(Subject $subject, array $result): array
    {
        // Check if special_price is already in the result
        if (!isset($result[self::SPECIAL_PRICE_ATTR_CODE])) {
            // Get the special_price attribute
            $specialPriceAttribute = $this->eavConfig->getAttribute(
                Product::ENTITY,
                self::SPECIAL_PRICE_ATTR_CODE
            );

            if ($specialPriceAttribute && $specialPriceAttribute->getId()) {
                // Add it to the result
                $result[self::SPECIAL_PRICE_ATTR_CODE] = $specialPriceAttribute;
            }
        }
        return $result;
    }
}
