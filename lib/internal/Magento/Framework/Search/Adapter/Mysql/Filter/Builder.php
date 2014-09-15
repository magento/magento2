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
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

class Builder implements BuilderInterface
{
    /**
     * @var Range
     */
    private $range;
    /**
     * @var Term
     */
    private $term;

    /**
     * @param Range $range
     * @param Term $term
     */
    public function __construct(
        Range $range,
        Term $term
    ) {
        $this->range = $range;
        $this->term = $term;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestFilterInterface $filter)
    {
        switch ($filter->getType()) {
            case RequestFilterInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Filter\Bool $filter */
                $queries = [];
                $must = $this->buildFilters($filter->getMust(), Select::SQL_AND);
                if (!empty($must)) {
                    $queries[] = $must;
                }
                $should = $this->buildFilters($filter->getShould(), Select::SQL_OR);
                if (!empty($should)) {
                    $queries[] = $this->wrapBrackets($should);
                }
                $mustNot = $this->buildFilters($filter->getMustNot(), Select::SQL_AND);
                if (!empty($mustNot)) {
                    $queries[] = '!' . $this->wrapBrackets($mustNot);
                }
                $query = $this->generateQuery($queries, Select::SQL_AND);
                break;
            case RequestFilterInterface::TYPE_TERM:
                /** @var \Magento\Framework\Search\Request\Filter\Term $filter */
                $query = $this->term->buildFilter($filter);
                break;
            case RequestFilterInterface::TYPE_RANGE:
                /** @var \Magento\Framework\Search\Request\Filter\Range $filter */
                $query = $this->range->buildFilter($filter);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown filter type \'%s\'', $filter->getType()));
        }
        return $this->wrapBrackets($query);
    }

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface[] $filters
     * @param string $unionOperator
     * @return string
     */
    private function buildFilters(array $filters, $unionOperator)
    {
        $queries = [];
        foreach ($filters as $filter) {
            $queries[] = $this->build($filter);
        }
        return $this->generateQuery($queries, $unionOperator);
    }

    /**
     * @param string[] $queries
     * @param string $unionOperator
     * @return string
     */
    private function generateQuery(array $queries, $unionOperator)
    {
        $query = implode(
            ' ' . $unionOperator . ' ',
            $queries
        );
        return $query;
    }

    /**
     * @param string $query
     * @return string
     */
    private function wrapBrackets($query)
    {
        return empty($query)
            ? $query
            : '(' . $query . ')';
    }
}
