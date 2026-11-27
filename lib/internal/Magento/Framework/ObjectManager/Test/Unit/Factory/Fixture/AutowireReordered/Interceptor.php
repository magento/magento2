<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireReordered;

use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireReordered;

/**
 * An Interceptor with reordered and additional constructor parameters.
 */
class Interceptor extends AutowireReordered implements InterceptorInterface
{
    /**
     * @var \stdClass|null
     */
    public ?\stdClass $extra;

    /**
     * Capture an additional parameter while forwarding dependencies by name.
     */
    public function __construct(
        ?\stdClass $unattributed = null,
        ?\stdClass $dependency = null,
        ?\stdClass $extra = null
    ) {
        $this->extra = $extra;
        parent::__construct($dependency, $unattributed);
    }

    /**
     * @inheritdoc
     */
    public function ___callParent($method, array $arguments)
    {
        return parent::$method(...$arguments);
    }
}
