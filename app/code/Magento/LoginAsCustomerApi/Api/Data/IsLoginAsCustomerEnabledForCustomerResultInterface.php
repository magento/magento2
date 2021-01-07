<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api\Data;

/**
 * IsLoginAsCustomerEnabledForCustomerInterface results.
 */
interface IsLoginAsCustomerEnabledForCustomerResultInterface
{
    /**
     * Check if no validation failures occurred.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get error messages as array in case of validation failure, else return empty array.
     *
     * @return string[]
     */
    public function getMessages(): array;

    /**
     * Set error messages as array in case of validation failure.
     *
     * @param string[] $messages
     */
    public function setMessages(array $messages): void;
}
