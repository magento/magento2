<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Adds passed in attributes to product collection results
 *
 * {@inheritdoc}
 */
class AttributeProcessor implements CollectionProcessorInterface
{
    /**
     * Map GraphQl input fields to product attributes
     *
     * @var array
     */
    private $fieldToAttributeMap = [
        'price_range' => 'price'
    ];

    /**
     * @param array $fieldToAttributeMap
     */
    public function __construct($fieldToAttributeMap = [])
    {
        $this->fieldToAttributeMap = array_merge($this->fieldToAttributeMap, $fieldToAttributeMap);
    }

    /**
     * @inheritdoc
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ): Collection {
        foreach ($attributeNames as $name) {
            $attributeName = $name;
            if (isset($this->fieldToAttributeMap[$name])) {
                $attributeName = $this->fieldToAttributeMap[$name];
            }
            $collection->addAttributeToSelect($attributeName);
        }

        return $collection;
    }
}
