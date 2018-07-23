<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model;

use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;
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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
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
    public function convert(float $amount, $scope = null, $currency = null): float
    {
        $currentCurrency = $this->getCurrency($scope, $currency);

        return $this->getStore($scope)
            ->getBaseCurrency()
            ->convert($amount, $currentCurrency);
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndRound(
        float $amount,
        $scope = null,
        $currency = null,
        int $precision = self::DEFAULT_PRECISION
    ): float {
        $currentCurrency = $this->getCurrency($scope, $currency);
        $convertedValue = $this->getStore($scope)->getBaseCurrency()->convert($amount, $currentCurrency);
        return round($convertedValue, $precision);
    }

    /**
     * {@inheritdoc}
     */
    public function format(
        float $amount,
        bool $includeContainer = true,
        int $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ): string {
        return $this->getCurrency($scope, $currency)
            ->formatPrecision($amount, $precision, [], $includeContainer);
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndFormat(
        float $amount,
        bool $includeContainer = true,
        int $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ): string {
        $amount = $this->convert($amount, $scope, $currency);

        return $this->format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency($scope = null, $currency = null): \Magento\Framework\Model\AbstractModel
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
     */
    public function getCurrencySymbol($scope = null, $currency = null): string
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
     * Round price
     *
     * @param float $price
     * @return float
     */
    public function round(float $price): float
    {
        return round($price, 2);
    }
}
