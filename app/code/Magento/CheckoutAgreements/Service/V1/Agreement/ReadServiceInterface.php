<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
