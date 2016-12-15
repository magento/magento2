<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

/**
 * Signifyd case creation request builder interface
 *
 * Retrieves params for case creation request API call based on order ID
 */
interface CreateCaseBuilderInterface
{
    /**
     * Returns params for Case creation request
     *
     * @param int $orderId
     * @return array
     */
    public function build($orderId);
}
