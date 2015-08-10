<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Store\Model\StoreManagerInterface;

class TableMapper
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Resource|Resource $resource
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        Resource $resource,
        StoreManagerInterface $storeManager,
        Config $config
    ) {

        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @param Select $select
     * @param RequestInterface $request
     * @return Select
     */
    public function addTables(Select $select, RequestInterface $request)
    {
        $filterFields = $this->getFilterFields($request->getQuery());
        $mappedTables = [];
        $fieldToTableMap = $this->getFieldToTableMap();

        foreach ($filterFields as $field => $filter) {
            if (array_key_exists($field, $fieldToTableMap)) {
                $mappingName = $field . '_index';
                list($table, $mapOn, $mappedFields) = $fieldToTableMap[$field];
                if (!array_key_exists($table, $mappedTables)) {
                    $select->joinLeft(
                        [
                            $mappingName => $table,
                        ],
                        $mapOn,
                        $mappedFields
                    );
                    $mappedTables[$table] = $mappingName;
                    unset($filterFields[$field]);
                }
            } elseif ($filter->getType() === FilterInterface::TYPE_TERM) {
                $table = $this->resource->getTableName('catalog_product_index_eav');
                if (!array_key_exists($table, $mappedTables)) {
                    $select->joinLeft(
                        ['cpie' => $table],
                        'search_index.entity_id = cpie.entity_id AND search_index.attribute_id = cpie.attribute_id',
                        []
                    );
                    $mappedTables[$table] = $field;
                }
            }
        }

        return $select;
    }

    /**
     * @param RequestQueryInterface $queryInterface
     * @return FilterInterface[]
     */
    private function getFilterFields(RequestQueryInterface $queryInterface)
    {
        $fields = [];
        foreach ($this->getFilters($queryInterface) as $filter) {
            $field = $filter->getField();
            $fields[$field] = $filter;
        }

        return $fields;
    }

    /**
     * @param RequestQueryInterface|FilterInterface $query
     * @return Filter[]
     */
    private function getFilters($query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    /**
     * @return int
     */
    private function getWebsiteId()
    {
        return $this->storeManager->getWebsite()->getId();
    }

    /**
     * @return array
     */
    private function getFieldToTableMap()
    {
        return [
            'price' => [
                $this->resource->getTableName('catalog_product_index_price'),
                'search_index.entity_id = price_index.entity_id'
                . $this->resource->getConnection()->quoteInto(' AND price_index.website_id = ?', $this->getWebsiteId()),
                []
            ],
            'category_ids' => [
                $this->resource->getTableName('catalog_category_product_index'),
                'search_index.entity_id = category_index.product_id',
                []
            ]
        ];
    }
}
