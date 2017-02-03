<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Child\Interceptor;

class B
{
    /**
     * @param string $param
     * @return string
     */
    public function wrapBefore($param)
    {
        return 'B' . $param . 'B';
    }

    /**
     * @param string $returnValue
     * @return string
     */
    public function wrapAfter($returnValue)
    {
        return '_B_' . $returnValue . '_B_';
    }
}
