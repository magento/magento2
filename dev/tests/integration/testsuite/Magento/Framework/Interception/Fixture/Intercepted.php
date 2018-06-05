<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Interception\Fixture;

class Intercepted extends InterceptedParent implements InterceptedInterface
{
    protected $_key;

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function A($param1)
    {
        $this->_key = $param1;
        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function B($param1, $param2)
    {
        return '<B>' . $param1 . $param2 . $this->C($param1) . '</B>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function C($param1)
    {
        return '<C>' . $param1 . '</C>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function D($param1)
    {
        return '<D>' . $this->_key . $param1 . '</D>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    final public function E($param1)
    {
        return '<E>' . $this->_key . $param1 . '</E>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function F($param1)
    {
        return '<F>' . $param1 . '</F>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function G($param1)
    {
        return '<G>' . $param1 . "</G>";
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function K($param1)
    {
        return '<K>' . $param1 . '</K>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function V($param1)
    {
        return '<V>' . $param1 . '</V>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function W($param1)
    {
        return '<W>' . $param1 . '</W>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function X($param1)
    {
        return '<X>' . $param1 . '</X>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function Y($param1)
    {
        return '<Y>' . $param1 . '</Y>';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function Z($param1)
    {
        return '<Z>' . $param1 . '</Z>';
    }
}
