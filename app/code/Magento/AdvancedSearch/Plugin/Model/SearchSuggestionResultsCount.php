<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Plugin\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Search\Model\QueryResult;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Get the search suggestion results count.
 */
class SearchSuggestionResultsCount
{
    /**
     * @var QueryCollectionFactory
     */
    private QueryCollectionFactory $queryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Construct Method for get the search suggestion results count.
     *
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QueryCollectionFactory $queryCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get the search suggestion results count.
     *
     * @param QueryResult $subject
     * @param array $result
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetResultsCount(QueryResult $subject, $result)
    {
        $collection = $this->queryCollectionFactory->create()->setStoreId(
            $this->storeManager->getStore()->getId()
        )->setQueryFilter(
            $subject->getQueryText()
        );
        foreach ($collection as $item) {
            $result = $item->getData('num_results');
        }
        return $result;
    }
}
