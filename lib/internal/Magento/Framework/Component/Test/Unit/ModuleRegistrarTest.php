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
        $this->module = new ModuleRegistrar();
        ModuleRegistrar::register("test_module_one", "some/path/name/one");
        ModuleRegistrar::register("test_module_two", "some/path/name/two");
    }

    public function testGetPaths()
    {
        $expected = [
            'test_module_one' => "some/path/name/one",
            'test_module_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->module->getPaths());
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->module->getPath('test_module_one'));
        $this->assertSame("some/path/name/two", $this->module->getPath('test_module_two'));
    }
}
