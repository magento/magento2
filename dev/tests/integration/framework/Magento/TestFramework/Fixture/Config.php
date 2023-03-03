<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Config
{
    /**
     * @param string $path
     * @param mixed $value
     * @param string $scopeType
     * @param string|null $scopeValue
     */
    public function __construct(
        public string $path,
        public mixed $value,
        public string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        public ?string $scopeValue = null
    ) {
    }
}
