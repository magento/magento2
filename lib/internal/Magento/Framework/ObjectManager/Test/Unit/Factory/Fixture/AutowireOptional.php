<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

use Magento\Framework\ObjectManager\Attribute\Autowire;

/**
 * Optional dependencies with and without container injection.
 */
class AutowireOptional
{
    /**
     * Capture the dependencies supplied by the container.
     */
    public function __construct(
        #[Autowire] public ?\stdClass $dependency = null,
        public ?\stdClass $unattributed = null
    ) {
    }
}
