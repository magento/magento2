<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class PlaceOrder implements RevertibleDataFixtureInterface
{
    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentManagement;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @param PaymentMethodManagementInterface $paymentManagement
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentManagement,
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement
    ) {
        $this->paymentManagement = $paymentManagement;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id' => (int) Cart ID. Required
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $cartId = (int)$data['cart_id'];
        $paymentMethod = $this->paymentManagement->get($cartId);

        $orderId = (int) $this->cartManagement->placeOrder($cartId, $paymentMethod);

        return $this->orderRepository->get($orderId);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $order = $this->orderRepository->get($data->getId());
        if ($order) {
            $this->orderManagement->cancel($order->getId());
            $this->orderRepository->delete($order);
        }
    }
}
