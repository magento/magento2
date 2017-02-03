<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;

class ComponentRegistrarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Module registrar object
     *
     * @var ComponentRegistrar
     */
    private $object;

    public function setUp()
    {
        $this->object = new ComponentRegistrar();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage 'some_type' is not a valid component type
     */
    public function testWithInvalidType()
    {
        ComponentRegistrar::register('some_type', "test_module_one", "some/path/name/one");
    }

    public function testGetPathsForModule()
    {
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_one", "some/path/name/one");
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_two", "some/path/name/two");
        $expected = [
            'test_module_one' => "some/path/name/one",
            'test_module_two' => "some/path/name/two",
        ];
        $this->assertContains($expected['test_module_one'], $this->object->getPaths(ComponentRegistrar::MODULE));
        $this->assertContains($expected['test_module_two'], $this->object->getPaths(ComponentRegistrar::MODULE));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage 'test_module_one' component already exists
     */
    public function testRegistrarWithExceptionForModules()
    {
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_one", "some/path/name/one");
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->object->getPath(ComponentRegistrar::MODULE, 'test_module_one'));
        $this->assertSame("some/path/name/two", $this->object->getPath(ComponentRegistrar::MODULE, 'test_module_two'));
    }
}
