<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\Theme;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    /** @var UrnResolver */
    protected $urnResolver;

    /** @var UrnResolver */
    protected $urnResolverMock;

    protected function setUp(): void
    {
        $this->urnResolver = new UrnResolver();
        $this->urnResolverMock = $this->createMock(UrnResolver::class);
    }

    public function testGetSchemaFile()
    {
        $config = new Theme($this->urnResolverMock, null);
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
        $config = new Theme(
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
