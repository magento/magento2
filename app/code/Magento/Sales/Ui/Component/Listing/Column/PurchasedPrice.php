<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;

/**
 * Class Price
 */
class PurchasedPrice extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $components
     * @param array $data
     * @param Currency $currency
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        array $components = [],
        array $data = [],
        Currency $currency = null,
        OrderRepositoryInterface $orderRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->currency = $currency ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(Currency::class);
        $this->orderRepository = $orderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(SearchCriteriaBuilder::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $orderIds = array_column($dataSource['data']['items'],'order_id');
            $orderCurrencyCodes = $this->getOrdersCurrency($orderIds);
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = $item['order_currency_code'] ?? $orderCurrencyCodes[$item['order_id']];
                $purchaseCurrency = $this->currency->load($currencyCode);
                $item[$this->getData('name')] = $purchaseCurrency
                    ->format($item[$this->getData('name')], [], false);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $orderIds
     * @return array
     */
    private function getOrdersCurrency(array $orderIds): array
    {
        $orderCurrencyCodes = [];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $orderIds ,'in')
            ->create();

        foreach ($this->orderRepository->getList($searchCriteria)->getItems() as $order) {
            $orderCurrencyCodes[$order->getEntityId()] = $order->getOrderCurrencyCode();
        }

        return $orderCurrencyCodes;
    }
}
