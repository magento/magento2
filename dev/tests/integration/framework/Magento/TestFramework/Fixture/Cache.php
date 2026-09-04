<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Cache
{
    /**
     * @param string $type
     * @param bool $status
     */
    public function __construct(
        public string $type,
        public bool $status,
    ) {
    }
}
