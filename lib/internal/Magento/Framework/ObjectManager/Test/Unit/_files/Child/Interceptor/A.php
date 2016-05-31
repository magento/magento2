<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Child\Interceptor;

class A
{
    /**
     * @var string
     */
    protected $_wrapperSym;

    /**
     * @param string $wrapperSym
     */
    public function __construct($wrapperSym = 'A')
    {
        $this->_wrapperSym = $wrapperSym;
    }

    /**
     * @param string $param
     * @return string
     */
    public function wrapBefore($param)
    {
        return $this->_wrapperSym . $param . $this->_wrapperSym;
    }

    /**
     * @param string $returnValue
     * @return string
     */
    public function wrapAfter($returnValue)
    {
        return '_' . $this->_wrapperSym . '_' . $returnValue . '_' . $this->_wrapperSym . '_';
    }
}
