<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

class TestDataObjectWithPublicProperties implements \Magento\Framework\Reflection\Api\PublicPropertySerializableInterface
{
    public function __construct(
        public readonly int $entityId,
        public readonly string $name
    ) {
    }
}
