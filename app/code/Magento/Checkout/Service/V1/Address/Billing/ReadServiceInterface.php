<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Service\V1\Address\Billing;

/** Quote billing address read service interface. */
interface ReadServiceInterface
{
    /**
     * Returns the billing address for a specified quote.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address Quote billing address object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getAddress($cartId);
}
