<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

/**
 * A handwritten constructor that does not opt into injection.
 */
class AutowireRedeclared extends AutowireOptional
{
    /**
     * @var array
     */
    public array $forwardedArguments;

    /**
     * Forward optional dependencies without inheriting parameter attributes.
     */
    public function __construct(?\stdClass $dependency = null, ?\stdClass $unattributed = null)
    {
        $this->forwardedArguments = [$dependency, $unattributed];
        parent::__construct($dependency, $unattributed);
    }
}
