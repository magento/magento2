<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

        /** @var \Magento\Framework\Api\Search\DocumentInterface[] $documents */
        $documents = [];

        /** @var \Magento\Framework\Search\Document $responseDocument */
        foreach ($response as $responseDocument) {
            $document = $this->documentFactory->create();

            /** @var \Magento\Framework\Search\DocumentField $field */
            foreach ($responseDocument as $field) {
                $document->setCustomAttribute($field->getName(), $field->getValue());
            }

            $document->setId($responseDocument->getId());

            $documents[] = $document;
        }
        $searchResult->setItems($documents);
        $searchResult->setAggregations($response->getAggregations());
        $searchResult->setTotalCount(count($documents));

        return $searchResult;
    }
}
