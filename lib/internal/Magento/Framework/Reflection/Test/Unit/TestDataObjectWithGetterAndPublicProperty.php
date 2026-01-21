<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

class TestDataObjectWithGetterAndPublicProperty
{
    public function __construct(
        public readonly string $name
    ) {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'getter-value';
    }
}
