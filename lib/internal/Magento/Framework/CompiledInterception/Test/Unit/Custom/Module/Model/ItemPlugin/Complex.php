<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile

namespace Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin;

class Complex
{
    public function aroundGetName($subject, $proceed, $arg)
    {
        return '[' . $proceed($arg) . ']';
    }

    public function afterGetName($subject, $result)
    {
        return $result . '%';
    }

    public function aroundSetValue($subject, $proceed, $arg)
    {
        return $proceed('[' . $arg . ']');
    }

    public function beforeSetValue($subject, $arg)
    {
        return '%' . $arg;
    }

    public function aroundGetReference($subject, $proceed)
    {
        return $proceed();
    }

    public function aroundFirstVariadicParameter($subject, $proceed, ...$variadicValue)
    {
        return $proceed();
    }

    public function aroundSecondVariadicParameter($subject, $proceed, $value, ...$variadicValue)
    {
        return $proceed();
    }

    public function aroundByRefVariadic($subject, $proceed, & ...$variadicValue)
    {
        return $proceed();
    }

    public function afterReturnVoid($subject, $ret)
    {

    }

    public function afterGetNullableValue($subject, $ret)
    {

    }

}
