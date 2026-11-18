<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

use Magento\Framework\ObjectManager\Attribute\Autowire;

/**
 * An optional interface dependency resolved through a preference.
 */
class AutowireInterface
{
    /**
     * Capture the implementation selected by the container.
     */
    public function __construct(#[Autowire] public ?\Countable $dependency = null)
    {
    }
}
