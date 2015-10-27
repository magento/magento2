<?php
/**
 * Plugin for \Magento\Catalog\Model\Product\Attribute\Repository
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin\Api;

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
    public function __construct($blackList = ['quantity_and_stock_status'])
    {
        $this->blackList = $blackList;
    }

    /**
     * Delete custom attribute from api response
     *
     * @param Repository $repository
     * @param $attributes
     * @return \Magento\Eav\Model\AttributeRepository
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomAttributesMetadata(Repository $repository, $attributes)
    {
        foreach ($attributes as $key => $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->blackList)) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }
}
