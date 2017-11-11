<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order Quote address interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * This interface is used for saving properly CustomerQuoteAddressId in SalesOrderAddress table
 */
interface OrderQuoteAddressInterface
{
    /**
     * Quote address ID.
     */
    const QUOTE_ADDRESS_ID = 'quote_address_id';

    /**
     * Gets the quote address ID for the order address.
     *
     * @return int|null Quote address ID.
     */
    public function getQuoteAddressId();

    /**
     * @param int|null $addressId
     * @return $this
     */
    public function setQuoteAddressId($addressId);
}
