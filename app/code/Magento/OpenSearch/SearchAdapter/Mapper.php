<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\SearchAdapter;

use Magento\Framework\Search\RequestInterface;

/**
 * OpenSearch mapper class for a query building
 */
class Mapper
{
    /**
     * @var \Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper
     */
    private $mapper;

    /**
     * @param \Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper $mapper
     */
    public function __construct(\Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper $mapper)
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
