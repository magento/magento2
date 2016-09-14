<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

class RequestGenerator
{
    /** Filter name suffix */
    const FILTER_SUFFIX = '_filter';

    /** Bucket name suffix */
    const BUCKET_SUFFIX = '_bucket';

    /**
     * @var CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * @var GeneratorResolver
     */
    private $generatorResolver;

    /**
     * @param CollectionFactory $productAttributeCollectionFactory
     * @param GeneratorResolver $generatorResolver
     */
    public function __construct(
        CollectionFactory $productAttributeCollectionFactory,
        GeneratorResolver $generatorResolver = null
    ) {
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->generatorResolver = $generatorResolver
            ?: ObjectManager::getInstance()->get(GeneratorResolver::class);
    }

    /**
     * Generate dynamic fields requests
     *
     * @return array
     */
    public function generate()
    {
        $requests = [];
        $requests['catalog_view_container'] =
            $this->generateRequest(EavAttributeInterface::IS_FILTERABLE, 'catalog_view_container', false);
        $requests['quick_search_container'] =
            $this->generateRequest(EavAttributeInterface::IS_FILTERABLE_IN_SEARCH, 'quick_search_container', true);
        $requests['advanced_search_container'] = $this->generateAdvancedSearchRequest();
        return $requests;
    }

    /**
     * Generate search request
     *
     * @param string $attributeType
     * @param string $container
     * @param bool $useFulltext
     * @return array
     */
    private function generateRequest($attributeType, $container, $useFulltext)
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            if ($attribute->getData($attributeType)) {
                if (!in_array($attribute->getAttributeCode(), ['price', 'category_ids'], true)) {
                    $queryName = $attribute->getAttributeCode() . '_query';

                    $request['queries'][$container]['queryReference'][] = [
                        'clause' => 'should',
                        'ref' => $queryName,
                    ];
                    $filterName = $attribute->getAttributeCode() . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [['ref' => $filterName]],
                    ];
                    $bucketName = $attribute->getAttributeCode() . self::BUCKET_SUFFIX;
                    $generator = $this->generatorResolver->getGeneratorForType($attribute->getBackendType());
                    $request['filters'][$filterName] = $generator->getFilterData($attribute, $filterName);
                    $request['aggregations'][$bucketName] = $generator->getAggregationData($attribute, $bucketName);
                }
            }
            /** @var $attribute Attribute */
            if (!$attribute->getIsSearchable() || in_array($attribute->getAttributeCode(), ['price', 'sku'], true)) {
                // Some fields have their own specific handlers
                continue;
            }

            // Match search by custom price attribute isn't supported
            if ($useFulltext && $attribute->getFrontendInput() !== 'price') {
                $request['queries']['search']['match'][] = [
                    'field' => $attribute->getAttributeCode(),
                    'boost' => $attribute->getSearchWeight() ?: 1,
                ];
            }
        }

        return $request;
    }

    /**
     * Retrieve searchable attributes
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected function getSearchableAttributes()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
        $productAttributes = $this->productAttributeCollectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_searchable', 'is_visible_in_advanced_search', 'is_filterable', 'is_filterable_in_search'],
            [1, 1, [1, 2], 1]
        );

        return $productAttributes;
    }

    /**
     * Generate advanced search request
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateAdvancedSearchRequest()
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            /** @var $attribute Attribute */
            if (!$attribute->getIsVisibleInAdvancedSearch()) {
                continue;
            }
            if (in_array($attribute->getAttributeCode(), ['price', 'sku'])) {
                //same fields have special semantics
                continue;
            }

            $queryName = $attribute->getAttributeCode() . '_query';
            $request['queries']['advanced_search_container']['queryReference'][] = [
                'clause' => 'should',
                'ref' => $queryName,
            ];
            switch ($attribute->getBackendType()) {
                case 'static':
                    break;
                case 'text':
                case 'varchar':
                    if ($attribute->getFrontendInput() === 'multiselect') {
                        $filterName = $attribute->getAttributeCode() . self::FILTER_SUFFIX;
                        $request['queries'][$queryName] = [
                            'name' => $queryName,
                            'type' => QueryInterface::TYPE_FILTER,
                            'filterReference' => [['ref' => $filterName]],
                        ];

                        $request['filters'][$filterName] = [
                            'type' => FilterInterface::TYPE_TERM,
                            'name' => $filterName,
                            'field' => $attribute->getAttributeCode(),
                            'value' => '$' . $attribute->getAttributeCode() . '$',
                        ];
                    } else {
                        $request['queries'][$queryName] = [
                            'name' => $queryName,
                            'type' => 'matchQuery',
                            'value' => '$' . $attribute->getAttributeCode() . '$',
                            'match' => [
                                [
                                    'field' => $attribute->getAttributeCode(),
                                    'boost' => $attribute->getSearchWeight() ?: 1,
                                ],
                            ],
                        ];
                    }
                    break;
                case 'decimal':
                case 'datetime':
                case 'date':
                    $filterName = $attribute->getAttributeCode() . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [['ref' => $filterName]],
                    ];
                    $request['filters'][$filterName] = [
                        'field' => $attribute->getAttributeCode(),
                        'name' => $filterName,
                        'type' => FilterInterface::TYPE_RANGE,
                        'from' => '$' . $attribute->getAttributeCode() . '.from$',
                        'to' => '$' . $attribute->getAttributeCode() . '.to$',
                    ];
                    break;
                default:
                    $filterName = $attribute->getAttributeCode() . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [['ref' => $filterName]],
                    ];

                    $request['filters'][$filterName] = [
                        'type' => FilterInterface::TYPE_TERM,
                        'name' => $filterName,
                        'field' => $attribute->getAttributeCode(),
                        'value' => '$' . $attribute->getAttributeCode() . '$',
                    ];
            }
        }

        return $request;
    }
}
