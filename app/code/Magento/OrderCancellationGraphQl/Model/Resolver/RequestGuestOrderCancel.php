<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\OrderCancellationGraphQl\Model\CancelOrderGuest;
use Magento\OrderCancellationGraphQl\Model\Validator\ValidateGuestRequest;
use Magento\OrderCancellationGraphQl\Model\Validator\ValidateOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Order\Token;
use Magento\Store\Model\StoreManagerInterface;

class RequestGuestOrderCancel implements ResolverInterface
{
    /**
     * RequestGuestOrderCancel Constructor
     *
     * @param ValidateGuestRequest $validateRequest
     * @param OrderRepositoryInterface $orderRepository
     * @param ValidateOrder $validateOrder
     * @param CancelOrderGuest $cancelOrderGuest
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param StoreManagerInterface $storeManager
     * @param Token $token
     */
    public function __construct(
        private readonly ValidateGuestRequest         $validateRequest,
        private readonly OrderRepositoryInterface     $orderRepository,
        private readonly ValidateOrder                $validateOrder,
        private readonly CancelOrderGuest             $cancelOrderGuest,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly StoreManagerInterface        $storeManager,
        private readonly Token                        $token
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $this->validateRequest->validateInput($args['input'] ?? []);
        list($number, $email, $lastname) = $this->getNumberEmailLastname($args['input']['token']);

        $order = $this->getOrder($number);
        $this->validateRequest->validateOrderDetails($order, $lastname, $email);

        $errors = $this->validateOrder->execute($order);
        if ($errors) {
            return $errors;
        }

        return $this->cancelOrderGuest->execute($order, $args['input']);
    }

    /**
     * Retrieve order details based on order number
     *
     * @param string $number
     * @return OrderInterface
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     */
    private function getOrder(string $number): OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('increment_id', $number)
            ->addFilter('store_id', $this->storeManager->getStore()->getId())
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        if (empty($orders)) {
            $this->validateRequest->cannotLocateOrder();
        }

        return reset($orders);
    }

    /**
     * Retrieve number, email and lastname from token
     *
     * @param string $token
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getNumberEmailLastname(string $token): array
    {
        $data = $this->token->decrypt($token);
        if (count($data) !== 3) {
            $this->validateRequest->cannotLocateOrder();
        }
        return $data;
    }
}
