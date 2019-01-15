<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\SearchAdapter\Query;

use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Query\Builder as Elasticsearch5Builder;

/**
 * Query builder for search adapter.
 *
 * @api
 * @since 100.1.0
 */
class Builder extends Elasticsearch5Builder
{
    /**
     * Set initial settings for query
     *
     * @param RequestInterface $request
     * @return array
     * @since 100.1.0
     */
    public function initQuery(RequestInterface $request)
    {
        $dimension = current($request->getDimensions());
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $searchQuery = [
            'index' => $this->searchIndexNameResolver->getIndexName($storeId, $request->getIndex()),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'from' => $request->getFrom(),
                'size' => $request->getSize(),
                'fields' => ['_id', '_score'],
                'sort' => $this->sortBuilder->getSort($request),
                'query' => [],
            ],
        ];
        return $searchQuery;
    }
}
