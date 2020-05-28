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
     * @throws InputException
     */
    public function getResult(
        array $args,
        int $userId,
        StoreInterface $store
    ): DataObject {
        $collection = $this->collectionFactory->create($userId);
        $collection->addFilter('store_id', $store->getId());

        $this->orderFilter->applyFilter($args, $collection, $store);
        if (isset($args['currentPage'])) {
            $collection->setCurPage($args['currentPage']);
        }
        if (isset($args['pageSize'])) {
            $collection->setPageSize($args['pageSize']);
        }

        $orderArray = [];
        /** @var Order $order */
        foreach ($collection->getItems() as $key => $order) {
            $orderArray[$key] = $order->getData();
            $orderArray[$key]['model'] = $order;
        }

        if ($collection->getPageSize()) {
            $maxPages = (int)ceil($collection->getTotalCount() / $collection->getPageSize());
        } else {
            throw new InputException(__('Collection doesn\'t have set a page size'));
        }

        return $this->dataObjectFactory->create(
            [
                'data' => [
                        'total_count' => $collection->getTotalCount(),
                        'items' => $orderArray ?? [],
                        'page_size' => $collection->getPageSize(),
                        'current_page' => $collection->getCurPage(),
                        'total_pages' => $maxPages,
                    ]
            ]
        );
    }
}
