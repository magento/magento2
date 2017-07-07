<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Theme;

use Magento\Framework\Component\ComponentRegistrar;

class XmlFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\ValidationStateInterface
     */
    protected $validationStateMock;

    public function setUp()
    {
        $this->validationStateMock = $this->getMock(
            \Magento\Framework\Config\ValidationStateInterface::class,
            [],
            [],
            '',
            false
        );
        $this->validationStateMock->method('isValidationRequired')
            ->willReturn(true);
    }

    /**
     * @param string $file
     * @dataProvider viewConfigFileDataProvider
     */
    public function testViewConfigFile($file)
    {
        $domConfig = new \Magento\Framework\Config\Dom(
            file_get_contents($file),
            $this->validationStateMock
        );
        $errors = [];
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $result = $domConfig->validate(
            $urnResolver->getRealPath('urn:magento:framework:Config/etc/view.xsd'),
            $errors
        );
        $this->assertTrue($result, "Invalid XML-file: {$file}\n" . join("\n", $errors));
    }

    /**
     * @return array
     */
    public function viewConfigFileDataProvider()
    {
        $result = [];
        /** @var \Magento\Framework\Component\DirSearch $componentDirSearch */
        $componentDirSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Component\DirSearch::class);
        $files = $componentDirSearch->collectFiles(ComponentRegistrar::THEME, 'etc/view.xml');
        foreach ($files as $file) {
            $result[substr($file, strlen(BP))] = [$file];
        }
        return $result;
    }

    /**
     * @param string $themeDir
     * @dataProvider themeConfigFileExistsDataProvider
     */
    public function testThemeConfigFileExists($themeDir)
    {
        $this->assertFileExists($themeDir . '/theme.xml');
    }

    /**
     * @return array
     */
    public function themeConfigFileExistsDataProvider()
    {
        $result = [];
        /** @var \Magento\Framework\Component\ComponentRegistrar $componentRegistrar */
        $componentRegistrar = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Component\ComponentRegistrar::class);
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themeDir) {
            $result[substr($themeDir, strlen(BP))] = [$themeDir];
        }
        return $result;
    }

    /**
     * @param string $file
     * @dataProvider themeConfigFileDataProvider
     */
    public function testThemeConfigFileSchema($file)
    {
        $domConfig = new \Magento\Framework\Config\Dom(file_get_contents($file), $this->validationStateMock);
        $errors = [];
        $result = $domConfig->validate('urn:magento:framework:Config/etc/theme.xsd', $errors);
        $this->assertTrue($result, "Invalid XML-file: {$file}\n" . join("\n", $errors));
    }

    /**
     * Configuration should declare a single package/theme that corresponds to the file system directories
     *
     * @param string $file
     * @dataProvider themeConfigFileDataProvider
     */
    public function testThemeConfigFileHasSingleTheme($file)
    {
        /** @var $configXml \SimpleXMLElement */
        $configXml = simplexml_load_file($file);
        $actualThemes = $configXml->xpath('/theme');
        $this->assertCount(1, $actualThemes, 'Single theme declaration is expected.');
    }

    /**
     * @return array
     */
    public function themeConfigFileDataProvider()
    {
        $result = [];
        /** @var \Magento\Framework\Component\DirSearch $componentDirSearch */
        $componentDirSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Component\DirSearch::class);
        $files = $componentDirSearch->collectFiles(ComponentRegistrar::THEME, 'theme.xml');
        foreach ($files as $file) {
            $result[substr($file, strlen(BP))] = [$file];
        }
        return $result;
    }
}
