<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalsAdditionalDataInterface;

/**
 * Processes additional data for cart totals.
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
     */
    public function process(TotalsAdditionalDataInterface $additionalData, $cartId)
    {
        return;
    }
}
