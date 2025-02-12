<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributesCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributesCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;

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
     * @var AttributesCollectionFactory
     */
    private $attributesCollectionFactory;

    /**
     * @param MapperInterface $mapper
     * @param AttributesCollection $attributesCollection @deprecated @see $attributesCollectionFactory
     * @param AttributesCollectionFactory|null $attributesCollectionFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        MapperInterface $mapper,
        AttributesCollection $attributesCollection,
        ?AttributesCollectionFactory $attributesCollectionFactory = null
    ) {
        $this->mapper = $mapper;
        $this->attributesCollectionFactory = $attributesCollectionFactory
            ?? ObjectManager::getInstance()->get(AttributesCollectionFactory::class);
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
        $attributes = $this->attributesCollectionFactory->create()
            ->addSearchableAttributeFilter()->addFilter('used_for_sort_by', 1);
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
