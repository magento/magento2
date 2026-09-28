<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model\Data;

use Magento\Framework\Jwt\Claim\AbstractClaim;

class Claim extends AbstractClaim
{
    public function __construct(string $name, $value, ?int $class)
    {
        parent::__construct($name, $value, $class, false);
    }
}
