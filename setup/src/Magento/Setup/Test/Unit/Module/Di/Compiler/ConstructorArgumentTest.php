<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Compiler;

use Magento\Setup\Module\Di\Compiler\ConstructorArgument;
use PHPUnit\Framework\TestCase;

class ConstructorArgumentTest extends TestCase
{
    public function testInterface()
    {
        $argument = ['configuration', 'array', true, null];
        $model = new ConstructorArgument($argument);
        $this->assertEquals($argument[0], $model->getName());
        $this->assertEquals($argument[1], $model->getType());
        $this->assertTrue($model->isRequired());
        $this->assertNull($model->getDefaultValue());
    }
}
