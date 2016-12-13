<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

use Magento\Sales\Model\Order;

/**
 * Prepare details from registered user account.
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var array
     */
    private $currencies = [];

    /**
     * @var CustomerOrders
     */
    private $customerOrders;

    /**
     * @var string
     */
    private static $usdCurrencyCode = 'USD';

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param CustomerOrders $customerOrders
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerOrders $customerOrders,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger;
        $this->currencyFactory = $currencyFactory;
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

        $customerOrders = $this->customerOrders->get($customerId);
        if (!empty($customerOrders)) {
            try {
                $orderTotalDollars = 0.0;
                foreach ($customerOrders as $order) {
                    $orderTotalDollars += $this->getUsdOrderTotal(
                        $order->getBaseGrandTotal(),
                        $order->getBaseCurrencyCode()
                    );
                }
                $result['userAccount']['aggregateOrderCount'] = count($customerOrders);
                $result['userAccount']['aggregateOrderDollars'] = $orderTotalDollars;
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Returns amount in USD
     *
     * @param float $amount
     * @param string $currency
     * @return float
     */
    private function getUsdOrderTotal($amount, $currency)
    {
        if ($currency === self::$usdCurrencyCode) {
            return $amount;
        }

        $operationCurrency = $this->getCurrencyByCode($currency);

        return $operationCurrency->convert($amount, self::$usdCurrencyCode);
    }

    /**
     * Get currency by currency code
     *
     * @param string|null $currencyCode
     * @return \Magento\Directory\Model\Currency
     */
    private function getCurrencyByCode($currencyCode)
    {
        if (isset($this->currencies[$currencyCode])) {
            return $this->currencies[$currencyCode];
        }

        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->currencyFactory->create();
        $this->currencies[$currencyCode] = $currency->load($currencyCode);

        return $this->currencies[$currencyCode];
    }

    /**
     * Format date in ISO8601
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

        return $result->format(\DateTime::ISO8601);
    }
}
