<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\SalesGraphQl\Model\Order\Token;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Retrieve guest order details
 */
class GuestOrder implements ResolverInterface
{
    /**
     * @param OrderFormatter $orderFormatter
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param StoreManagerInterface $storeManager
     * @param Token $token
     */
    public function __construct(
        private readonly OrderFormatter $orderFormatter,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly Token $token
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        list($number, $email, $lastname) = $this->getNumberEmailLastname($args['input'] ?? []);
        $order = $this->getOrder($number);
        $this->validateOrder($order, $lastname, $email);
        return $this->orderFormatter->format($order);
    }

    /**
     * Retrieve order based on order number
     *
     * @param string $number
     * @return OrderInterface
     * @throws GraphQlNoSuchEntityException
     */
    private function getOrder(string $number): OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('increment_id', $number)
            ->addFilter('store_id', $this->storeManager->getStore()->getId())
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        if (empty($orders)) {
            $this->cannotLocateOrder();
        }

        return reset($orders);
    }

    /**
     * Ensure the order matches the provided criteria
     *
     * @param OrderInterface $order
     * @param string $lastname
     * @param string $email
     * @return void
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    private function validateOrder(OrderInterface $order, string $lastname, string $email): void
    {
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress->getLastname() !== $lastname || $billingAddress->getEmail() !== $email) {
            $this->cannotLocateOrder();
        }

        if ($order->getCustomerId()) {
            throw new GraphQlAuthorizationException(__('Please login to view the order.'));
        }
    }

    /**
     * Retrieve order number, email, and lastname from input
     *
     * @param array $input
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getNumberEmailLastname(array $input): array
    {
        if (isset($input['token'])) {
            $data = $this->token->decrypt($input['token']);
            if (count($data) !== 3) {
                $this->cannotLocateOrder();
            }
            return $data;
        }
        if (!isset($input['number']) || !isset($input['email']) || !isset($input['lastname'])) {
            $this->cannotLocateOrder();
        }
        return [$input['number'], $input['email'], $input['lastname']];
    }

    /**
     * Throw exception when the order cannot be found or does not match the criteria
     *
     * @return void
     * @throws GraphQlNoSuchEntityException
     */
    private function cannotLocateOrder(): void
    {
        throw new GraphQlNoSuchEntityException(__('We couldn\'t locate an order with the information provided.'));
    }
}
