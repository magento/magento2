<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch8\SearchAdapter;

use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper as Elasticsearch5Mapper;

/**
 * Elasticsearch8 mapper class
 */
class Mapper
{
    /**
     * @var Elasticsearch5Mapper
     */
    private Elasticsearch5Mapper $mapper;

    /**
     * Mapper constructor.
     * @param Elasticsearch5Mapper $mapper
     */
    public function __construct(Elasticsearch5Mapper $mapper)
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
