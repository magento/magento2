<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
