<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Exception;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides information about customer orders.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.2.0
 */
class CustomerOrders
{
    /**
     * @var SearchCriteriaBuilder
     * @since 2.2.0
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     * @since 2.2.0
     */
    private $filterBuilder;

    /**
     * @var OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @var CurrencyFactory
     * @since 2.2.0
     */
    private $currencyFactory;

    /**
     * @var array
     * @since 2.2.0
     */
    private $currencies = [];

    /**
     * @var string
     * @since 2.2.0
     */
    private static $usdCurrencyCode = 'USD';

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param CurrencyFactory $currencyFactory
     * @param LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        OrderRepositoryInterface $orderRepository,
        CurrencyFactory $currencyFactory,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->orderRepository = $orderRepository;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
    }

    /**
     * Returns aggregated customer orders count and total amount in USD.
     *
     * Returned array contains next keys:
     * aggregateOrderCount - total count of orders placed by this account since it was created, including the current
     * aggregateOrderDollars - total amount spent by this account since it was created, including the current order
     *
     * @param int $customerId
     * @return array
     * @since 2.2.0
     */
    public function getAggregatedOrdersInfo($customerId)
    {
        $result = [
            'aggregateOrderCount' => null,
            'aggregateOrderDollars' => null
        ];

        $customerOrders = $this->getCustomerOrders($customerId);
        if (!empty($customerOrders)) {
            try {
                $orderTotalDollars = 0.0;
                foreach ($customerOrders as $order) {
                    $orderTotalDollars += $this->getUsdOrderTotal(
                        $order->getBaseGrandTotal(),
                        $order->getBaseCurrencyCode()
                    );
                }
                $result = [
                    'aggregateOrderCount' => count($customerOrders),
                    'aggregateOrderDollars' => $orderTotalDollars
                ];
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Returns customer orders.
     *
     * @param int $customerId
     * @return OrderInterface[]
     * @since 2.2.0
     */
    private function getCustomerOrders($customerId)
    {
        $filters = [
            $this->filterBuilder->setField(OrderInterface::CUSTOMER_ID)->setValue($customerId)->create()
        ];
        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->orderRepository->getList($searchCriteria);

        return $searchResults->getItems();
    }

    /**
     * Returns amount in USD.
     *
     * @param float $amount
     * @param string $currency
     * @return float
     * @since 2.2.0
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
     * Returns currency by currency code.
     *
     * @param string|null $currencyCode
     * @return Currency
     * @since 2.2.0
     */
    private function getCurrencyByCode($currencyCode)
    {
        if (isset($this->currencies[$currencyCode])) {
            return $this->currencies[$currencyCode];
        }

        /** @var Currency $currency */
        $currency = $this->currencyFactory->create();
        $this->currencies[$currencyCode] = $currency->load($currencyCode);

        return $this->currencies[$currencyCode];
    }
}
