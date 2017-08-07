<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

/**
 * Class TierPriceType validates tier price type.
 * @since 2.2.0
 */
class TierPriceType extends \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * Validate tier price type.
     *
     * @param array $value
     * @return bool
     * @since 2.2.0
     */
    public function isValid($value)
    {
        $isValid = true;

        if (isset($value[AdvancedPricing::COL_TIER_PRICE_TYPE])
            && !empty($value[AdvancedPricing::COL_TIER_PRICE_TYPE])
            && !in_array(
                $value[AdvancedPricing::COL_TIER_PRICE_TYPE],
                [AdvancedPricing::TIER_PRICE_TYPE_FIXED, AdvancedPricing::TIER_PRICE_TYPE_PERCENT]
            )
        ) {
            $this->_addMessages([RowValidatorInterface::ERROR_INVALID_TIER_PRICE_TYPE]);
            $isValid = false;
        }

        return $isValid;
    }
}
