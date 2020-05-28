<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query;

use Magento\Sales\Model\Order;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Retrieve filtered orders data based off given search criteria in a format that GraphQL can interpret.
 */
class SearchQuery
{
    /**
     * @var CollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * @var OrderFilter
     */
    private $orderFilter;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param CollectionFactoryInterface $collectionFactoryInterface
     * @param OrderFilter $orderFilter
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        OrderFilter $orderFilter,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->orderFilter = $orderFilter;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Filter order data based off given search criteria
     *
     * @param array $args
     * @param int $userId,
     * @param StoreInterface $store
     * @return DataObject
     */
    public function getResult(
        array $args,
        int $userId,
        StoreInterface $store
    ): DataObject {
        $collection = $this->collectionFactory->create($userId);
        $collection->addFilter('store_id', $store->getId());
        try {
            $this->orderFilter->applyFilter($args, $collection, $store);
            if (isset($args['currentPage'])) {
                $collection->setCurPage($args['currentPage']);
            }
            if (isset($args['pageSize'])) {
                $collection->setPageSize($args['pageSize']);
            }
        } catch (InputException $e) {
            return $this->createEmptyResult($args);
        }

        $orderArray = [];
        /** @var Order $order */
        foreach ($collection->getItems() as $order) {
            $orderArray[$order->getId()] = $order->getData();
            $orderArray[$order->getId()]['model'] = $order;
        }

        if ($collection->getPageSize()) {
            $maxPages = (int)ceil($collection->getTotalCount() / $collection->getPageSize());
        } else {
            $maxPages = 0;
        }

        return $this->dataObjectFactory->create(
            [
                'data' => [
                        'total_count' => $collection->getTotalCount() ?? 0,
                        'items' => $orderArray ?? [],
                        'page_size' => $collection->getPageSize() ?? 0,
                        'current_page' => $collection->getCurPage() ?? 0,
                        'total_pages' => $maxPages ?? 0,
                    ]
            ]
        );
    }

    /**
     * Return and empty SearchResult object
     *
     * Used for handling exceptions gracefully
     *
     * @param array $args
     * @return DataObject
     */
    private function createEmptyResult(array $args): DataObject
    {
        return $this->dataObjectFactory->create(
            [
                'data' => [
                    'total_count' => 0,
                    'items' => [],
                    'page_size' => $args['pageSize'] ?? 20,
                    'current_page' => $args['currentPage'] ?? 1,
                    'total_pages' => 0,
                ]
            ]
        );
    }
}
