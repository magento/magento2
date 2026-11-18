<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

use Magento\Framework\ObjectManager\Attribute\Autowire;

/**
 * Parameter types that cannot opt into container injection.
 */
class AutowireIgnored
{
    /**
     * @var \stdClass[]
     */
    public array $variadic;

    /**
     * Capture defaults for unsupported parameter types.
     *
     * @param mixed $untyped
     * @param \stdClass ...$variadic
     */
    public function __construct(
        #[Autowire] public int $scalar = 7,
        #[Autowire] public array $array = [],
        #[Autowire] public $untyped = null,
        #[Autowire] public \stdClass|\Countable|null $union = null,
        #[Autowire] public (\Countable&\Iterator)|null $intersection = null,
        #[Autowire] \stdClass ...$variadic
    ) {
        $this->variadic = $variadic;
    }
}
