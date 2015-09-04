<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ThemeRegistrar;

class ThemeRegistrarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * theme registrar object
     */
    private $theme;

    protected function setUp()
    {
        $this->theme = ThemeRegistrar::getInstance();
    }

    public function testGetPaths()
    {
        $this->theme->register("test_theme_one", "some/path/name/one");
        $this->theme->register("test_theme_two", "some/path/name/two");
        $expected = [
            'test_theme_one' => "some/path/name/one",
            'test_theme_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->theme->getPaths());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage test_theme_one already exists
     */
    public function testRegistrarWithException()
    {
        $this->theme->register("test_theme_one", "some/path/name/one");
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->theme->getPath('test_theme_one'));
        $this->assertSame("some/path/name/two", $this->theme->getPath('test_theme_two'));
    }
}
