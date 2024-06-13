<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Email\Model\Template\Config;
use Magento\Email\Model\Template\Config\Data;
use Magento\Framework\App\Area;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\View\Design\Theme\ThemePackage;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Framework\View\FileSystem;
use Magento\Setup\Module\I18n\Locale;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var array
     */
    private static $designParams = [
        'area' => Area::AREA_FRONTEND,
        'theme' => 'Magento/blank',
        'locale' => Locale::DEFAULT_SYSTEM_LOCALE,
        'module' => 'Fixture_ModuleOne',
    ];

    /**
     * @var Config
     */
    private $model;

    /**
     * @var Data|MockObject
     */
    protected $_dataStorage;

    /**
     * @var Reader|MockObject
     */
    protected $_moduleReader;

    /**
     * @var FileSystem|MockObject
     */
    protected $viewFileSystem;

    /**
     * @var ThemePackageList|MockObject
     */
    private $themePackages;

    /**
     * @var ReadFactory|MockObject
     */
    private $readDirFactory;

    protected function setUp(): void
    {
        $this->_dataStorage = $this->createPartialMock(Data::class, ['get']);
        $this->_dataStorage->expects(
            $this->any()
        )->method(
            'get'
        )->willReturn(
            require __DIR__ . '/Config/_files/email_templates_merged.php'
        );
        $this->_moduleReader = $this->createPartialMock(Reader::class, ['getModuleDir']);
        $this->viewFileSystem = $this->createPartialMock(
            FileSystem::class,
            ['getEmailTemplateFileName']
        );
        $this->themePackages = $this->createMock(ThemePackageList::class);
        $this->readDirFactory = $this->createMock(ReadFactory::class);
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
            $theme = $this->createMock(ThemePackage::class);
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
        $dir = $this->getMockForAbstractClass(ReadInterface::class);
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
        $theme = $this->createMock(ThemePackage::class);
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
        $dir = $this->getMockForAbstractClass(ReadInterface::class);
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
    public static function parseTemplateCodePartsDataProvider()
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
            self::$designParams,
            'Fixture_ModuleOne'
        )->willReturn(
            '_files/Fixture/ModuleOne/view/frontend/email/one.html'
        );

        $actualResult = $this->model->getTemplateFilename('template_one', self::$designParams);
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
                'area' => self::$designParams['area'],
                'module' => self::$designParams['module'],
            ],
            'Fixture_ModuleOne'
        )->willReturn(
            '_files/Fixture/ModuleOne/view/frontend/email/one.html'
        );

        $actualResult = $this->model->getTemplateFilename('template_one');
        $this->assertEquals('_files/Fixture/ModuleOne/view/frontend/email/one.html', $actualResult);
    }

    /**
     * @return void
     */
    public function testGetTemplateFilenameWrongFileName(): void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Template file \'one.html\' is not found');
        $this->viewFileSystem->expects($this->once())->method('getEmailTemplateFileName')
            ->with('one.html', self::$designParams, 'Fixture_ModuleOne')
            ->willReturn(false);

        $this->model->getTemplateFilename('template_one', self::$designParams);
    }

    /**
     * @param string $getterMethod
     * @param $argument
     * @dataProvider getterMethodUnknownTemplateDataProvider
     */
    public function testGetterMethodUnknownTemplate($getterMethod, $argument = null)
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Email template is not defined');
        if (!$argument) {
            $this->model->{$getterMethod}('unknown');
        } else {
            $this->model->{$getterMethod}('unknown', $argument);
        }
    }

    /**
     * @return array
     */
    public static function getterMethodUnknownTemplateDataProvider()
    {
        return [
            'label getter' => ['getTemplateLabel'],
            'type getter' => ['getTemplateType'],
            'module getter' => ['getTemplateModule'],
            'file getter' => ['getTemplateFilename', self::$designParams],
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
        $dataStorage = $this->createPartialMock(Data::class, ['get']);
        $dataStorage->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->willReturn(
            ['fixture' => $fixtureFields]
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
    public static function getterMethodUnknownFieldDataProvider()
    {
        return [
            'label getter' => ['getTemplateLabel', "Field 'label' is not defined for email template."],
            'type getter' => ['getTemplateType', "Field 'type' is not defined for email template."],
            'module getter' => [
                'getTemplateModule',
                "Field 'module' is not defined for email template.",
            ],
            'file getter, unknown module' => [
                'getTemplateFilename',
                "Field 'module' is not defined for email template.",
                [],
                self::$designParams,
            ],
            'file getter, unknown file' => [
                'getTemplateFilename',
                "Field 'file' is not defined for email template.",
                ['module' => 'Fixture_Module'],
                self::$designParams,
            ],
        ];
    }
}
