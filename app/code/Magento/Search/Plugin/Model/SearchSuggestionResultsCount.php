<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Plugin\Model;

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
     * Query collection factory
     *
     * @var QueryCollectionFactory
     */
    protected QueryCollectionFactory $_queryCollectionFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

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
        $this->_queryCollectionFactory = $queryCollectionFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get the search suggestion results count.
     *
     * @throws NoSuchEntityException
     */
    public function afterGetResultsCount(QueryResult $subject, $result)
    {
        $collection = $this->_queryCollectionFactory->create()->setStoreId(
            $this->_storeManager->getStore()->getId()
        )->setQueryFilter(
            $subject->getQueryText()
        );
        foreach ($collection as $item) {
            $result = $item->getNumResults();
        }
        return $result;
    }
}
