<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface ShippingMethodInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Shipping carrier code.
     */
    const CARRIER_CODE = 'carrier_code';

    /**
     * Shipping method code.
     */
    const METHOD_CODE = 'method_code';

    /**
     * Shipping carrier title.
     */
    const CARRIER_TITLE = 'carrier_title';

    /**
     * Shipping method title.
     */
    const METHOD_TITLE = 'method_title';

    /**
     * Shipping amount in store currency.
     */
    const SHIPPING_AMOUNT = 'amount';

    /**
     * Shipping amount in base currency.
     */
    const BASE_SHIPPING_AMOUNT = 'base_amount';

    /**
     * Available.
     */
    const AVAILABLE = 'available';

    /**
     * Returns the shipping carrier code.
     *
     * @return string Shipping carrier code.
     */
    public function getCarrierCode();

    /**
     * Sets the shipping carrier code.
     *
     * @param string $carrierCode
     * @return $this
     */
    public function setCarrierCode($carrierCode);

    /**
     * Returns the shipping method code.
     *
     * @return string Shipping method code.
     */
    public function getMethodCode();

    /**
     * Sets the shipping method code.
     *
     * @param string $methodCode
     * @return $this
     */
    public function setMethodCode($methodCode);

    /**
     * Returns the shipping carrier title.
     *
     * @return string|null Shipping carrier title. Otherwise, null.
     */
    public function getCarrierTitle();

    /**
     * Sets the shipping carrier title.
     *
     * @param string $carrierTitle
     * @return $this
     */
    public function setCarrierTitle($carrierTitle);

    /**
     * Returns the shipping method title.
     *
     * @return string|null Shipping method title. Otherwise, null.
     */
    public function getMethodTitle();

    /**
     * Sets the shipping method title.
     *
     * @param string $methodTitle
     * @return $this
     */
    public function setMethodTitle($methodTitle);

    /**
     * Returns the shipping amount in store currency.
     *
     * @return float Shipping amount in store currency.
     */
    public function getAmount();

    /**
     * Sets the shipping amount in store currency.
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Returns the shipping amount in base currency.
     *
     * @return float Shipping amount in base currency.
     */
    public function getBaseAmount();

    /**
     * Sets the shipping amount in base currency.
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);

    /**
     * Returns the value of the availability flag for the current shipping method.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAvailable();

    /**
     * Sets the value of the availability flag for the current shipping method.
     *
     * @param bool $available
     * @return $this
     */
    public function setAvailable($available);
}
