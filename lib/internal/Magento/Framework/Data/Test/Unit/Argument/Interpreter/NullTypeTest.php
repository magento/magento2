<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use \Magento\Framework\Data\Argument\Interpreter\NullType;

class NullTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testEvaluate()
    {
        $object = new NullType();
        $this->assertNull($object->evaluate(['unused']));
    }
}
