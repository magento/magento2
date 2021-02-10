<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;

/**
 * Class TierPriceType validates tier price type.
 */
class TierPriceType extends AbstractImportValidator
{
    /**
     * Validate tier price type.
     *
     * @param array $value
     *
     * @return bool
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
