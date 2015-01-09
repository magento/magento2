<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dirReadMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dirReadMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($this->dirReadMock);
    }

    public function testGetSchemaFile()
    {
        $config = $this->objectManager->getObject(
            'Magento\Framework\Config\Theme',
            ['configContent' => '<theme><title>Required Dummy</title><version>1</version></theme>']
        );
        $this->assertFileExists($config->getSchemaFile());
    }

    public function testThemeXmlTitleAndVersionOnly()
    {
        $themePath = 'test_themexml_only';
        $config = $this->createThemeConfig(file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/theme-only-title-version.xml'
        ), '');
        $this->assertSame('1.0', $config->getThemeVersion());
        $this->assertSame(['preview_image' => ''], $config->getMedia());
        $this->assertSame('Test', $config->getThemeTitle());
        $this->assertSame(null, $config->getParentTheme());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage The specified versions do not match
     */
    public function testItThrowsAnExceptionIfVersionsDoNotMatch()
    {
        $themePath = 'test_theme_composer_mismatch';
        $this->createThemeConfig(file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/version-mismatch.xml'
        ), file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/version-mismatch.json'
        ));
    }
    
    public function testThemeXmlTitleVersionAndParent()
    {
        $themePath = 'test_themexml_only';
        $config = $this->createThemeConfig(file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/theme-only-title-version-parent.xml'
        ), '');
        $this->assertSame('1.0', $config->getThemeVersion());
        $this->assertSame(['preview_image' => ''], $config->getMedia());
        $this->assertSame('Test', $config->getThemeTitle());
        $this->assertSame(['Test', 'parent'], $config->getParentTheme());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage The specified parent themes do not match
     */
    public function testItThrowsAnExceptionIfParentsDoNotMatch()
    {
        $themePath = 'test_theme_composer_mismatch';
        $this->createThemeConfig(file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/parent-mismatch.xml'
        ), file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/parent-mismatch.json'
        ));
    }
    
    public function testItThrowsNoExceptionIfVersionAndParentMatchInXmlAndComposer()
    {
        $themePath = 'test_theme_xml_composer_match';
        $config = $this->createThemeConfig(file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/theme.xml'
        ), file_get_contents(
            __DIR__ . '/_files/area/' . $themePath . '/composer.json'
        ));
        $this->assertSame(['Test', 'parent'], $config->getParentTheme());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Version configuration is missing from theme
     */
    public function testItThrowsAnExceptionIfTheVersionIsNotSpecifiedAnywhere()
    {
        $xmlConfig = '<theme><title>Test</title></theme>';
        $this->createThemeConfig($xmlConfig, '');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Theme title configuration is missing
     */
    public function testItThrowsAnExceptionIfTheTitleIsMissingInXmlConfig()
    {
        $xmlConfig = '<theme><version>1</version></theme>';
        $this->createThemeConfig($xmlConfig, '');
    }

    /**
     * @param string $themePath
     * @param array $expected
     * @dataProvider dataGetterDataProvider
     */
    public function testItCanCombineRequiredInfoFromThemeXmlAndComposer($themePath, $expected)
    {
        $expected = reset($expected);
        $config = $this->createThemeConfig(
            file_get_contents(__DIR__ . '/_files/area/' . $themePath . '/theme.xml'),
            file_get_contents(__DIR__ . '/_files/area/' . $themePath . '/composer.json')
        );
        $this->assertSame($expected['version'], $config->getThemeVersion());
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
                    'version' => '0.1.0',
                    'media' => ['preview_image' => 'media/default_default.jpg'],
                    'title' => 'Default',
                    'parent' => null,
                ]], ],
            [
                'default_test',
                [[
                    'version' => '0.1.1',
                    'media' => ['preview_image' => ''],
                    'title' => 'Test',
                    'parent' => ['Magento', 'default_default'],
                ]]],
            [
                'default_test2',
                [[
                    'version' => '0.1.2',
                    'media' => ['preview_image' => ''],
                    'title' => 'Test2',
                    'parent' => ['Magento', 'default_test'],
                ]]],
            [
                'test_default',
                [[
                    'version' => '0.1.3',
                    'media' => ['preview_image' => 'media/test_default.jpg'],
                    'title' => 'Default',
                    'parent' => null,
                ]]],
            [
                'test_external_package_descendant',
                [[
                    'version' => '0.1.4',
                    'media' => ['preview_image' => ''],
                    'title' => 'Default',
                    'parent' => ['Magento', 'default_test2'],
                ]]],
        ];
    }

    /**
     * @param string $xmlConfig
     * @param string $composerConfig
     * @return \Magento\Framework\Config\Theme
     */
    private function createThemeConfig($xmlConfig, $composerConfig)
    {
        return $this->objectManager->getObject(
            'Magento\Framework\Config\Theme',
            [
                'configContent' => $xmlConfig,
                'composerContent' => $composerConfig,
            ]
        );
    }
}
