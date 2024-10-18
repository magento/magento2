<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch7\SearchAdapter;

use Magento\Framework\Search\RequestInterface;

/**
 * Elasticsearch7 mapper class
 * @deprecated 100.3.0 because of EOL for Elasticsearch7
 * @see this class will be responsible for ES7 only
 */
class Mapper
{
    /**
     * @var \Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Mapper
     */
    private $mapper;

    /**
     * Mapper constructor.
     * @param \Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Mapper $mapper
     */
    public function __construct(\Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @return array
     */
    public function buildQuery(RequestInterface $request) : array
    {
        $searchQuery = $this->mapper->buildQuery($request);
        $searchQuery['track_total_hits'] = true;
        return $searchQuery;
    }
}
