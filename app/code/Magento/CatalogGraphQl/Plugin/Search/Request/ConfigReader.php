<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\Plugin\Search\Request;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

/**
 * Add search request configuration to config for give ability filter and search products during GraphQL request
 * Add 2 request name with and without aggregation correspondingly:
 * - graphql_product_search_with_aggregation
 * - graphql_product_search
 *
 * @see Magento/CatalogGraphQl/etc/search_request.xml
 */
class ConfigReader
{
    /**
     * @var string
     */
    private $requestNameWithAggregation = 'graphql_product_search_with_aggregation';

    /**
     * @var string
     */
    private $requestName = 'graphql_product_search';

    /**
     * @var GeneratorResolver
     */
    private $generatorResolver;

    /**
     * @var CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /** Bucket name suffix */
    private const BUCKET_SUFFIX = '_bucket';

    /**
     * @param GeneratorResolver $generatorResolver
     * @param CollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(
        GeneratorResolver $generatorResolver,
        CollectionFactory $productAttributeCollectionFactory
    ) {
        $this->generatorResolver = $generatorResolver;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
    }

    /**
     * Merge reader's value with generated
     *
     * @param \Magento\Framework\Config\ReaderInterface $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(
        \Magento\Framework\Config\ReaderInterface $subject,
        array $result
    ) {
        $searchRequestNameWithAggregation = $this->generateRequest();
        $searchRequest = $searchRequestNameWithAggregation;
        $searchRequest['queries'][$this->requestName] = $searchRequest['queries'][$this->requestNameWithAggregation];
        unset($searchRequest['queries'][$this->requestNameWithAggregation], $searchRequest['aggregations']);

        return array_merge_recursive(
            $result,
            [
                $this->requestNameWithAggregation => $searchRequestNameWithAggregation,
                $this->requestName => $searchRequest,
            ]
        );
    }

    /**
     * Retrieve searchable attributes
     *
     * @return \Magento\Eav\Model\Entity\Attribute[]
     */
    private function getSearchableAttributes(): array
    {
        $attributes = [];
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
        $productAttributes = $this->productAttributeCollectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_searchable', 'is_visible_in_advanced_search', 'is_filterable', 'is_filterable_in_search'],
            [1, 1, [1, 2], 1]
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($productAttributes->getItems() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        return $attributes;
    }

    /**
     * Generate search request for search products via GraphQL
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateRequest()
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            if (\in_array($attribute->getAttributeCode(), ['price', 'visibility', 'category_ids'])) {
                //some fields have special semantics
                continue;
            }
            $queryName = $attribute->getAttributeCode() . '_query';
            $request['queries'][$this->requestNameWithAggregation]['queryReference'][] = [
                'clause' => 'must',
                'ref' => $queryName,
            ];
            switch ($attribute->getBackendType()) {
                case 'static':
                case 'text':
                case 'varchar':
                    if ($attribute->getFrontendInput() === 'multiselect') {
                        $filterName = $attribute->getAttributeCode() . RequestGenerator::FILTER_SUFFIX;
                        $request['queries'][$queryName] = [
                            'name' => $queryName,
                            'type' => QueryInterface::TYPE_FILTER,
                            'filterReference' => [
                                [
                                    'ref' => $filterName,
                                ],
                            ],
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
                    $filterName = $attribute->getAttributeCode() . RequestGenerator::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [
                            [
                                'ref' => $filterName,
                            ],
                        ],
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
                    $filterName = $attribute->getAttributeCode() . RequestGenerator::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [
                            [
                                'ref' => $filterName,
                            ],
                        ],
                    ];
                    $request['filters'][$filterName] = [
                        'type' => FilterInterface::TYPE_TERM,
                        'name' => $filterName,
                        'field' => $attribute->getAttributeCode(),
                        'value' => '$' . $attribute->getAttributeCode() . '$',
                    ];
            }
            $generator = $this->generatorResolver->getGeneratorForType($attribute->getBackendType());

            if ($attribute->getData(EavAttributeInterface::IS_FILTERABLE)) {
                $bucketName = $attribute->getAttributeCode() . self::BUCKET_SUFFIX;
                $request['aggregations'][$bucketName] = $generator->getAggregationData($attribute, $bucketName);
            }

            $this->addSearchAttributeToFullTextSearch($attribute, $request);
        }

        return $request;
    }

    /**
     * Add attribute with specified boost to "search" query used in full text search
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param array $request
     * @return void
     */
    private function addSearchAttributeToFullTextSearch(\Magento\Eav\Model\Entity\Attribute $attribute, &$request): void
    {
        // Match search by custom price attribute isn't supported
        if ($attribute->getFrontendInput() !== 'price') {
            $request['queries']['search']['match'][] = [
                'field' => $attribute->getAttributeCode(),
                'boost' => $attribute->getSearchWeight() ?: 1,
            ];
        }
    }
}
