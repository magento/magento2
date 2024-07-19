<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Resolve total records count.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class TotalRecordsResolver implements TotalRecordsResolverInterface
{
    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @param SearchResultInterface $searchResult
     */
    public function __construct(
        SearchResultInterface $searchResult
    ) {
        $this->searchResult = $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function resolve(): ?int
    {
        return $this->searchResult->getTotalCount();
    }
}
