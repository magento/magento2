<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\SalesRule\Model\Spi\CodeLimitManagerInterface;

/**
 * Limit manager for admin area.
 */
class AdminCodeLimitManager implements CodeLimitManagerInterface
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkRequest(string $code): void
    {
        //phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
        return;
    }
}
