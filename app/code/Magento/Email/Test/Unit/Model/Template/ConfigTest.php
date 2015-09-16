<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Email\Model\Template\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
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
        $this->_dataStorage = $this->getMock(
            'Magento\Email\Model\Template\Config\Data',
            ['get'],
            [],
            '',
            false
        );
        $this->_dataStorage->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(require __DIR__ . '/Config/_files/email_templates_merged.php')
        );
        $this->_moduleReader = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            ['getModuleDir'],
            [],
            '',
            false
        );
        $this->viewFileSystem = $this->getMock(
            '\Magento\Framework\View\FileSystem',
            ['getEmailTemplateFileName'],
            [],
            '',
            false
        );
        $this->themePackages = $this->getMock(
            '\Magento\Framework\View\Design\Theme\ThemePackageList',
            [],
            [],
            '',
            false
        );
        $this->themePackages->expects($this->any())
            ->method('getThemes')
            ->will($this->returnValue([]));
        $this->readDirFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
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
        $expectedTemplates = require __DIR__ . '/Config/_files/email_templates_merged.php';

        foreach ($this->model->getAvailableTemplates() as $templateOptions) {
            $this->assertArrayHasKey($templateOptions['value'], $expectedTemplates);
            $expectedOptions = $expectedTemplates[$templateOptions['value']];

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

        foreach ($this->model->getThemeTemplates($templateId) as $templateOptions) {
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
        $this->setExpectedException('UnexpectedValueException', $expectedException);
        $dataStorage = $this->getMock('Magento\Email\Model\Template\Config\Data', ['get'], [], '', false);
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
