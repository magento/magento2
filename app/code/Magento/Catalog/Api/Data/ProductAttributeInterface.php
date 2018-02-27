<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface ProductAttributeInterface extends \Magento\Catalog\Api\Data\EavAttributeInterface
{
    const ENTITY_TYPE_CODE = 'catalog_product';
    const CODE_HAS_WEIGHT = 'product_has_weight';
    const CODE_SPECIAL_PRICE = 'special_price';
    const CODE_PRICE = 'price';
    const CODE_TIER_PRICE_FIELD_PRICE_QTY = 'price_qty';
    const CODE_SHORT_DESCRIPTION = 'short_description';
    const CODE_SEO_FIELD_META_TITLE = 'meta_title';
    const CODE_STATUS = 'status';
    const CODE_NAME = 'name';
    const CODE_SKU = 'sku';
    const CODE_SEO_FIELD_META_KEYWORD = 'meta_keyword';
    const CODE_DESCRIPTION = 'description';
    const CODE_COST = 'cost';
    const CODE_SEO_FIELD_URL_KEY = 'url_key';
    const CODE_TIER_PRICE = 'tier_price';
    const CODE_TIER_PRICE_FIELD_PRICE = 'price';
    const CODE_TIER_PRICE_FIELD_PERCENTAGE_VALUE = 'percentage_value';
    const CODE_TIER_PRICE_FIELD_VALUE_TYPE = 'value_type';
    const CODE_SEO_FIELD_META_DESCRIPTION = 'meta_description';
    const CODE_WEIGHT = 'weight';

    /**
     * @return \Magento\Eav\Api\Data\AttributeExtensionInterface|null
     */
    public function getExtensionAttributes();
}
