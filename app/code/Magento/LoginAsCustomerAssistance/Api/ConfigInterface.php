<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Api;

/**
 * LoginAsCustomerAssistance config.
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get title for shopping assistance checkbox.
     *
     * @return string
     */
    public function getShoppingAssistanceCheckboxTitle(): string;

    /**
     * Get tooltip for shopping assistance checkbox.
     *
     * @return string
     */
    public function getShoppingAssistanceCheckboxTooltip(): string;
}
