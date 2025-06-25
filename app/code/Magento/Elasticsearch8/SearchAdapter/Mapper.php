<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\SearchAdapter;

use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper as ElasticsearchMapper;

/**
 * Elasticsearch8 mapper class
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class Mapper
{
    /**
     * @var ElasticsearchMapper
     */
    private ElasticsearchMapper $mapper;

    /**
     * Mapper constructor.
     * @param ElasticsearchMapper $mapper
     */
    public function __construct(ElasticsearchMapper $mapper)
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
