<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Compiler;

class ConstructorArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $argument = ['configuration', 'array', true, null];
        $model = new \Magento\Setup\Module\Di\Compiler\ConstructorArgument($argument);
        $this->assertEquals($argument[0], $model->getName());
        $this->assertEquals($argument[1], $model->getType());
        $this->assertTrue($model->isRequired());
        $this->assertNull($model->getDefaultValue());
    }
}
