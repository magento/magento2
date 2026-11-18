<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional;

use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireOptional;

/**
 * An Interceptor signature with no copied parameter attributes.
 */
class Interceptor extends AutowireOptional implements InterceptorInterface
{
    /**
     * @var array
     */
    public array $forwardedArguments;

    /**
     * Forward dependencies as a generated Interceptor does.
     */
    public function __construct(?\stdClass $dependency = null, ?\stdClass $unattributed = null)
    {
        $this->forwardedArguments = [$dependency, $unattributed];
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
