<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Api\Data;

interface DiscountAppliedToInterface
{
    public const APPLIED_TO_ITEM = 'ITEM';
    public const APPLIED_TO_SHIPPING = 'SHIPPING';
    public const APPLIED_TO = 'applied_to';
    /**
     * Get entity type the diescount is applied to
     *
     * @return string
     */
    public function getAppliedTo();
}
