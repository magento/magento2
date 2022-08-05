<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

/**
 * Constructor with non variadic and variadic argument in constructor
 */
class SemiVariadic
{
    const DEFAULT_FOO_VALUE = 'bar';

    /**
     * @var OneScalar[]
     */
    private $oneScalars;

    /**
     * @var string
     */
    private $foo;

    /**
     * SemiVariadic constructor.
     *
     * @param string      $foo
     * @param OneScalar[] ...$oneScalars
     */
    public function __construct(
        string $foo = self::DEFAULT_FOO_VALUE,
        OneScalar ...$oneScalars
    ) {
        $this->foo = $foo;
        $this->oneScalars = $oneScalars;
    }

    /**
     * @param  mixed $key
     * @return mixed
     */
    public function getOneScalarByKey($key)
    {
        return $this->oneScalars[$key] ?? null;
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        return $this->foo;
    }
}
