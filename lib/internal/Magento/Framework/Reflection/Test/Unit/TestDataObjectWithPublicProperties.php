<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use \Magento\Framework\Reflection\Api\PublicPropertySerializableInterface;

class TestDataObjectWithPublicProperties implements PublicPropertySerializableInterface
{
    public function __construct(
        public readonly int $entityId,
        public readonly string $name
    ) {
    }
}
