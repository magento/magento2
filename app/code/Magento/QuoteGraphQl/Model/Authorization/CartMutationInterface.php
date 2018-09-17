<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Authorization;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Service for checking that the shopping cart operations
 * are allowed for current user
 */
interface CartMutationInterface
{
    /**
     * @param int $quoteId
     * @return bool
     * @throws GraphQlNoSuchEntityException
     */
    public function isAllowed(int $quoteId): bool;
}
