<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory;
use Magento\Framework\Search\Request\BucketInterface;

class RequestGenerator
{
    /**
     * @var CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * @param CollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(CollectionFactory $productAttributeCollectionFactory)
    {
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
    }

    /**
     * Generate dynamic fields requests
     *
     * @return array
     */
    public function generate()
    {
        $requests = [];
        $requests['quick_search_container'] = $this->generateQuickSearchRequest();
        $requests['advanced_search_container'] = $this->generateAdvancedSearchRequest();
        return $requests;
    }

    /**
     * Generate quick search request
     *
     * @return array
     */
    private function generateQuickSearchRequest()
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            if ($attribute->getIsFilterable()) {
                if (!in_array($attribute->getAttributeCode(), ['price', 'category_ids'])) {

                    $queryName = $attribute->getAttributeCode() . '_query';

                    $request['queries']['quick_search_container']['queryReference'][] = [
                        'clause' => 'should',
                        'ref' => $queryName,
                    ];
                    $filterName = $attribute->getAttributeCode() . '_filter';
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => 'filteredQuery',
                        'filterReference' => [['ref' => $filterName]]
                    ];
                    $bucketName = $attribute->getAttributeCode() . '_bucket';
                    if ($attribute->getBackendType() == 'decimal') {
                        $request['filters'][$filterName] = [
                            'type' => 'rangeFilter',
                            'name' => $filterName,
                            'field' => $attribute->getAttributeCode(),
                            'from' => '$' . $attribute->getAttributeCode() . '.from$',
                            'to' => '$' . $attribute->getAttributeCode() . '.to$',
                        ];
                        $request['aggregations'][$bucketName] = [
                            'type' => BucketInterface::TYPE_DYNAMIC,
                            'name' => $bucketName,
                            'field' => $attribute->getAttributeCode(),
                            'method' => 'manual',
                            'metric' => [["type" => "count"]],
                        ];
                    } else {
                        $request['filters'][$filterName] = [
                            'type' => 'termFilter',
                            'name' => $filterName,
                            'field' => $attribute->getAttributeCode(),
                            'value' => '$' . $attribute->getAttributeCode() . '$',
                        ];
                        $request['aggregations'][$bucketName] = [
                            'type' => 'termBucket',
                            'name' => $bucketName,
                            'field' => $attribute->getAttributeCode(),
                            'metric' => [["type" => "count"]],
                        ];
                    }
                }
            }
            /** @var $attribute Attribute */
            if (in_array($attribute->getAttributeCode(), ['price', 'sku'])
                || !$attribute->getIsSearchable()
            ) {
                //same fields have special semantics
                continue;
            }
            $request['queries']['search']['match'][] = [
                'field' => $attribute->getAttributeCode(),
                'boost' => $attribute->getSearchWeight() ?: 1,
            ];
        }
        return $request;
    }

    /**
     * Retrieve searchable attributes
     *
     * @return \Magento\Catalog\Model\Entity\Attribute[]
     */
    protected function getSearchableAttributes()
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection $productAttributes */
        $productAttributes = $this->productAttributeCollectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_searchable', 'is_visible_in_advanced_search', 'is_filterable'],
            [1, 1, 1]
        );

        return $productAttributes;
    }

    /**
     * Generate advanced search request
     *
     * @return array
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
                        $filterName = $attribute->getAttributeCode() . '_filter';
                        $request['queries'][$queryName] = [
                            'name' => $queryName,
                            'type' => 'filteredQuery',
                            'filterReference' => [['ref' => $filterName]]
                        ];

                        $request['filters'][$filterName] = [
                            'type' => 'wildcardFilter',
                            'name' => $filterName,
                            'field' => $attribute->getAttributeCode(),
                            'value' => '$' . $attribute->getAttributeCode() . '$',
                        ];
                    } else {
                        $request['queries'][$queryName] = [
                            'name' => $queryName,
                            'type' => 'matchQuery',
                            'value' => '$' . $attribute->getAttributeCode() . '$*',
                            'match' => [
                                [
                                    'field' => $attribute->getAttributeCode(),
                                    'boost' => $attribute->getSearchWeight() ?: 1,
                                ]
                            ]
                        ];
                    }
                    break;
                case 'decimal':
                case 'datetime':
                case 'date':
                    $filterName = $attribute->getAttributeCode() . '_filter';
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => 'filteredQuery',
                        'filterReference' => [['ref' => $filterName]]
                    ];
                    $request['filters'][$filterName] = [
                        'field' => $attribute->getAttributeCode(),
                        'name' => $filterName,
                        'type' => 'rangeFilter',
                        'from' => '$' . $attribute->getAttributeCode() . '.from$',
                        'to' => '$' . $attribute->getAttributeCode() . '.to$',
                    ];
                    break;
                default:
                    $filterName = $attribute->getAttributeCode() . '_filter';
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => 'filteredQuery',
                        'filterReference' => [['ref' => $filterName]]
                    ];

                    $request['filters'][$filterName] = [
                        'type' => 'termFilter',
                        'name' => $filterName,
                        'field' => $attribute->getAttributeCode(),
                        'value' => '$' . $attribute->getAttributeCode() . '$',
                    ];
            }
        }
        return $request;
    }
}
