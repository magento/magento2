<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Theme;

use Magento\Framework\Component\ComponentRegistrar;

class XmlFilesTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    const NO_VIEW_XML_FILES_MARKER = 'no-view-xml';

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
    }

    /**
     * @param string $file
     * @dataProvider viewConfigFileDataProvider
     */
    public function testViewConfigFile($file)
    {
        $this->_validateConfigFile(
            $file,
            $this->urnResolver->getRealPath('urn:magento:library:framework:Config/etc/view.xsd')
        );
    }

    /**
     * @return array
     */
    public function viewConfigFileDataProvider()
    {
        $result = [];
        /** @var \Magento\Framework\Component\DirSearch $componentDirSearch */
        $componentDirSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Component\DirSearch');
        $files = $componentDirSearch->collectFiles(ComponentRegistrar::THEME, 'etc/view.xml');
        foreach ($files as $file) {
            $result[$file] = [$file];
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
            ->get('\Magento\Framework\Component\ComponentRegistrar');
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $themeDir) {
            $result[$themeDir] = [$themeDir];
        }
        return $result;
    }

    /**
     * @param string $file
     * @dataProvider themeConfigFileDataProvider
     */
    public function testThemeConfigFileSchema($file)
    {
        $this->_validateConfigFile(
            $file,
            $this->urnResolver->getRealPath('urn:magento:library:framework:Config/etc/theme.xsd')
        );
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
            ->get('Magento\Framework\Component\DirSearch');
        $files = $componentDirSearch->collectFiles(ComponentRegistrar::THEME, 'theme.xml');
        foreach ($files as $file) {
            $result[$file] = [$file];
        }
        return $result;
    }

    /**
     * Perform test whether a configuration file is valid
     *
     * @param string $file
     * @param string $schemaFile
     * @throws \PHPUnit_Framework_AssertionFailedError if file is invalid
     */
    protected function _validateConfigFile($file, $schemaFile)
    {
        $domConfig = new \Magento\Framework\Config\Dom(file_get_contents($file));
        $errors = [];
        $result = $domConfig->validate($schemaFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error->message} Line: {$error->line}\n";
        }
        $this->assertTrue($result, $message);
    }
}
