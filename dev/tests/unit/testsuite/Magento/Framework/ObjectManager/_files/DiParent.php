<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di;

require_once __DIR__ . '/DiInterface.php';
class DiParent implements \Magento\Test\Di\DiInterface
{
    /**
     * @var string
     */
    protected $_wrapperSymbol;

    /**
     * @param string $wrapperSymbol
     */
    public function __construct($wrapperSymbol = '|')
    {
        $this->_wrapperSymbol = $wrapperSymbol;
    }

    /**
     * @param string $param
     * @return mixed
     */
    public function wrap($param)
    {
        return $this->_wrapperSymbol . $param . $this->_wrapperSymbol;
    }
}
