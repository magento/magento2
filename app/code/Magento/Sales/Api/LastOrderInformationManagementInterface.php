<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Interface for managing guest payment information
 * @api
 */
interface LastOrderInformationManagementInterface
{

    /**
     * Get last order increment id
     *
     * @return mixed
     */
    public function getLastRealOrderId();

    /**
     * Get last order information
     *
     * @return bool|\Magento\Sales\Model\Order
     */
    public function getLastOrderInformation();
}
