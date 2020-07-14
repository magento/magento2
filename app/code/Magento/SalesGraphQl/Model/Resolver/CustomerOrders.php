<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query\OrderFilter;
use Magento\SalesGraphQl\Model\SalesItem\ExtractOrderAddressDetails;
use Magento\SalesGraphQl\Model\SalesItem\ExtractOrderPaymentDetails;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Orders data resolver
 */
class CustomerOrders implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ExtractOrderAddressDetails
     */
    private $extractOrderAddressDetails;

    /**
     * @var ExtractOrderPaymentDetails
     */
    private $extractOrderPaymentDetails;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderFilter
     */
    private $orderFilter;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ExtractOrderAddressDetails $extractOrderAddressDetails
     * @param ExtractOrderPaymentDetails $extractOrderPaymentDetails
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderFilter $orderFilter
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ExtractOrderAddressDetails $extractOrderAddressDetails,
        ExtractOrderPaymentDetails $extractOrderPaymentDetails,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderFilter $orderFilter
    ) {
        $this->orderRepository = $orderRepository;
        $this->extractOrderAddressDetails = $extractOrderAddressDetails;
        $this->extractOrderPaymentDetails = $extractOrderPaymentDetails;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderFilter = $orderFilter;
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
        $userId = $context->getUserId();
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $customerModel = $value['model'];
        $address = $customerModel->getAddresses();
        $addressArrayData = [];
        foreach ($address as $key => $addressArray) {
            $addressArrayData[$key] = $addressArray;
            foreach ($addressArray as $addressData) {
                $shipping = $addressData->isDefaultshipping();
                $billing = $addressData->isDefaultBilling();
            }
        }

        try {
            $searchResult = $this->getSearchResult($args, (int)$userId, (int)$store->getId());
            $maxPages = (int)ceil($searchResult->getTotalCount() / $searchResult->getPageSize());
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $this->formatOrdersArray($searchResult->getItems(), $address),
            'page_info' => [
                'page_size' => $searchResult->getPageSize(),
                'current_page' => $searchResult->getCurPage(),
                'total_pages' => $maxPages,
            ]
        ];
    }

    /**
     * Format order models for graphql schema
     *
     * @param OrderInterface[] $orderModels
     * @param array $address
     * @return array
     */
    private function formatOrdersArray(array $orderModels, array $address)
    {
        $ordersArray = [];

        foreach ($orderModels as $orderModel) {
            $ordersArray[] = [
                'created_at' => $orderModel->getCreatedAt(),
                'grand_total' => $orderModel->getGrandTotal(),
                'id' => base64_encode($orderModel->getEntityId()),
                'increment_id' => $orderModel->getIncrementId(),
                'number' => $orderModel->getIncrementId(),
                'order_date' => $orderModel->getCreatedAt(),
                'order_number' => $orderModel->getIncrementId(),
                'status' => $orderModel->getStatusLabel(),
                'shipping_method' => $orderModel->getShippingDescription(),
                'billing_address' => $this->extractOrderAddressDetails->getBillingAddressDetails($orderModel),
                'shipping_address' => $this->extractOrderAddressDetails->getShippingAddressDetails($orderModel),
                'payment_methods' => $this->extractOrderPaymentDetails->getOrderPaymentMethodDetails($orderModel),
                'model' => $orderModel,
            ];
        }
        return $ordersArray;
    }

    /**
     * Get search result from graphql query arguments
     *
     * @param array $args
     * @param int $userId
     * @param int $storeId
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     * @throws InputException
     */
    private function getSearchResult(array $args, int $userId, int $storeId)
    {
        $filterGroups = $this->orderFilter->createFilterGroups($args, $userId, (int)$storeId);
        $this->searchCriteriaBuilder->setFilterGroups($filterGroups);
        if (isset($args['currentPage'])) {
            $this->searchCriteriaBuilder->setCurrentPage($args['currentPage']);
        }
        if (isset($args['pageSize'])) {
            $this->searchCriteriaBuilder->setPageSize($args['pageSize']);
        }
        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }
}

