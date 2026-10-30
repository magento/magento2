<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Claim;

/**
 * Public collision-resistant claim.
 */
class PublicClaim extends AbstractClaim
{
    public function __construct(string $name, $value, ?string $prefix, bool $duplicated = false)
    {
        if ($prefix) {
            $prefix .= '-';
        }
        parent::__construct($prefix .$name, $value, self::CLASS_PUBLIC, $duplicated);
    }
}
