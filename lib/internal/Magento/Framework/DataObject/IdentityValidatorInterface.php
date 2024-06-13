<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject;

/**
 * Interface IdentityValidatorInterface
 *
 * @api
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
