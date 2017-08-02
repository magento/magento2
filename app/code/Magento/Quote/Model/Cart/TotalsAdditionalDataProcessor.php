<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalsAdditionalDataInterface;

/**
 * Processes additional data for cart totals.
 * @since 2.0.0
 */
class TotalsAdditionalDataProcessor
{
    /**
     * Process cart totals additional data.
     *
     * @param TotalsAdditionalDataInterface $additionalData
     * @param int $cartId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function process(TotalsAdditionalDataInterface $additionalData, $cartId)
    {
        return;
    }
}
