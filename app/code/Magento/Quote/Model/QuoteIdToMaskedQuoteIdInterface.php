<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Converts quote id to the masked quote id
 * @api
 */
interface QuoteIdToMaskedQuoteIdInterface
{
    /**
     * @param int $quoteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function execute(int $quoteId): string;
}
