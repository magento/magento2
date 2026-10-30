<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

/**
 * Private non-registered claim.
 */
class PrivateClaim extends AbstractClaim
{
    /**
     * @param string $name
     * @param $value
     * @param bool $duplicated
     */
    public function __construct(string $name, $value, bool $duplicated = false)
    {
        parent::__construct($name, $value, self::CLASS_PRIVATE, $duplicated);
    }
}
