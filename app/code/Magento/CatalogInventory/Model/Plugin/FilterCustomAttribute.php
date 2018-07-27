<?php
/**
 * Plugin for \Magento\Catalog\Model\Product\Attribute\Repository
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Model\Product\Attribute\Repository;

class FilterCustomAttribute
{
    /**
     * @var array
     */
    private $blackList;

    /**
     * @param array $blackList
     */
    public function __construct(array $blackList = [])
    {
        $this->blackList = $blackList;
    }

    /**
     * Delete custom attribute
     *
     * @param Repository $repository
     * @param array $attributes
     * @return \Magento\Eav\Model\AttributeRepository
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomAttributesMetadata(Repository $repository, array $attributes)
    {
        foreach ($attributes as $key => $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->blackList)) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }
}
