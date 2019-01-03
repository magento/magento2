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

    public function returnVoid() : void
    {
        // Nothing to do here
    }

    /**
     * @return null|string
     */
    public function getNullableValue() : ?string
    {
        return null;
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string ...$variadicValue
     */
    public function typeHintedFirstVariadicParameter(string ...$variadicValue)
    {
        $this->variadicValue = $variadicValue;
    }

    /**
     * @param string $value
     * @param string ...$variadicValue
     */
    public function typeHintedSecondVariadicParameter(string $value, string ...$variadicValue)
    {
        $this->value = $value;
        $this->variadicValue = $variadicValue;
    }

    /**
     * @param string ...$variadicValue
     */
    public function byRefTypeHintedVariadic(string & ...$variadicValue)
    {
        $this->variadicValue = $variadicValue;
    }
}
