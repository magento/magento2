<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing;

/**
 * Interface PriceCurrencyInterface
 *
 * @api
 * @since 100.0.2
 */
interface PriceCurrencyInterface
{
    /**
     * @deprecated precision should be retrieved from current locale
     * @see \Magento\Framework\Pricing\Price\PricePrecisionInterface::getPrecision
     */
    const DEFAULT_PRECISION = 2;

    /**
     * Convert price value
     *
     * @param float $amount
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return float
     */
    public function convert($amount, $scope = null, $currency = null);

    /**
     * Convert and round price value
     *
     * @param float $amount
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @param int|null $precision
     * @return float
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = null);

    /**
     * Format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int|null $precision
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = null,
        $scope = null,
        $currency = null
    );

    /**
     * Convert and format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int|null $precision
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
     */
    public function convertAndFormat(
        $amount,
        $includeContainer = true,
        $precision = null,
        $scope = null,
        $currency = null
    );

    /**
     * Round price
     *
     * @deprecated 102.0.1
     * @param float $price
     * @return float
     */
    public function round($price);

    /**
     * Get currency model
     *
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getCurrency($scope = null, $currency = null);

    /**
     * Get currency symbol
     *
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
     */
    public function getCurrencySymbol($scope = null, $currency = null);
}
