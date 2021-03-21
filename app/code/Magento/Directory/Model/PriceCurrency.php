<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Pricing\Price\PricePrecisionInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Store\Model\Store;

/**
 * Class PriceCurrency model for convert and format price value
 */
class PriceCurrency implements PriceCurrencyInterface
{
    /**
     * @var StoreManagerInterface
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
     * @var PricePrecisionInterface
     */
    private $pricePrecision;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param Logger $logger
     * @param PricePrecisionInterface $pricePrecision
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        Logger $logger,
        PricePrecisionInterface $pricePrecision
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
        $this->pricePrecision = $pricePrecision;
    }

    /**
     * @inheritdoc
     */
    public function convert($amount, $scope = null, $currency = null)
    {
        $currentCurrency = $this->getCurrency($scope, $currency);

        return $this->getStore($scope)
            ->getBaseCurrency()
            ->convert($amount, $currentCurrency);
    }

    /**
     * @inheritdoc
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = null)
    {
        if ($precision === null) {
            $precision = $this->pricePrecision->getPrecision();
        }

        return $this->roundPrice($this->convert($amount, $scope, $currency), $precision);
    }

    /**
     * @inheritdoc
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = null,
        $scope = null,
        $currency = null
    ) {
        if ($precision === null) {
            $precision = $this->pricePrecision->getPrecision();
        }

        return $this->getCurrency($scope, $currency)
            ->formatPrecision($amount, $precision, [], $includeContainer);
    }

    /**
     * @inheritdoc
     */
    public function convertAndFormat(
        $amount,
        $includeContainer = true,
        $precision = null,
        $scope = null,
        $currency = null
    ) {
        if ($precision === null) {
            $precision = $this->pricePrecision->getPrecision();
        }

        $amount = $this->convert($amount, $scope, $currency);

        return $this->format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * @inheritdoc
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
     * Get currrency symbol
     *
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
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
     * @inheritdoc
     */
    public function round($price)
    {
        return round($price, 2);
    }

    /**
     * Round price with precision
     *
     * @param float $price
     * @param int $precision
     * @return float
     */
    public function roundPrice($price, $precision = null)
    {
        if ($precision === null) {
            $precision = $this->pricePrecision->getPrecision();
        }

        return round($price, $precision);
    }
}
