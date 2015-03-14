<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di;

interface DiInterface
{
    /**
     * @param string $param
     * @return mixed
     */
    public function wrap($param);
}
