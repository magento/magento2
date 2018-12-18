<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

use Magento\Framework\Data\Collection;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Resolve specific attributes for search criteria.
 */
class SearchResultApplier implements SearchResultApplierInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param TemporaryStorageFactory $temporaryStorageFactory
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        TemporaryStorageFactory $temporaryStorageFactory
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $temporaryStorage = $this->temporaryStorageFactory->create();
        $table = $temporaryStorage->storeApiDocuments($this->searchResult->getItems());

        $this->collection->getSelect()->joinInner(
            [
                'search_result' => $table->getName(),
            ],
            'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
            []
        );
    }
}
