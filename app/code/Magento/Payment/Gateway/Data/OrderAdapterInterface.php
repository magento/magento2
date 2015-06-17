<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

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
     * @return AddressAdapterInterface
     */
    public function getBillingAddress();

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface
     */
    public function getShippingAddress();
}
