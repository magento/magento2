<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSchemaFile()
    {
        $config = new \Magento\Framework\Config\Theme();
        $this->assertFileExists($config->getSchemaFile());
    }

    /**
     * @param string $themePath
     * @param array $expected
     * @dataProvider dataGetterDataProvider
     */
    public function testDataGetter($themePath, $expected)
    {
        $expected = reset($expected);
        $config = new \Magento\Framework\Config\Theme(
            file_get_contents(__DIR__ . '/_files/area/' . $themePath . '/theme.xml')
        );
        $this->assertSame($expected['media'], $config->getMedia());
        $this->assertSame($expected['title'], $config->getThemeTitle());
        $this->assertSame($expected['parent'], $config->getParentTheme());
    }

    /**
     * @return array
     */
    public function dataGetterDataProvider()
    {
        return [
            [
                'default_default',
                [[
                    'media' => ['preview_image' => 'media/default_default.jpg'],
                    'title' => 'Default',
                    'parent' => null,
                ]], ],
            [
                'default_test',
                [[
                    'media' => ['preview_image' => ''],
                    'title' => 'Test',
                    'parent' => ['default_default'],
                ]]],
            [
                'default_test2',
                [[
                    'media' => ['preview_image' => ''],
                    'title' => 'Test2',
                    'parent' => ['default_test'],
                ]]],
            [
                'test_default',
                [[
                    'media' => ['preview_image' => 'media/test_default.jpg'],
                    'title' => 'Default',
                    'parent' => null,
                ]]],
            [
                'test_external_package_descendant',
                [[
                    'media' => ['preview_image' => ''],
                    'title' => 'Default',
                    'parent' => ['default_test2'],
                ]]],
        ];
    }
}
