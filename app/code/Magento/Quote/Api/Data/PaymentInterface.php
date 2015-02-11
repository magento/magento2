<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface PaymentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get purchase order number
     *
     * @return string|null
     */
    public function getPoNumber();

    /**
     * Set purchase order number
     *
     * @param string $poNumber
     * @return $this
     */
    public function setPoNumber($poNumber);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set payment method code
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * Get credit card owner
     *
     * @return string|null
     */
    public function getCcOwner();

    /**
     * Set credit card owner
     *
     * @param string $ccOwner
     * @return $this
     */
    public function setCcOwner($ccOwner);

    /**
     * Get credit card number
     *
     * @return string|null
     */
    public function getCcNumber();

    /**
     * Set credit card number
     *
     * @param string $ccNumber
     * @return $this
     */
    public function setCcNumber($ccNumber);

    /**
     * Get credit card type
     *
     * @return string|null
     */
    public function getCcType();

    /**
     * Set credit card type
     *
     * @param string $ccType
     * @return $this
     */
    public function setCcType($ccType);

    /**
     * Get credit card expiration year
     *
     * @return string|null
     */
    public function getCcExpYear();

    /**
     * Set credit card expiration year
     *
     * @param string $ccExpYear
     * @return $this
     */
    public function setCcExpYear($ccExpYear);

    /**
     * Get credit card expiration month
     *
     * @return string|null
     */
    public function getCcExpMonth();

    /**
     * Set credit card expiration month
     *
     * @param string $ccExpMonth
     * @return $this
     */
    public function setCcExpMonth($ccExpMonth);

    /**
     * Get payment additional details
     *
     * @return string[]|null
     */
    public function getAdditionalData();

    /**
     * Set payment additional details
     *
     * @param string[] $additionalData
     * @return $this
     */
    public function setAdditionalData(array $additionalData = null);
}
