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
 */
interface PriceCurrencyInterface
{
    /**
     * Default precision
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
    public function convert(float $amount, $scope = null, $currency = null): float;

    /**
     * Convert and round price value
     *
     * @param float $amount
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @param int $precision
     * @return float
     */
    public function convertAndRound(
        float $amount,
        $scope = null,
        $currency = null,
        int $precision = self::DEFAULT_PRECISION
    ): float;

    /**
     * Format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
     */
    public function format(
        float $amount,
        bool $includeContainer = true,
        int $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ): string;

    /**
     * Convert and format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
     */
    public function convertAndFormat(
        float $amount,
        bool $includeContainer = true,
        int $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ): string;

    /**
     * Round price
     *
     * @param float $price
     * @return float
     */
    public function round(float $price): float;

    /**
     * Get currency model
     *
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getCurrency($scope = null, $currency = null): \Magento\Framework\Model\AbstractModel;

    /**
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return string
     */
    public function getCurrencySymbol($scope = null, $currency = null): string;
}
