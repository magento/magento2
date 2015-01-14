<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_INVALID_SCOPE = 'invalidScope';

    const ERROR_INVALID_WEBSITE = 'invalidWebsite';

    const ERROR_INVALID_STORE = 'invalidStore';

    const ERROR_INVALID_ATTR_SET = 'invalidAttrSet';

    const ERROR_INVALID_TYPE = 'invalidType';

    const ERROR_INVALID_CATEGORY = 'invalidCategory';

    const ERROR_VALUE_IS_REQUIRED = 'isRequired';

    const ERROR_TYPE_CHANGED = 'typeChanged';

    const ERROR_SKU_IS_EMPTY = 'skuEmpty';

    const ERROR_NO_DEFAULT_ROW = 'noDefaultRow';

    const ERROR_CHANGE_TYPE = 'changeProductType';

    const ERROR_DUPLICATE_SCOPE = 'duplicateScope';

    const ERROR_DUPLICATE_SKU = 'duplicateSKU';

    const ERROR_CHANGE_ATTR_SET = 'changeAttrSet';

    const ERROR_TYPE_UNSUPPORTED = 'productTypeUnsupported';

    const ERROR_ROW_IS_ORPHAN = 'rowIsOrphan';

    const ERROR_INVALID_TIER_PRICE_QTY = 'invalidTierPriceOrQty';

    const ERROR_INVALID_TIER_PRICE_SITE = 'tierPriceWebsiteInvalid';

    const ERROR_INVALID_TIER_PRICE_GROUP = 'tierPriceGroupInvalid';

    const ERROR_TIER_DATA_INCOMPLETE = 'tierPriceDataIsIncomplete';

    const ERROR_INVALID_GROUP_PRICE_SITE = 'groupPriceWebsiteInvalid';

    const ERROR_INVALID_GROUP_PRICE_GROUP = 'groupPriceGroupInvalid';

    const ERROR_GROUP_PRICE_DATA_INCOMPLETE = 'groupPriceDataIsIncomplete';

    const ERROR_SKU_NOT_FOUND_FOR_DELETE = 'skuNotFoundToDelete';

    const ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND = 'superProductsSkuNotFound';

    const ERROR_MEDIA_DATA_INCOMPLETE = 'mediaDataIsIncomplete';

    const ERROR_INVALID_WEIGHT = 'invalidWeight';

    /**
     * Value that means all entities (e.g. websites, groups etc.)
     */
    const VALUE_ALL = 'all';

    /**
     * Initialize validator
     *
     * @return $this
     */
    public function init();
}
