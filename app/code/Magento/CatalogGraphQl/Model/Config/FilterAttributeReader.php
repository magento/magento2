<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Adds custom/eav attributes to product filter type in the GraphQL config.
 *
 * Product Attribute should satisfy the following criteria:
 * - (Attribute is searchable AND "Visible in Advanced Search" is set to "Yes")
 * - OR attribute is "Used in Layered Navigation"
 * - AND Attribute of type "Select" must have options
 */
class FilterAttributeReader implements ReaderInterface
{
    /**
     * Entity type constant
     */
    private const ENTITY_TYPE = 'filter_attributes';

    /**
     * Filter input types
     */
    private const FILTER_EQUAL_TYPE = 'FilterEqualTypeInput';
    private const FILTER_RANGE_TYPE = 'FilterRangeTypeInput';
    private const FILTER_MATCH_TYPE = 'FilterMatchTypeInput';

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $exactMatchAttributes = ['sku'];

    /**
     * @param MapperInterface $mapper
     * @param CollectionFactory $collectionFactory
     * @param array $exactMatchAttributes
     */
    public function __construct(
        MapperInterface $mapper,
        CollectionFactory $collectionFactory,
        array $exactMatchAttributes = []
    ) {
        $this->mapper = $mapper;
        $this->collectionFactory = $collectionFactory;
        $this->exactMatchAttributes = array_merge($this->exactMatchAttributes, $exactMatchAttributes);
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
        $typeNames = $this->mapper->getMappedTypes(self::ENTITY_TYPE);
        $config = [];

        foreach ($this->getFilterAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            foreach ($typeNames as $typeName) {
                $config[$typeName]['fields'][$attributeCode] = [
                    'name' => $attributeCode,
                    'type' => $this->getFilterType($attribute),
                    'arguments' => [],
                    'required' => false,
                    'description' => sprintf('Attribute label: %s', $attribute->getDefaultFrontendLabel())
                ];
            }
        }

        return $config;
    }

    /**
     * Map attribute type to filter type
     *
     * @param Attribute $attribute
     * @return string
     */
    private function getFilterType(Attribute $attribute): string
    {
        if (in_array($attribute->getAttributeCode(), $this->exactMatchAttributes)) {
            return self::FILTER_EQUAL_TYPE;
        }

        $filterTypeMap = [
            'price' => self::FILTER_RANGE_TYPE,
            'date' => self::FILTER_RANGE_TYPE,
            'select' => self::FILTER_EQUAL_TYPE,
            'multiselect' => self::FILTER_EQUAL_TYPE,
            'boolean' => self::FILTER_EQUAL_TYPE,
            'text' => self::FILTER_MATCH_TYPE,
            'textarea' => self::FILTER_MATCH_TYPE,
        ];

        return $filterTypeMap[$attribute->getFrontendInput()] ?? self::FILTER_MATCH_TYPE;
    }

    /**
     * Get attributes to use in product filter input
     *
     * @return array
     */
    private function getFilterAttributes(): array
    {
        $filterableAttributes = $this->collectionFactory
            ->create()
            ->addHasOptionsFilter()
            ->addIsFilterableFilter()
            ->getItems();

        $searchableAttributes = $this->collectionFactory
            ->create()
            ->addHasOptionsFilter()
            ->addIsSearchableFilter()
            ->addDisplayInAdvancedSearchFilter()
            ->getItems();

        return $filterableAttributes + $searchableAttributes;
    }
}
