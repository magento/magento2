<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

/**
 * Interface OrderAdapterInterface
 * @api
 * @since 2.0.0
 */
interface OrderAdapterInterface
{
    /**
     * Returns currency code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrencyCode();

    /**
     * Returns order increment id
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrderIncrementId();

    /**
     * Returns customer ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId();

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     * @since 2.0.0
     */
    public function getBillingAddress();

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|null
     * @since 2.0.0
     */
    public function getShippingAddress();

    /**
     * Returns order store id
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Returns order id
     *
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * Returns order grand total amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getGrandTotalAmount();

    /**
     * Returns list of line items in the cart
     *
     * @return array
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     * @since 2.0.0
     */
    public function getRemoteIp();
}
