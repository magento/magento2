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

class RequestGenerator
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
    ) {
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
            /** @var $attribute \Magento\Catalog\Model\Product\Attribute */
            if (in_array($attribute->getAttributeCode(), ['price', 'sku'])) {
                //same fields have special semantics
                continue;
            }
            $request['queries']['quick_search_container']['match'][] = [
                'field' => $attribute->getAttributeCode(),
                'boost' => $attribute->getSearchWeight() ?: 1,
            ];
        }
        return $request;
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
            /** @var $attribute \Magento\Catalog\Model\Product\Attribute */
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
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => 'matchQuery',
                        'value' => '$' . $attribute->getAttributeCode() . '$',
                        'match' => [
                            [
                                'field' => $attribute->getAttributeCode(),
                                'boost' => $attribute->getSearchWeight() ?: 1,
                            ]
                        ]
                    ];
                    break;
                case 'decimal':
                case 'date':
                    $filterName = $attribute->getAttributeCode() . '_filter';
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => 'filteredQuery',
                        'filterReference' => [['ref' => $filterName]]
                    ];
                    $request['filters'][$filterName] = [
                        'field' => $attribute->getAttributeCode(),
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
                        'field' => $attribute->getAttributeCode(),
                        'value' => '$' . $attribute->getAttributeCode() . '$',
                    ];
            }
        }
        return $request;
    }

    /**
     * Retrieve searchable attributes
     *
     * @return \Traversable
     */
    protected function getSearchableAttributes()
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection $productAttributes */
        $productAttributes = $this->productAttributeCollectionFactory->create();
        $productAttributes->addFieldToFilter('is_searchable', 1);

        return $productAttributes;
    }
}
