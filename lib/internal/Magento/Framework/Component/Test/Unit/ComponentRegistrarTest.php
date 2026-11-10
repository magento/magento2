<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;

class ComponentRegistrarTest extends TestCase
{
    /**
     * @var ComponentRegistrar
     */
    private ComponentRegistrar $object;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->object = new ComponentRegistrar();
    }

    /**
     * @return void
     */
    public function testWithInvalidType()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('\'some_type\' is not a valid component type');
        ComponentRegistrar::register('some_type', "test_module_one", "some/path/name/one");
    }

    /**
     * @return void
     */
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
     * @return void
     */
    public function testRegistrarWithExceptionForModules()
    {
        $this->expectException('LogicException');
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_one", "some/path/name/onemore");
    }

    /**
     * @return void
     */
    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->object->getPath(ComponentRegistrar::MODULE, 'test_module_one'));
        $this->assertSame("some/path/name/two", $this->object->getPath(ComponentRegistrar::MODULE, 'test_module_two'));
    }

    /**
     * @return void
     */
    public function testGetPathForUnregisteredComponentReturnsNull()
    {
        $this->assertNull($this->object->getPath(ComponentRegistrar::MODULE, 'nonexistent_module'));
    }

    /**
     * @return void
     */
    public function testGetPathWithTypeAndNullComponentName()
    {
        $this->assertNull($this->object->getPath(ComponentRegistrar::LANGUAGE, null));
        $this->assertNull($this->object->getPath(ComponentRegistrar::MODULE, null));
        $this->assertNull($this->object->getPath(ComponentRegistrar::LIBRARY, null));
        $this->assertNull($this->object->getPath(ComponentRegistrar::THEME, null));
        $this->assertNull($this->object->getPath(ComponentRegistrar::SETUP, null));
    }
}
