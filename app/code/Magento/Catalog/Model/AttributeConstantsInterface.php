<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

/**
 * Interface AttributeConstantsInterface
 */
interface AttributeConstantsInterface
{
    const CODE_NAME = 'name';
    const CODE_PRICE = 'price';
    const CODE_WEIGHT = 'weight';
    const CODE_HAS_WEIGHT = 'product_has_weight';
    const CODE_IS_DOWNLOADABLE = 'is_downloadable';
    const CODE_STATUS = 'status';
    const CODE_DESCRIPTION = 'description';
    const CODE_SHORT_DESCRIPTION = 'short_description';
    const CODE_SPECIAL_PRICE = 'special_price';
    const CODE_COST = 'cost';
    const CODE_TIER_PRICE = 'tier_price';
    const CODE_TIER_PRICE_FIELD_PRICE = 'price';
    const CODE_TIER_PRICE_FIELD_PRICE_QTY = 'price_qty';
    const CODE_SKU = 'sku';
    const CODE_SEO_FIELD_URL_KEY = 'url_key';
    const CODE_SEO_FIELD_META_TITLE = 'meta_title';
    const CODE_SEO_FIELD_META_KEYWORD = 'meta_keyword';
    const CODE_SEO_FIELD_META_DESCRIPTION = 'meta_description';
}
