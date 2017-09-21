<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\QueryChecker;

use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;

/**
 * Class is responsible for checking if fulltext search is required for search query
 */
class FullTextSearchCheck
{
    /**
     * Checks if $query requires full text search
     *
     * This is required to determine whether we need
     * to join catalog_eav_attribute table to search query or not
     *
     * In case when the $query does not requires full text search
     * - we can skipp joining catalog_eav_attribute table because it becomes excessive
     *
     * @param QueryInterface $query
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isRequiredForQuery(QueryInterface $query)
    {
        return $this->processQuery($query);
    }

    /**
     * @param QueryInterface $query
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function processQuery(QueryInterface $query)
    {
        switch ($query->getType()) {
            case QueryInterface::TYPE_MATCH:
                return true;
                break;

            case QueryInterface::TYPE_BOOL:
                return $this->processBoolQuery($query);
                break;

            case QueryInterface::TYPE_FILTER:
                return $this->processFilterQuery($query);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Unknown query type \'%s\'', $query->getType()));
        }
    }

    /**
     * @param BoolExpression $query
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function processBoolQuery(BoolExpression $query)
    {
        foreach ($query->getShould() as $shouldQuery) {
            if ($this->processQuery($shouldQuery)) {
                return true;
            }
        }

        foreach ($query->getMust() as $mustQuery) {
            if ($this->processQuery($mustQuery)) {
                return true;
            }
        }

        foreach ($query->getMustNot() as $mustNotQuery) {
            if ($this->processQuery($mustNotQuery)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Filter $query
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function processFilterQuery(Filter $query)
    {
        switch ($query->getReferenceType()) {
            case Filter::REFERENCE_QUERY:
                return $this->processQuery($query->getReference());
                break;

            case Filter::REFERENCE_FILTER:
                return false;
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unknown reference type \'%s\'',
                        $query->getReferenceType()
                    )
                );
        }
    }
}
