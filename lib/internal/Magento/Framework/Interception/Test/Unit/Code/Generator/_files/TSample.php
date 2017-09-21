<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code\Generator;

class TSample
{
    private $value;
    private $variadicValue;

    public function getValue() : string
    {
        return $this->value;
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }

    public function typeHintedFirstVariadicParameter(string ...$variadicValue)
    {
        $this->variadicValue = $variadicValue;
    }

    public function typeHintedSecondVariadicParameter(string $value, string ...$variadicValue)
    {
        $this->value = $value;
        $this->variadicValue = $variadicValue;
    }

    public function byRefTypeHintedVariadic(string & ...$variadicValue)
    {
        $this->variadicValue = $variadicValue;
    }
}
