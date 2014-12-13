<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Directory\Model;

use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Logger;
use Magento\Store\Model\Store;

/**
 * Class PriceCurrency model for convert and format price value
 */
class PriceCurrency implements \Magento\Framework\Pricing\PriceCurrencyInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($amount, $scope = null, $currency = null)
    {
        $currentCurrency = $this->getCurrency($scope, $currency);

        return $this->getStore($scope)
            ->getBaseCurrency()
            ->convert($amount, $currentCurrency);
    }

    /**
     * Convert and round price value for specified store or passed currency
     *
     * @param float $amount
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @param Currency|string|null $currency
     * @param int $precision
     * @return float
     */
    public function convertAndRound($amount, $store = null, $currency = null, $precision = self::DEFAULT_PRECISION)
    {
        $currentCurrency = $this->getCurrency($store, $currency);
        $convertedValue = $this->getStore($store)->getBaseCurrency()->convert($amount, $currentCurrency);
        return round($convertedValue, $precision);
    }

    /**
     * {@inheritdoc}
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        return $this->getCurrency($scope, $currency)
            ->formatPrecision($amount, $precision, [], $includeContainer);
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndFormat(
        $amount,
        $includeContainer = true,
        $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $amount = $this->convert($amount, $scope, $currency);

        return $this->format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency($scope = null, $currency = null)
    {
        if ($currency instanceof Currency) {
            $currentCurrency = $currency;
        } elseif (is_string($currency)) {
            $currency = $this->currencyFactory->create()
                ->load($currency);
            $baseCurrency = $this->getStore($scope)
                ->getBaseCurrency();
            $currentCurrency = $baseCurrency->getRate($currency) ? $currency : $baseCurrency;
        } else {
            $currentCurrency = $this->getStore($scope)
                ->getCurrentCurrency();
        }

        return $currentCurrency;
    }

    /**
     * Get store model
     *
     * @param null|string|bool|int|ScopeInterface $scope
     * @return Store
     */
    protected function getStore($scope = null)
    {
        try {
            if (!$scope instanceof Store) {
                $scope = $this->storeManager->getStore($scope);
            }
        } catch (\Exception $e) {
            $this->logger->logException($e);
            $scope = $this->storeManager->getStore();
        }

        return $scope;
    }

    /**
     * Round price
     *
     * @param float $price
     * @return float
     */
    public function round($price)
    {
        return round($price, 2);
    }
}
