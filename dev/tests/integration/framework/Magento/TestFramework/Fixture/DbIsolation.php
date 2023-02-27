<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class DbIsolation
{
    /**
     * @param bool $state
     */
    public function __construct(
        public bool $state = true
    ) {
    }
}
