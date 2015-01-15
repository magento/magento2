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
     * Get payment method code
     *
     * @return string
     */
    public function getMethod();

    /**
     * Get credit card owner
     *
     * @return string|null
     */
    public function getCcOwner();

    /**
     * Get credit card number
     *
     * @return string|null
     */
    public function getCcNumber();

    /**
     * Get credit card type
     *
     * @return string|null
     */
    public function getCcType();

    /**
     * Get credit card expiration year
     *
     * @return string|null
     */
    public function getCcExpYear();

    /**
     * Get credit card expiration month
     *
     * @return string|null
     */
    public function getCcExpMonth();

    /**
     * Get payment additional details
     *
     * @return string[]|null
     */
    public function getAdditionalData();
}
