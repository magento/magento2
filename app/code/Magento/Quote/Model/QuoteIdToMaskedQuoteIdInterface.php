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
 * @since 101.1.0
 */
interface QuoteIdToMaskedQuoteIdInterface
{
    /**
     * @param int $quoteId
     * @return string
     * @throws NoSuchEntityException
     * @since 101.1.0
     */
    public function execute(int $quoteId): string;
}
