<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
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
}
