<?php
/**
 * Plugin for \Magento\Catalog\Model\Product\Attribute\Repository
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Model\Product\Attribute\Repository;

/**
 * Class \Magento\CatalogInventory\Model\Plugin\FilterCustomAttribute
 *
 * @since 2.0.0
 */
class FilterCustomAttribute
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $blackList;

    /**
     * @param array $blackList
     * @since 2.0.0
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
     * @since 2.0.0
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
