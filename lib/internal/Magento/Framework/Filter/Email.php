<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\FilterInterface;

class Email implements FilterInterface
{
    /**
     * Returns the result of filtering $value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        return $value;
    }
}
