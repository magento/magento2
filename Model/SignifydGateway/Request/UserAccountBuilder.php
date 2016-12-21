<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Sales\Model\Order;

/**
 * Prepares details based on registered user account info
 */
class UserAccountBuilder
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var CustomerOrders
     */
    private $customerOrders;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param CustomerOrders $customerOrders
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerOrders $customerOrders,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->customerOrders = $customerOrders;
    }

    /**
     * Returns user account data params.
     * Only for registered customers.
     *
     * @param Order $order
     * @return array
     */
    public function build(Order $order)
    {
        $result = [];

        $customerId = $order->getCustomerId();
        if (null === $customerId) {
            return $result;
        }

        $customer = $this->customerRepository->getById($customerId);
        $result = [
            'userAccount' => [
                'email' => $customer->getEmail(),
                'username' => $customer->getEmail(),
                'phone' => $order->getBillingAddress()->getTelephone(),
                'accountNumber' => $customerId,
                'createdDate' => $this->formatDate($customer->getCreatedAt()),
                'lastUpdateDate' => $this->formatDate($customer->getUpdatedAt())
            ]
        ];

        $ordersInfo = $this->customerOrders->getCountAndTotalAmount($customerId);
        if ($this->isNotEmptyCustomerOrdersInfo($ordersInfo)) {
            $result['userAccount']['aggregateOrderCount'] = $ordersInfo['aggregateOrderCount'];
            $result['userAccount']['aggregateOrderDollars'] = $ordersInfo['aggregateOrderDollars'];
        }

        return $result;
    }

    /**
     * Checks if customer aggregated orders count and total amount are available.
     *
     * @param array $ordersInfo
     * @return bool
     */
    private function isNotEmptyCustomerOrdersInfo(array $ordersInfo)
    {
        return null !== $ordersInfo['aggregateOrderCount'] && null !== $ordersInfo['aggregateOrderDollars'];
    }

    /**
     * Returns date formatted according to ISO8601.
     *
     * @param string $date
     * @return string
     */
    private function formatDate($date)
    {
        $result = $this->dateTimeFactory->create(
            $date,
            new \DateTimeZone('UTC')
        );

        return $result->format(\DateTime::ATOM);
    }
}
