<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Converts masked quote id to the quote id (entity id)
 * @api
 */
interface MaskedQuoteIdToQuoteIdInterface
{
    /**
     * @param string $maskedQuoteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function execute(string $maskedQuoteId): int;
}
