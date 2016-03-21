<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 */
interface CategoryAttributeInterface extends \Magento\Catalog\Api\Data\EavAttributeInterface
{
    const ENTITY_TYPE_CODE = 'catalog_category';
}
