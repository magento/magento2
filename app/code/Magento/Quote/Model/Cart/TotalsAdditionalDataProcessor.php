<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
