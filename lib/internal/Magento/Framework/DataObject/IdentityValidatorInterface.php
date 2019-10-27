<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

/**
 * Interface IdentityValidatorInterface
 */
interface IdentityValidatorInterface
{
    /**
     * Checks if uuid is valid
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid(string $value): bool;
}
