<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture\NativeTypeResolver;

/**
 * Declaring interface for inherited relative return types.
 */
interface ReturnInterface
{
    /**
     * Declare a static return type.
     *
     * @return static
     */
    public function staticName(): static;
}
