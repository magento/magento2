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

use Magento\Framework\Exception\StateException;

class Cleaner
{
    /**
     * @var array
     */
    private $requestData;

    /**
     * @var array
     */
    private $mappedQueries;

    /**
     * @var array
     */
    private $mappedFilters;

    /**
     * Clean not binder queries and filters
     *
     * @param array $requestData
     * @return array
     */
    public function clean(array $requestData)
    {
        $this->clear();
        $this->requestData = $requestData;
        $this->cleanQuery($requestData['query']);
        $requestData = $this->requestData;
        $this->clear();

        return $requestData;
    }

    /**
     * Clear don't bind queries
     *
     * @param string $queryName
     * @return void
     * @throws StateException
     * @throws \Exception
     */
    private function cleanQuery($queryName)
    {
        if (!isset($this->requestData['queries'][$queryName])) {
            throw new \Exception('Query ' . $queryName . ' does not exist');
        } elseif (in_array($queryName, $this->mappedQueries)) {
            throw new StateException('Cycle found. Query %1 already used in request hierarchy', [$queryName]);
        }
        $this->mappedQueries[] = $queryName;
        $query = $this->requestData['queries'][$queryName];
        switch ($query['type']) {
            case QueryInterface::TYPE_BOOL:
                $queryReference = $this->processQueryReference($query['queryReference']);
                if (empty($queryReference)) {
                    unset($this->requestData['queries'][$queryName]);
                } else {
                    $this->requestData['queries'][$queryName]['queryReference'] = array_values($queryReference);
                }
                break;
            case QueryInterface::TYPE_MATCH:
                if (preg_match('/\$(.+)\$/si', $query['value'], $matches)) {
                    unset($this->requestData['queries'][$queryName]);
                }
                break;
            case QueryInterface::TYPE_FILTER:
                if (isset($query['queryReference'][0])) {
                    $fQueryName = $query['queryReference'][0]['ref'];
                    $this->cleanQuery($fQueryName);
                    if (!isset($this->requestData['queries'][$fQueryName])) {
                        unset($this->requestData['queries'][$queryName]);
                    }
                } elseif (isset($query['filterReference'][0])) {
                    $filterName = $query['filterReference'][0]['ref'];
                    $this->cleanFilter($filterName);
                    if (!isset($this->requestData['filters'][$filterName])) {
                        unset($this->requestData['queries'][$queryName]);
                    }
                } else {
                    throw new \Exception('Reference is not provided');
                }
                break;
            default:
                throw new \InvalidArgumentException('Invalid query type');
        }
    }

    /**
     * Clear don't bind filters
     *
     * @param string $filterName
     * @return void
     * @throws StateException
     * @throws \Exception
     */
    private function cleanFilter($filterName)
    {
        if (!isset($this->requestData['filters'][$filterName])) {
            throw new \Exception('Filter ' . $filterName . ' does not exist');
        } elseif (in_array($filterName, $this->mappedFilters)) {
            throw new StateException('Cycle found. Filter %1 already used in request hierarchy', [$filterName]);
        }
        $this->mappedFilters[] = $filterName;
        $filter = $this->requestData['filters'][$filterName];
        switch ($filter['type']) {
            case FilterInterface::TYPE_WILDCARD:
            case FilterInterface::TYPE_TERM:
                if (is_string($filter['value']) && preg_match('/\$(.+)\$/si', $filter['value'], $matches)) {
                    unset($this->requestData['filters'][$filterName]);
                }
                break;
            case FilterInterface::TYPE_RANGE:
                $keys = ['from', 'to'];
                foreach ($keys as $key) {
                    if (isset($filter[$key]) && preg_match('/\$(.+)\$/si', $filter[$key], $matches)) {
                        unset($this->requestData['filters'][$filterName]);
                        break;
                    }
                }
                break;
            case FilterInterface::TYPE_BOOL:
                $filterReference = $this->processFilterReference($filter['filterReference']);
                if (empty($filterReference)) {
                    unset($this->requestData['filters'][$filterName]);
                } else {
                    $this->requestData['filters'][$filterName]['filterReference'] = array_values($filterReference);
                }
                break;
            default:
                throw new \InvalidArgumentException('Invalid filter type');
        }
    }

    /**
     * Aggregate Queries by clause
     *
     * @param array $queryReference
     * @return array
     */
    private function processQueryReference($queryReference)
    {
        foreach ($queryReference as $key => $value) {
            $this->cleanQuery($value['ref']);
            if (!isset($this->requestData['queries'][$value['ref']])) {
                unset($queryReference[$key]);
            }
        }
        return $queryReference;
    }

    /**
     * Aggregate Filters by clause
     *
     * @param array $filterReference
     * @return array
     */
    private function processFilterReference($filterReference)
    {
        foreach ($filterReference as $key => $value) {
            $this->cleanFilter($value['ref']);
            if (!isset($this->requestData['filters'][$value['ref']])) {
                unset($filterReference[$key]);
            }
        }
        return $filterReference;
    }

    /**
     * Clear variables to default status
     *
     * @return void
     */
    private function clear()
    {
        $this->mappedQueries = [];
        $this->mappedFilters = [];
        $this->requestData = [];
    }
}
