<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api\Data;

interface ProductAttributeInterface extends \Magento\Catalog\Api\Data\EavAttributeInterface
{
    const ENTITY_TYPE_CODE = 'catalog_product';

    const DEFAULT_ATTRIBUTE_SET_ID = 4;
}
