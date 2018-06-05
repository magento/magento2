<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolverMock;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->urnResolverMock = $this->getMock('Magento\Framework\Config\Dom\UrnResolver', [], [], '', false);
    }

    public function testGetSchemaFile()
    {
        $config = new \Magento\Framework\Config\Theme($this->urnResolverMock, null);
        $this->urnResolverMock->expects($this->exactly(2))
            ->method('getRealPath')
            ->with('urn:magento:framework:Config/etc/theme.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Config/etc/theme.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Config/etc/theme.xsd'),
            $config->getSchemaFile()
        );
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
            $this->urnResolverMock,
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
