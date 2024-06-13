<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture;

/**
 * Constructor with undefined number of arguments
 */
class Polymorphous
{
    /**
     * @var array
     */
    private $args;

    public function __construct()
    {
        $this->args = func_get_args();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getArg($key)
    {
        return $this->args[$key] ?? null;
    }
}
