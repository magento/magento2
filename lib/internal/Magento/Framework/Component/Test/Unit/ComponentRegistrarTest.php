<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    private $module;

    /**
     * Language registrar object
     *
     * @var ComponentRegistrar
     */
    private $language;

    /**
     * Theme registrar object
     *
     * @var ComponentRegistrar
     */
    private $theme;

    /**
     * Library registrar object
     *
     * @var ComponentRegistrar
     */
    private $library;

    public function setUp()
    {
        $this->module = new ComponentRegistrar(ComponentRegistrar::MODULE);
        $this->language = new ComponentRegistrar(ComponentRegistrar::LANGUAGE);
        $this->theme = new ComponentRegistrar(ComponentRegistrar::THEME);
        $this->library = new ComponentRegistrar(ComponentRegistrar::LIBRARY);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage 'some_type' is not a valid component type
     */
    public function testConstructorWithInvalidType()
    {
        $this->library = new ComponentRegistrar('some_type');
    }

    public function testGetPathsForModule()
    {
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_one", "some/path/name/one");
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_two", "some/path/name/two");
        $expected = [
            'test_module_one' => "some/path/name/one",
            'test_module_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->module->getPaths());
    }

    public function testGetPathsForLanguage()
    {
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, "test_language_one", "some/path/name/one");
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, "test_language_two", "some/path/name/two");
        $expected = [
            'test_language_one' => "some/path/name/one",
            'test_language_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->language->getPaths());
    }

    public function testGetPathsForLibrary()
    {
        ComponentRegistrar::register(ComponentRegistrar::LIBRARY, "test_library_one", "some/path/name/one");
        ComponentRegistrar::register(ComponentRegistrar::LIBRARY, "test_library_two", "some/path/name/two");
        $expected = [
            'test_library_one' => "some/path/name/one",
            'test_library_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->library->getPaths());
    }

    public function testGetPathsForTheme()
    {
        ComponentRegistrar::register(ComponentRegistrar::THEME, "test_theme_one", "some/path/name/one");
        ComponentRegistrar::register(ComponentRegistrar::THEME, "test_theme_two", "some/path/name/two");
        $expected = [
            'test_theme_one' => "some/path/name/one",
            'test_theme_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->theme->getPaths());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage 'test_module_one' module already exists
     */
    public function testRegistrarWithExceptionForModules()
    {
        ComponentRegistrar::register(ComponentRegistrar::MODULE, "test_module_one", "some/path/name/one");
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage 'test_language_one' language already exists
     */
    public function testRegistrarWithExceptionForLanguage()
    {
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, "test_language_one", "some/path/name/one");
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage 'test_library_one' library already exists
     */
    public function testRegistrarWithExceptionForLibrary()
    {
        ComponentRegistrar::register(ComponentRegistrar::LIBRARY, "test_library_one", "some/path/name/one");
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage 'test_theme_one' theme already exists
     */
    public function testRegistrarWithExceptionForTheme()
    {
        ComponentRegistrar::register(ComponentRegistrar::THEME, "test_theme_one", "some/path/name/one");
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->module->getPath('test_module_one'));
        $this->assertSame("some/path/name/two", $this->module->getPath('test_module_two'));

        $this->assertSame("some/path/name/one", $this->language->getPath('test_language_one'));
        $this->assertSame("some/path/name/two", $this->language->getPath('test_language_two'));

        $this->assertSame("some/path/name/one", $this->library->getPath('test_library_one'));
        $this->assertSame("some/path/name/two", $this->library->getPath('test_library_two'));

        $this->assertSame("some/path/name/one", $this->theme->getPath('test_theme_one'));
        $this->assertSame("some/path/name/two", $this->theme->getPath('test_theme_two'));
    }
}
