<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter\NullType;
use PHPUnit\Framework\TestCase;

class NullTypeTest extends TestCase
{
    public function testEvaluate()
    {
        $object = new NullType();
        $this->assertNull($object->evaluate(['unused']));
    }
}
