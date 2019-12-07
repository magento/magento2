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
    private $fieldToAttributeMap = [];

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
            $this->addAttribute($collection, $name);
        }

        return $collection;
    }

    /**
     * Add attribute to collection select
     *
     * Add attributes to the collection where graphql fields names don't match attributes names, or if attributes exist
     * on a nested level and they need to be loaded.
     *
     * Format of the attribute can be string or array while array can have different formats.
     * Example: [
     *          'price_range' =>
     *               [
     *                   'price' => 'price',
     *                   'price_type' => 'price_type',
     *               ],
     *           'thumbnail' => //complex array where more than one attribute is needed to compute a value
     *               [
     *                   'label' =>
     *                       [
     *                           'attribute' => 'thumbnail_label', // the actual attribute
     *                           'fallback_attribute' => 'name', //used as default value in case attribute value is null
     *                       ],
     *                   'url' => 'thumbnail',
     *               ]
     *          ]
     *
     * @param Collection $collection
     * @param string $attribute
     */
    private function addAttribute(Collection $collection, string $attribute): void
    {
        if (isset($this->fieldToAttributeMap[$attribute])) {
            $attributeMap = $this->fieldToAttributeMap[$attribute];
            if (is_array($attributeMap)) {
                $this->addAttributeAsArray($collection, $attributeMap);
            } else {
                $collection->addAttributeToSelect($attributeMap);
            }

        } else {
            $collection->addAttributeToSelect($attribute);
        }
    }

    /**
     * Add an array defined attribute to the collection
     *
     * @param Collection $collection
     * @param array $attributeMap
     * @return void
     */
    private function addAttributeAsArray(Collection $collection, array $attributeMap): void
    {
        foreach ($attributeMap as $attribute) {
            if (is_array($attribute)) {
                $this->addAttributeComplexArrayToCollection($collection, $attribute);
            } else {
                $collection->addAttributeToSelect($attribute);
            }
        }
    }

    /**
     * Add a complex array defined attribute to the collection
     *
     * @param Collection $collection
     * @param array $attribute
     * @return void
     */
    private function addAttributeComplexArrayToCollection(Collection $collection, array $attribute): void
    {
        if (isset($attribute['attribute'])) {
            $collection->addAttributeToSelect($attribute['attribute']);
        }
        if (isset($attribute['fallback_attribute'])) {
            $collection->addAttributeToSelect($attribute['fallback_attribute']);
        }
    }
}
