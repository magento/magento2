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
     * @var array
     */
    private $orders;

    /**
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param array $orders
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        TemporaryStorageFactory $temporaryStorageFactory,
        array $orders
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->orders = $orders;
    }

    /**
     * @inheritdoc
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

        if (isset($this->orders['relevance'])) {
            $this->collection->getSelect()->order(
                'search_result.' . TemporaryStorage::FIELD_SCORE . ' ' . $this->orders['relevance']
            );
        }
    }
}
