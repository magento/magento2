<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
