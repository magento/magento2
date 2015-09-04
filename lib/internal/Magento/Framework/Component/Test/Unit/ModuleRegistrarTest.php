<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ModuleRegistrar;

class ModuleRegistrarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * module registrar object
     */
    private $module;

    protected function setUp()
    {
        $this->module = ModuleRegistrar::getInstance();
    }

    public function testGetPaths()
    {
        $this->module->register("test_module_one", "some/path/name/one");
        $this->module->register("test_module_two", "some/path/name/two");
        $expected = [
            'test_module_one' => "some/path/name/one",
            'test_module_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->module->getPaths());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage test_module_one already exists
     */
    public function testRegistrarWithException()
    {
        $this->module->register("test_module_one", "some/path/name/one");
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->module->getPath('test_module_one'));
        $this->assertSame("some/path/name/two", $this->module->getPath('test_module_two'));
    }
}
