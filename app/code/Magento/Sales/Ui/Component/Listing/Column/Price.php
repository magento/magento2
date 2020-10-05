<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;

/**
 * Class Price
 *
 * UiComponent class for Price format column
 */
class Price extends Column
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
     * @var StoreManagerInterface|null
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $components
     * @param array $data
     * @param Currency $currency
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        array $components = [],
        array $data = [],
        Currency $currency = null,
        StoreManagerInterface $storeManager = null
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->currency = $currency ?: ObjectManager::getInstance()
            ->get(Currency::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
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
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = isset($item['base_currency_code']) ? $item['base_currency_code'] : null;
                if (!$currencyCode) {
                    $store = $this->storeManager->getStore(
                        $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                    );
                    $currencyCode = $store->getBaseCurrency()->getCurrencyCode();
                }
                $basePurchaseCurrency = $this->currency->load($currencyCode);
                $item[$this->getData('name')] = $basePurchaseCurrency
                    ->format($item[$this->getData('name')], [], false);
            }
        }

        return $dataSource;
    }
}
