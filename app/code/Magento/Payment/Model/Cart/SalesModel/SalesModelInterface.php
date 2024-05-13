<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Wrapper interface for accessing sales model data
 *
 * @api
 */
interface SalesModelInterface
{
    /**
     * Get all items from shopping sales model
     *
     * @return array
     */
    public function getAllItems();

    /**
     * Get base subtotal
     *
     * @return float|null
     */
    public function getBaseSubtotal();

    /**
     * Get base tax amount
     *
     * @return float|null
     */
    public function getBaseTaxAmount();

    /**
     * Get base shipping amount
     *
     * @return float|null
     */
    public function getBaseShippingAmount();

    /**
     * Get base discount amount
     *
     * @return float|null
     */
    public function getBaseDiscountAmount();

    /**
     * Wrapper for \Magento\Framework\DataObject getDataUsingMethod method
     *
     * @param string $key
     * @param mixed $args
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null);

    /**
     * Return object that contains tax related fields
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|\Magento\Quote\Api\Data\AddressInterface
     */
    public function getTaxContainer();
}
