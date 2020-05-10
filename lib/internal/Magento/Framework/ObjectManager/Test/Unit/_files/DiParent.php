<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Di;

require_once __DIR__ . '/DiInterface.php';
class DiParent implements DiInterface
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
