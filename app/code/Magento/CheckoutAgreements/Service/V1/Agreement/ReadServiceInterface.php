<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CheckoutAgreements\Service\V1\Agreement;

/**
 * Checkout agreement service interface.
 */
interface ReadServiceInterface
{
    /**
     * Lists active checkout agreements.
     *
     * @return \Magento\CheckoutAgreements\Service\V1\Data\Agreement[] Array of active checkout agreements.
     */
    public function getList();
}
