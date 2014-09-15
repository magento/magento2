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
namespace Magento\Framework\Search;

/**
 * Search Response
 */
class QueryResponse implements ResponseInterface, \IteratorAggregate, \Countable
{
    /**
     * Document Collection
     *
     * @var Document[]
     */
    protected $documents;

    /**
     * Aggregation Collection
     *
     * @var Aggregation
     */
    protected $aggregations;

    /**
     * @param Document[] $documents
     * @param Aggregation $aggregations
     */
    public function __construct(array $documents, Aggregation $aggregations)
    {
        $this->documents = $documents;
        $this->aggregations = $aggregations;
    }

    /**
     * Countable: return count of fields in document
     * @return int
     */
    public function count()
    {
        return count($this->documents);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->documents);
    }

    /**
     * Return Aggregation Collection
     *
     * @return Aggregation
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }
}
