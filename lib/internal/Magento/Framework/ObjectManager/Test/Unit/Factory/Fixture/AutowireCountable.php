<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

/**
 * An instantiable implementation for an optional interface dependency.
 */
class AutowireCountable implements \Countable
{
    /**
     * Return the size of this empty fixture.
     */
    public function count(): int
    {
        return 0;
    }
}
