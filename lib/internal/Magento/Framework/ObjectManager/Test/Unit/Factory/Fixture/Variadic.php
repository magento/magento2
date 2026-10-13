<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

/**
 * Constructor with variadic argument in constructor
 */
class Variadic
{
    /**
     * @var OneScalar[]
     */
    private $oneScalars;

    /**
     * Variadic constructor.
     * @param OneScalar[] ...$oneScalars
     */
    public function __construct(OneScalar ...$oneScalars)
    {
        $this->oneScalars = $oneScalars;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getOneScalarByKey($key)
    {
        return $this->oneScalars[$key] ?? null;
    }
}
