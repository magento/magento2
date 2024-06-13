<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * Interface ProductAttributeInterface
 * @api
 * @since 100.1.0
 */
interface ProductAttributeInterface extends \Magento\Catalog\Api\Data\ProductAttributeInterface
{
    const CODE_IS_DOWNLOADABLE = 'is_downloadable';
}
