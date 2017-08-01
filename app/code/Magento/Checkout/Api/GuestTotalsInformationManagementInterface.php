<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

/**
 * Interface for guest quote totals calculation
 * @api
 * @since 2.0.0
 */
interface GuestTotalsInformationManagementInterface
{
    /**
     * Calculate quote totals based on address and shipping method.
     *
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @since 2.0.0
     */
    public function calculate(
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    );
}
