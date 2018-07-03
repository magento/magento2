<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\DocumentFactory;
use Magento\Framework\Api\Search\SearchResultFactory;

class SearchResponseBuilder
{
    /**
     * @var DocumentFactory
     * @deprecated 100.1.0
     */
    private $documentFactory;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param DocumentFactory $documentFactory
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        DocumentFactory $documentFactory
    ) {
        $this->documentFactory = $documentFactory;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @param ResponseInterface $response
     * @return SearchResultInterface
     */
    public function build(ResponseInterface $response)
    {
        /** @var \Magento\Framework\Api\Search\SearchResult $searchResult */
        $searchResult = $this->searchResultFactory->create();

        $documents = iterator_to_array($response);
        $searchResult->setItems($documents);
        $searchResult->setAggregations($response->getAggregations());
        $searchResult->setTotalCount(count($documents));

        return $searchResult;
    }
}
