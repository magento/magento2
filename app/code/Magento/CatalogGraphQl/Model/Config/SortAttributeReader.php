<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributesCollection;

/**
 * Adds custom/eav attribute to catalog products sorting in the GraphQL config.
 */
class SortAttributeReader implements ReaderInterface
{
    /**
     * Entity type constant
     */
    private const ENTITY_TYPE = 'sort_attributes';

    /**
     * Fields type constant
     */
    private const FIELD_TYPE = 'SortEnum';

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var AttributesCollection
     */
    private $attributesCollection;

    /**
     * @param MapperInterface $mapper
     * @param AttributesCollection $attributesCollection
     */
    public function __construct(
        MapperInterface $mapper,
        AttributesCollection $attributesCollection
    ) {
        $this->mapper = $mapper;
        $this->attributesCollection = $attributesCollection;
    }

    /**
     * Read configuration scope
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null) : array
    {
        $map = $this->mapper->getMappedTypes(self::ENTITY_TYPE);
        $config =[];
        $attributes = $this->attributesCollection->addSearchableAttributeFilter()->addFilter('used_for_sort_by', 1);
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributeLabel = $attribute->getDefaultFrontendLabel();
            foreach ($map as $type) {
                $config[$type]['fields'][$attributeCode] = [
                    'name' => $attributeCode,
                    'type' => self::FIELD_TYPE,
                    'arguments' => [],
                    'required' => false,
                    'description' => __('Attribute label: ') . $attributeLabel
                ];
            }
        }

        return $config;
    }
}
