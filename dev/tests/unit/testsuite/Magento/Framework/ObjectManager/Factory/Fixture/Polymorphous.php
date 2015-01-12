<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory\Fixture;

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
        return isset($this->args[$key]) ? $this->args[$key] : null;
    }
}
