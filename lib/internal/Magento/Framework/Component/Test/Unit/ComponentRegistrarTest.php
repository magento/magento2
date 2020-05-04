<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;

class ComponentRegistrarTest extends TestCase
{
    /**
     * Module registrar object
     *
     * @var ComponentRegistrar
     */
    private $object;

    protected function setUp(): void
    {
        $this->object = new ComponentRegistrar();
    }

    public function testWithInvalidType()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('\'some_type\' is not a valid component type');
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

    public function testRegistrarWithExceptionForModules()
    {
        $this->expectException('LogicException');
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_one", "some/path/name/onemore");
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->object->getPath(ComponentRegistrar::MODULE, 'test_module_one'));
        $this->assertSame("some/path/name/two", $this->object->getPath(ComponentRegistrar::MODULE, 'test_module_two'));
    }
}
