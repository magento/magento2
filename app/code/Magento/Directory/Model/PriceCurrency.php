<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Store\Model\Store;

/**
 * Class PriceCurrency model for convert and format price value
 * @since 2.0.0
 */
class PriceCurrency implements \Magento\Framework\Pricing\PriceCurrencyInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory
     * @since 2.0.0
     */
    protected $currencyFactory;

    /**
     * @var Logger
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param Logger $logger
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function convert($amount, $scope = null, $currency = null)
    {
        $currentCurrency = $this->getCurrency($scope, $currency);

        return $this->getStore($scope)
            ->getBaseCurrency()
            ->convert($amount, $currentCurrency);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = self::DEFAULT_PRECISION)
    {
        $currentCurrency = $this->getCurrency($scope, $currency);
        $convertedValue = $this->getStore($scope)->getBaseCurrency()->convert($amount, $currentCurrency);
        return round($convertedValue, $precision);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
     * @since 2.0.0
     */
    public function getCurrencySymbol($scope = null, $currency = null)
    {
        return $this->getCurrency($scope, $currency)->getCurrencySymbol();
    }

    /**
     * Get store model
     *
     * @param null|string|bool|int|ScopeInterface $scope
     * @return Store
     * @since 2.0.0
     */
    protected function getStore($scope = null)
    {
        try {
            if (!$scope instanceof Store) {
                $scope = $this->storeManager->getStore($scope);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $scope = $this->storeManager->getStore();
        }

        return $scope;
    }

    /**
     * Round price
     *
     * @param float $price
     * @return float
     * @since 2.0.0
     */
    public function round($price)
    {
        return round($price, 2);
    }
}
