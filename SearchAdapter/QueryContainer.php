<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\SearchAdapter;

/**
 * The purpose of this class to be a container for the array with ElasticSearch query.
 * @codeCoverageIgnore
 * @since 2.2.0
 */
class QueryContainer
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $query;

    /**
     * @param array $query
     * @since 2.2.0
     */
    public function __construct(array $query)
    {
        $this->query = $query;
    }

    /**
     * Returns a query
     *
     * @return array
     * @since 2.2.0
     */
    public function getQuery()
    {
        return $this->query;
    }
}
