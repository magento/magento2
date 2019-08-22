<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

/**
 * Interface OrderAdapterInterface
 * @api
 * @since 100.0.2
 */
interface OrderAdapterInterface
{
    /**
     * Returns currency code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * Returns customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     */
    public function getBillingAddress();

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|null
     */
    public function getShippingAddress();

    /**
     * Returns order store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Returns order id
     *
     * @return int
     */
    public function getId();

    /**
     * Returns order grand total amount
     *
     * @return float
     */
    public function getGrandTotalAmount();

    /**
     * Returns list of line items in the cart
     *
     * @return array
     */
    public function getItems();

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp();
}
