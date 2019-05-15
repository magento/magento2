<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile

namespace Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin;

class Complex
{
    /**
     * @param $subject
     * @param $proceed
     * @param $arg
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetName($subject, $proceed, $arg)
    {
        return '[' . $proceed($arg) . ']';
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetName($subject, $result)
    {
        return $result . '%';
    }

    /**
     * @param $subject
     * @param $proceed
     * @param $arg
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetValue($subject, $proceed, $arg)
    {
        return $proceed('[' . $arg . ']');
    }

    /**
     * @param $subject
     * @param $arg
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetValue($subject, $arg)
    {
        return '%' . $arg;
    }

    /**
     * @param $subject
     * @param $proceed
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetReference($subject, $proceed)
    {
        return $proceed();
    }

    /**
     * @param $subject
     * @param $proceed
     * @param mixed ...$variadicValue
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundFirstVariadicParameter($subject, $proceed, ...$variadicValue)
    {
        return $proceed();
    }

    /**
     * @param $subject
     * @param $proceed
     * @param $value
     * @param mixed ...$variadicValue
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSecondVariadicParameter($subject, $proceed, $value, ...$variadicValue)
    {
        return $proceed();
    }

    /**
     * @param $subject
     * @param $proceed
     * @param mixed ...$variadicValue
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundByRefVariadic($subject, $proceed, & ...$variadicValue)
    {
        return $proceed();
    }

    /**
     * @param $subject
     * @param $ret
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterReturnVoid($subject, $ret)
    {

    }

    /**
     * @param $subject
     * @param $ret
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetNullableValue($subject, $ret)
    {

    }

}
