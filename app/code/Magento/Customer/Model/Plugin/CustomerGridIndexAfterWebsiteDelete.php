<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Website;

/**
 * Run customer_grid indexer after deleting website for specified customers
 */
class CustomerGridIndexAfterWebsiteDelete
{
    private const CUSTOMER_GRID_INDEXER_ID = 'customer_grid';

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param CustomerCollectionFactory $customerCollectionFactory
     */
    public function __construct(IndexerRegistry $indexerRegistry, CustomerCollectionFactory $customerCollectionFactory)
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Run customer_grid indexer after deleting website
     *
     * @param Website $subject
     * @param callable $proceed
     * @return Website
     */
    public function aroundDelete(Website $subject, callable $proceed): Website
    {
        $customerIds = $this->getCustomerIdsByWebsiteId((int) $subject->getId());
        $result = $proceed();

        if ($customerIds) {
            $this->indexerRegistry->get(self::CUSTOMER_GRID_INDEXER_ID)
                ->reindexList($customerIds);
        }

        return $result;
    }

    /**
     * Returns customer ids by website id
     *
     * @param int $websiteId
     * @return array
     */
    private function getCustomerIdsByWebsiteId(int $websiteId): array
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->addFieldToFilter('website_id', $websiteId);

        return $collection->getAllIds();
    }
}
