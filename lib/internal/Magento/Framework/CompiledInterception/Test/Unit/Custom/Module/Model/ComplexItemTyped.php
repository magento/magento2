<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile

namespace Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model;

class ComplexItemTyped
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
    public function getName() : string
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
    public function firstVariadicParameter(string ...$variadicValue)
    {
        $this->variadicValue = $variadicValue;
    }

    /**
     * @param string $value
     * @param string ...$variadicValue
     */
    public function secondVariadicParameter(string $value, string ...$variadicValue)
    {
        $this->value = $value;
        $this->variadicValue = $variadicValue;
    }

    /**
     * @param string ...$variadicValue
     */
    public function byRefVariadic(string & ...$variadicValue)
    {
        $this->variadicValue = $variadicValue;
    }
}
