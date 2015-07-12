<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\FirstModule\Model;

use Magento\LibSecond;

class Model
{
    /**
     * @use Magento\LibSecond()
     */
    public function test()
    {
        new LibSecond();
    }
}
