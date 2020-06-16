<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Retrieve filtered orders data based off given search criteria in a format that GraphQL can interpret.
 */
class SearchQuery
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderFilter
     */
    private $orderFilter;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderFilter $orderFilter
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderFilter $orderFilter,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderFilter = $orderFilter;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Filter order data based off given search criteria
     *
     * @param array $args
     * @param int $userId
     * @param StoreInterface $store
     * @return DataObject
     * @throws InputException
     */
    public function getResult(
        array $args,
        int $userId,
        StoreInterface $store
    ): DataObject {
        $this->orderFilter->applyFilter($userId, $args, $store, $this->searchCriteriaBuilder);
        if (isset($args['currentPage'])) {
            $this->searchCriteriaBuilder->setCurrentPage($args['currentPage']);
        }
        if (isset($args['pageSize'])) {
            $this->searchCriteriaBuilder->setPageSize($args['pageSize']);
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult = $this->orderRepository->getList($searchCriteria);
        $orderArray = [];
        /** @var Order $order */
        foreach ($searchResult->getItems() as $key => $order) {
            $orderArray[$key] = $order->getData();
            $orderArray[$key]['model'] = $order;
        }

        if ($searchResult->getPageSize()) {
            $maxPages = (int)ceil($searchResult->getTotalCount() / $searchResult->getPageSize());
        } else {
            throw new InputException(__('Collection doesn\'t have set a page size'));
        }

        return $this->dataObjectFactory->create(
            [
                'data' => [
                        'total_count' => $searchResult->getTotalCount(),
                        'items' => $orderArray ?? [],
                        'page_size' => $searchResult->getPageSize(),
                        'current_page' => $searchResult->getCurPage(),
                        'total_pages' => $maxPages,
                    ]
            ]
        );
    }
}
