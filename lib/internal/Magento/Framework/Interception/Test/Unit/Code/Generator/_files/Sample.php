<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code\Generator;

class Sample
{
    private $attribute;
    private $variadicAttribute;

    public function getValue()
    {
        return $this->attribute;
    }

    public function setValue($value)
    {
        $this->attribute = $value;
    }

    public function & getReference()
    {
    }

    public function firstVariadicParameter(...$variadicValue)
    {
        $this->variadicAttribute = $variadicValue;
    }

    public function secondVariadicParameter($value, ...$variadicValue)
    {
        $this->attribute = $value;
        $this->variadicAttribute = $variadicValue;
    }

    public function byRefVariadic(& ...$variadicValue)
    {
        $this->variadicAttribute = $variadicValue;
    }
}
