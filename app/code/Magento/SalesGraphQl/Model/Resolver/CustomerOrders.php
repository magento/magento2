<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query\OrderFilter;
use Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query\OrderSort;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Orders data resolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerOrders implements ResolverInterface
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
     * @var OrderFormatter
     */
    private $orderFormatter;

    /**
     * @var OrderSort
     */
    private $orderSort;

    /**
     * @var StoreManagerInterface|mixed|null
     */
    private $storeManager;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderFilter $orderFilter
     * @param OrderFormatter $orderFormatter
     * @param OrderSort $orderSort
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderFilter $orderFilter,
        OrderFormatter $orderFormatter,
        OrderSort $orderSort,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderFilter = $orderFilter;
        $this->orderFormatter = $orderFormatter;
        $this->orderSort = $orderSort;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $storeIds = [];
        $userId = $context->getUserId();
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        if (isset($args['scope'])) {
            $storeIds = $this->getStoresByScope($args['scope'], $store);
        }
        try {
            $searchResult = $this->getSearchResult($args, (int)$userId, (int)$store->getId(), $storeIds);
            $maxPages = (int)ceil($searchResult->getTotalCount() / $searchResult->getPageSize());
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        $ordersArray = [];
        foreach ($searchResult->getItems() as $orderModel) {
            $ordersArray[] = $this->orderFormatter->format($orderModel);
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $ordersArray,
            'page_info' => [
                'page_size' => $searchResult->getPageSize(),
                'current_page' => $searchResult->getCurPage(),
                'total_pages' => $maxPages,
            ]
        ];
    }

    /**
     * Get search result from graphql query arguments
     *
     * @param array $args
     * @param int $userId
     * @param int $storeId
     * @param array $storeIds
     * @return OrderSearchResultInterface
     * @throws InputException
     */
    private function getSearchResult(array $args, int $userId, int $storeId, array $storeIds)
    {
        $filterGroups = $this->orderFilter->createFilterGroups($args, $userId, (int)$storeId, $storeIds);
        $this->searchCriteriaBuilder->setFilterGroups($filterGroups);
        if (isset($args['currentPage'])) {
            $this->searchCriteriaBuilder->setCurrentPage($args['currentPage']);
        }
        if (isset($args['pageSize'])) {
            $this->searchCriteriaBuilder->setPageSize($args['pageSize']);
        }
        if (isset($args['sort'])) {
            $sortOrders = $this->orderSort->createSortOrders($args);
            $this->searchCriteriaBuilder->setSortOrders($sortOrders);
        }
        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * Get eligible store to filter by based on scope
     *
     * @param string $scope
     * @param StoreInterface $store
     * @return array
     */
    private function getStoresByScope(string $scope, StoreInterface $store): array
    {
        $storeIds = [];
        switch ($scope) {
            case 'GLOBAL':
                $storeIds = $this->getStoresByFilter(null, null);
                break;
            case 'WEBSITE':
                    $websiteId = $store->getWebsiteId();
                    $storeIds = $this->getStoresByFilter((int)$websiteId, null);
                break;
            case 'STORE':
                    $storeGroupId = $store->getStoreGroupId();
                    $storeIds = $this->getStoresByFilter(null, (int)$storeGroupId);
                break;
            default:
                break;
        }
        return $storeIds;
    }

    /**
     * Filter store ids based on selected scope
     *
     * @param int|null $websiteId
     * @param int|null $storeGroupId
     * @return array
     */
    private function getStoresByFilter(?int $websiteId, ?int $storeGroupId): array
    {
        $stores = $this->storeManager->getStores(true, true);
        $storeIds = [];
        foreach ($stores as $store) {
            if (isset($websiteId) && $websiteId === (int)$store->getWebsiteId()
                ||
                isset($storeGroupId) && $storeGroupId === (int)$store->getStoreGroupId()
            ) {
                $storeIds[] = $store->getId();
            } elseif (!isset($websiteId) && !isset($storeGroupId)) {
                $storeIds[] = $store->getId();
            }
        }
        return $storeIds;
    }
}
