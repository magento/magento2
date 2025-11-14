<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Api\Data\TotalsExtensionInterface;

/**
 * Test helper for TotalsExtension to support extension attribute methods
 *
 * This helper implements TotalsExtensionInterface to provide test-specific functionality.
 * Used in JoinNegotiableQuoteTotalsPluginTest.php to set negotiable quote totals.
 *
 * Provides:
 * - negotiableQuoteTotals - NegotiableQuoteTotals object for B2B quotes
 *
 * All other TotalsExtensionInterface methods return null by default.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class TotalsExtensionTestHelper implements TotalsExtensionInterface
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterface|null
     */
    private $negotiableQuoteTotals;

    /**
     * Get negotiable quote totals
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterface|null
     */
    public function getNegotiableQuoteTotals()
    {
        return $this->negotiableQuoteTotals;
    }

    /**
     * Set negotiable quote totals
     *
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterface|null $totals
     * @return $this
     */
    public function setNegotiableQuoteTotals($totals)
    {
        $this->negotiableQuoteTotals = $totals;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCouponLabel()
    {
        return null;
    }

    /**
     * @param mixed $couponLabel
     * @return $this
     */
    public function setCouponLabel($couponLabel)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseCustomerBalanceAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setBaseCustomerBalanceAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerBalanceAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setCustomerBalanceAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseGiftCardsAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setBaseGiftCardsAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGiftCardsAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setGiftCardsAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwItemsBasePrice()
    {
        return null;
    }

    /**
     * @param mixed $price
     * @return $this
     */
    public function setGwItemsBasePrice($price)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwItemsPrice()
    {
        return null;
    }

    /**
     * @param mixed $price
     * @return $this
     */
    public function setGwItemsPrice($price)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwCardBasePrice()
    {
        return null;
    }

    /**
     * @param mixed $price
     * @return $this
     */
    public function setGwCardBasePrice($price)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwCardPrice()
    {
        return null;
    }

    /**
     * @param mixed $price
     * @return $this
     */
    public function setGwCardPrice($price)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwBaseTaxAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setGwBaseTaxAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwTaxAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setGwTaxAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwItemsBaseTaxAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setGwItemsBaseTaxAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwItemsTaxAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setGwItemsTaxAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwCardBaseTaxAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setGwCardBaseTaxAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGwCardTaxAmount()
    {
        return null;
    }

    /**
     * @param mixed $amount
     * @return $this
     */
    public function setGwCardTaxAmount($amount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCouponCodes()
    {
        return null;
    }

    /**
     * @param mixed $couponCodes
     * @return $this
     */
    public function setCouponCodes($couponCodes)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCouponsLabels()
    {
        return null;
    }

    /**
     * @param mixed $couponsLabels
     * @return $this
     */
    public function setCouponsLabels($couponsLabels)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRewardPointsBalance()
    {
        return null;
    }

    /**
     * @param mixed $rewardPointsBalance
     * @return $this
     */
    public function setRewardPointsBalance($rewardPointsBalance)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRewardCurrencyAmount()
    {
        return null;
    }

    /**
     * @param mixed $rewardCurrencyAmount
     * @return $this
     */
    public function setRewardCurrencyAmount($rewardCurrencyAmount)
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseRewardCurrencyAmount()
    {
        return null;
    }

    /**
     * @param mixed $baseRewardCurrencyAmount
     * @return $this
     */
    public function setBaseRewardCurrencyAmount($baseRewardCurrencyAmount)
    {
        return $this;
    }
}
