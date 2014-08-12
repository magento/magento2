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
namespace Magento\Framework\Search\Request;

use Magento\Framework\Search\Request\Query\Filter;

class Mapper
{
    /**
     * @var array
     */
    private $queries;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param array $queries
     * @param array $filters
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        array $queries,
        array $filters = null
    ) {
        $this->objectManager = $objectManager;
        $this->queries = $queries;
        $this->filters = $filters;
    }

    /**
     * Get Query Interface by name
     *
     * @param string $queryName
     * @return QueryInterface
     */
    public function get($queryName)
    {
        return $this->mapQuery($queryName);
    }

    /**
     * Convert array to Query instance
     *
     * @param string $queryName
     * @throws \Exception
     * @return QueryInterface
     */
    private function mapQuery($queryName)
    {
        if (!isset($this->queries[$queryName])) {
            throw new \Exception('Query ' . $queryName . ' does not exist');
        }
        $query = $this->queries[$queryName];
        switch ($query['type']) {
            case QueryInterface::TYPE_MATCH:
                $query = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Query\Match',
                    [
                        'name' => $query['name'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'matches' => $query['match']
                    ]
                );
                break;
            case QueryInterface::TYPE_FILTER:
                if (isset($query['queryReference'][0])) {
                    $reference = $this->mapQuery($query['queryReference'][0]['ref']);
                    $referenceType = Filter::REFERENCE_QUERY;
                } elseif (isset($query['filterReference'][0])) {
                    $reference = $this->mapFilter($query['filterReference'][0]['ref']);
                    $referenceType = Filter::REFERENCE_FILTER;
                } else {
                    throw new \Exception('Reference is not provided');
                }
                $query = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Query\Filter',
                    [
                        'name' => $query['name'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'reference' => $reference,
                        'referenceType' => $referenceType
                    ]
                );
                break;
            case QueryInterface::TYPE_BOOL:
                $aggregatedByType = $this->aggregateQueriesByType($query['queryReference']);
                $query = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Query\Bool',
                    array_merge(
                        ['name' => $query['name'], 'boost' => isset($query['boost']) ? $query['boost'] : 1],
                        $aggregatedByType
                    )
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid query type');
        }
        return $query;
    }

    /**
     * Aggregate Queries by clause
     *
     * @param array $data
     * @return array
     */
    private function aggregateQueriesByType($data)
    {
        $list = [];
        foreach ($data as $value) {
            $list[$value['clause']][$value['ref']] = $this->mapQuery($value['ref']);
        }
        return $list;
    }

    /**
     * Aggregate Filters by clause
     *
     * @param array $data
     * @return array
     */
    private function aggregateFiltersByType($data)
    {
        $list = [];
        foreach ($data as $value) {
            $list[$value['clause']][$value['ref']] = $this->mapFilter($value['ref']);
        }
        return $list;
    }

    /**
     * Convert array to Filter instance
     *
     * @param string $filterName
     * @throws \Exception
     * @return FilterInterface
     */
    private function mapFilter($filterName)
    {
        if (!isset($this->filters[$filterName])) {
            throw new \Exception('Filter ' . $filterName . ' does not exist');
        }
        $filter = $this->filters[$filterName];
        switch ($filter['type']) {
            case FilterInterface::TYPE_TERM:
                $filter = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Filter\Term',
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value']
                    ]
                );
                break;
            case FilterInterface::TYPE_RANGE:
                $filter = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Filter\Range',
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'from' => $filter['from'],
                        'to' => $filter['to']
                    ]
                );

                break;
            case FilterInterface::TYPE_BOOL:
                $aggregatedByType = $this->aggregateFiltersByType($filter['filterReference']);
                $filter = $this->objectManager->create(
                    'Magento\Framework\Search\Request\Filter\Bool',
                    array_merge(
                        ['name' => $filter['name']],
                        $aggregatedByType
                    )
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid filter type');
        }
        return $filter;
    }
}
