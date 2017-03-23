<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
