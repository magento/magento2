<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Config\ScopeInterface;

/**
 * Prepare data related to purchase event represented in Case Creation request.
 */
class PurchaseBuilder
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param DateTimeFactory $dateTimeFactory
     * @param ScopeInterface $scope
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        DateTimeFactory $dateTimeFactory,
        ScopeInterface $scope
    ) {
        $this->orderRepository = $orderRepository;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->scope = $scope;
    }

    /**
     * Returns purchase data params
     *
     * @param int $orderId
     * @return array
     */
    public function build($orderId)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        $orderPayment = $order->getPayment();
        $createdAt = $this->dateTimeFactory->create(
            $order->getCreatedAt(),
            new \DateTimeZone('UTC')
        );


        return [
            'purchase' => [
                'browserIpAddress' => $order->getRemoteIp(),
                'shipments' => [
                    'shippingPrice' => $order->getShippingAmount()

                ],
                'orderId' => $order->getEntityId(),
                'createdAt' => $createdAt->format(\DateTime::ISO8601),
                'paymentGateway' => $this->getPaymentGateway($orderPayment->getMethod()),
                'transactionId' => $orderPayment->getLastTransId(),
                'currency' => $order->getOrderCurrencyCode(),
                'orderChannel' => $this->getOrderChannel(),
                'totalPrice' => $order->getGrandTotal(),
            ],
        ];
    }

    /**
     * Returns the gateway that processed the transaction. For PayPal orders use paypal_account.
     *
     * @param string $gatewayCode
     * @return string
     */
    private function getPaymentGateway($gatewayCode)
    {
        return (bool)substr_count($gatewayCode, 'paypal') ? 'paypal_account' : $gatewayCode;
    }

    /**
     * Returns WEB for web-orders, PHONE for orders created by Admin
     *
     * @return string
     */
    private function getOrderChannel()
    {
        return $this->scope->getCurrentScope() === 'adminhtml' ? 'PHONE' : 'WEB';
    }
}
