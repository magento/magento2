<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Email\Model\Template\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    private $designParams = [
        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
        'theme' => 'Magento/blank',
        'locale' => \Magento\Setup\Module\I18n\Locale::DEFAULT_SYSTEM_LOCALE,
        'module' => 'Fixture_ModuleOne',
    ];

    /**
     * @var Config
     */
    private $model;

    /**
     * @var \Magento\Email\Model\Template\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataStorage;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemePackageList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themePackages;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readDirFactory;

    protected function setUp()
    {
        $this->_dataStorage = $this->createPartialMock(\Magento\Email\Model\Template\Config\Data::class, ['get']);
        $this->_dataStorage->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(require __DIR__ . '/Config/_files/email_templates_merged.php')
        );
        $this->_moduleReader = $this->createPartialMock(\Magento\Framework\Module\Dir\Reader::class, ['getModuleDir']);
        $this->viewFileSystem = $this->createPartialMock(
            \Magento\Framework\View\FileSystem::class,
            ['getEmailTemplateFileName']
        );
        $this->themePackages = $this->createMock(\Magento\Framework\View\Design\Theme\ThemePackageList::class);
        $this->readDirFactory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $this->model = new Config(
            $this->_dataStorage,
            $this->_moduleReader,
            $this->viewFileSystem,
            $this->themePackages,
            $this->readDirFactory
        );
    }

    public function testGetAvailableTemplates()
    {
        $templates = require __DIR__ . '/Config/_files/email_templates_merged.php';

        $themes = [];
        $i = 1;
        foreach ($templates as $templateData) {
            $theme = $this->createMock(\Magento\Framework\View\Design\Theme\ThemePackage::class);
            $theme->expects($this->any())
                ->method('getArea')
                ->willReturn($templateData['area']);
            $theme->expects($this->any())
                ->method('getVendor')
                ->willReturn('Vendor');
            $theme->expects($this->any())
                ->method('getName')
                ->willReturn('custom_theme');
            $theme->expects($this->any())
                ->method('getPath')
                ->willReturn('/theme/path');
            $themes[] = $theme;
            $i++;
        }
        $this->themePackages->expects($this->exactly(count($templates)))
            ->method('getThemes')
            ->willReturn($themes);
        $dir = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->willReturn($dir);
        $dir->expects($this->any())
            ->method('isExist')
            ->willReturn(true);

        $expected = [
            'template_one' => ['label' => 'Template One', 'module' => 'Fixture_ModuleOne'],
            'template_two' => ['label' => 'Template 2', 'module' => 'Fixture_ModuleTwo'],
            'template_one/Vendor/custom_theme' => [
                'label' => 'Template One (Vendor/custom_theme)',
                'module' => 'Fixture_ModuleOne'
            ],
            'template_two/Vendor/custom_theme' => [
                'label' => 'Template 2 (Vendor/custom_theme)',
                'module' => 'Fixture_ModuleTwo'
            ],
        ];

        $actualTemplates = $this->model->getAvailableTemplates();
        $this->assertCount(count($expected), $actualTemplates);
        foreach ($actualTemplates as $templateOptions) {
            $this->assertArrayHasKey($templateOptions['value'], $expected);
            $expectedOptions = $expected[$templateOptions['value']];

            $this->assertEquals($expectedOptions['label'], (string) $templateOptions['label']);
            $this->assertEquals($expectedOptions['module'], (string) $templateOptions['group']);
        }
    }

    public function testGetThemeTemplates()
    {
        $templates = require __DIR__ . '/Config/_files/email_templates_merged.php';

        $templateId = 'template_one';
        $template = $templates[$templateId];

        $foundThemePath = 'Vendor/custom_theme';
        $theme = $this->createMock(\Magento\Framework\View\Design\Theme\ThemePackage::class);
        $theme->expects($this->any())
            ->method('getArea')
            ->willReturn('frontend');
        $theme->expects($this->any())
            ->method('getVendor')
            ->willReturn('Vendor');
        $theme->expects($this->any())
            ->method('getName')
            ->willReturn('custom_theme');
        $theme->expects($this->any())
            ->method('getPath')
            ->willReturn('/theme/path');
        $this->themePackages->expects($this->once())
            ->method('getThemes')
            ->willReturn([$theme]);
        $dir = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->readDirFactory->expects($this->once())
            ->method('create')
            ->with('/theme/path')
            ->willReturn($dir);
        $dir->expects($this->once())
            ->method('isExist')
            ->willReturn(true);

        $actualTemplates = $this->model->getThemeTemplates($templateId);
        $this->assertNotEmpty($actualTemplates);
        foreach ($actualTemplates as $templateOptions) {
            $this->assertEquals(
                sprintf(
                    '%s (%s)',
                    $template['label'],
                    $foundThemePath
                ),
                $templateOptions['label']
            );
            $this->assertEquals(sprintf('%s/%s', $templateId, $foundThemePath), $templateOptions['value']);
            $this->assertEquals($template['module'], $templateOptions['group']);
        }
    }

    /**
     * @dataProvider parseTemplateCodePartsDataProvider
     *
     * @param string $input
     * @param array $expectedOutput
     */
    public function testParseTemplateIdParts($input, $expectedOutput)
    {
        $this->assertEquals($this->model->parseTemplateIdParts($input), $expectedOutput);
    }

    /**
     * @return array
     */
    public function parseTemplateCodePartsDataProvider()
    {
        return [
            'Template ID with no theme' => [
                'random_template_code',
                [
                    'templateId' => 'random_template_code',
                    'theme' => null,
                ],
            ],
            'Template ID with theme' => [
                'random_template_code/Vendor/CustomTheme',
                [
                    'templateId' => 'random_template_code',
                    'theme' => 'Vendor/CustomTheme',
                ],
            ],
        ];
    }

    public function testGetTemplateLabel()
    {
        $this->assertEquals('Template One', $this->model->getTemplateLabel('template_one'));
    }

    public function testGetTemplateType()
    {
        $this->assertEquals('html', $this->model->getTemplateType('template_one'));
    }

    public function testGetTemplateModule()
    {
        $this->assertEquals('Fixture_ModuleOne', $this->model->getTemplateModule('template_one'));
    }

    public function testGetTemplateArea()
    {
        $this->assertEquals('frontend', $this->model->getTemplateArea('template_one'));
    }

    public function testGetTemplateFilenameWithParams()
    {
        $this->viewFileSystem->expects(
            $this->once()
        )->method(
            'getEmailTemplateFileName'
        )->with(
            'one.html',
            $this->designParams,
            'Fixture_ModuleOne'
        )->will(
            $this->returnValue('_files/Fixture/ModuleOne/view/frontend/email/one.html')
        );

        $actualResult = $this->model->getTemplateFilename('template_one', $this->designParams);
        $this->assertEquals('_files/Fixture/ModuleOne/view/frontend/email/one.html', $actualResult);
    }

    /**
     * Ensure that the getTemplateFilename method can be called without design params
     */
    public function testGetTemplateFilenameWithNoParams()
    {
        $this->viewFileSystem->expects(
            $this->once()
        )->method(
            'getEmailTemplateFileName'
        )->with(
            'one.html',
            [
                'area' => $this->designParams['area'],
                'module' => $this->designParams['module'],
            ],
            'Fixture_ModuleOne'
        )->will(
            $this->returnValue('_files/Fixture/ModuleOne/view/frontend/email/one.html')
        );

        $actualResult = $this->model->getTemplateFilename('template_one');
        $this->assertEquals('_files/Fixture/ModuleOne/view/frontend/email/one.html', $actualResult);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Template file 'one.html' is not found
     * @return void
     */
    public function testGetTemplateFilenameWrongFileName(): void
    {
        $this->viewFileSystem->expects($this->once())->method('getEmailTemplateFileName')
            ->with('one.html', $this->designParams, 'Fixture_ModuleOne')
            ->willReturn(false);

        $this->model->getTemplateFilename('template_one', $this->designParams);
    }

    /**
     * @param string $getterMethod
     * @param $argument
     * @dataProvider getterMethodUnknownTemplateDataProvider
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Email template 'unknown' is not defined
     */
    public function testGetterMethodUnknownTemplate($getterMethod, $argument = null)
    {
        if (!$argument) {
            $this->model->{$getterMethod}('unknown');
        } else {
            $this->model->{$getterMethod}('unknown', $argument);
        }
    }

    /**
     * @return array
     */
    public function getterMethodUnknownTemplateDataProvider()
    {
        return [
            'label getter' => ['getTemplateLabel'],
            'type getter' => ['getTemplateType'],
            'module getter' => ['getTemplateModule'],
            'file getter' => ['getTemplateFilename', $this->designParams],
        ];
    }

    /**
     * @param string $getterMethod
     * @param string $expectedException
     * @param array $fixtureFields
     * @param $argument
     * @dataProvider getterMethodUnknownFieldDataProvider
     */
    public function testGetterMethodUnknownField(
        $getterMethod,
        $expectedException,
        array $fixtureFields = [],
        $argument = null
    ) {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage($expectedException);
        $dataStorage = $this->createPartialMock(\Magento\Email\Model\Template\Config\Data::class, ['get']);
        $dataStorage->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->will(
            $this->returnValue(['fixture' => $fixtureFields])
        );
        $model = new Config(
            $dataStorage,
            $this->_moduleReader,
            $this->viewFileSystem,
            $this->themePackages,
            $this->readDirFactory
        );
        if (!$argument) {
            $model->{$getterMethod}('fixture');
        } else {
            $model->{$getterMethod}('fixture', $argument);
        }
    }

    /**
     * @return array
     */
    public function getterMethodUnknownFieldDataProvider()
    {
        return [
            'label getter' => ['getTemplateLabel', "Field 'label' is not defined for email template 'fixture'."],
            'type getter' => ['getTemplateType', "Field 'type' is not defined for email template 'fixture'."],
            'module getter' => [
                'getTemplateModule',
                "Field 'module' is not defined for email template 'fixture'.",
            ],
            'file getter, unknown module' => [
                'getTemplateFilename',
                "Field 'module' is not defined for email template 'fixture'.",
                [],
                $this->designParams,
            ],
            'file getter, unknown file' => [
                'getTemplateFilename',
                "Field 'file' is not defined for email template 'fixture'.",
                ['module' => 'Fixture_Module'],
                $this->designParams,
            ],
        ];
    }
}
