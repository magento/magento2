<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @api
 * @since 100.0.2
 */
class QueryResult
{
    /**
     * @var string
     */
    private string $queryText;

    /**
     * @var int
     */
    private $resultsCount;

    /**
     * @var QueryCollectionFactory
     */
    private QueryCollectionFactory $queryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param $queryText
     * @param $resultsCount
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        $queryText,
        $resultsCount,
        QueryCollectionFactory $queryCollectionFactory,
        StoreManagerInterface  $storeManager
    ) {
        $this->queryText = $queryText;
        $this->resultsCount = $resultsCount;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getQueryText(): string
    {
        return $this->queryText;
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getResultsCount(): int
    {
        $collection = $this->queryCollectionFactory->create()->setStoreId(
            $this->storeManager->getStore()->getId()
        )->setQueryFilter(
            $this->getQueryText()
        );
        foreach ($collection as $item) {
            $this->resultsCount = $item->getData('num_results');
        }
        return $this->resultsCount;
    }
}
