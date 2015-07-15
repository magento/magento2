<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    private $designParams = [
        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
        'theme' => 'Magento/blank',
        'locale' => \Magento\Setup\Module\I18n\Locale::DEFAULT_SYSTEM_LOCALE,
        'module' => 'Fixture_ModuleOne',
    ];

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

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
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystem;

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
        $this->fileSystem = $this->getMock(
            '\Magento\Framework\Filesystem',
            [],
            [],
            '',
            false
        );
        $this->_model = $this->getMockBuilder('\Magento\Email\Model\Template\Config')
            ->setConstructorArgs(
                [
                    $this->_dataStorage,
                    $this->_moduleReader,
                    $this->fileSystem,
                    $this->viewFileSystem
                ]
            )
            ->setMethods(['getThemeTemplates'])
            ->getMock();
    }

    public function testGetAvailableTemplates()
    {
        $this->_model->expects($this->atLeastOnce())
            ->method('getThemeTemplates')
            ->will($this->returnValue([]));

        $expectedTemplates = require __DIR__ . '/Config/_files/email_templates_merged.php';

        foreach ($this->_model->getAvailableTemplates() as $templateOptions) {
            $this->assertArrayHasKey($templateOptions['value'], $expectedTemplates);
            $expectedOptions = $expectedTemplates[$templateOptions['value']];

            $this->assertEquals($expectedOptions['label'], (string) $templateOptions['label']);
            $this->assertEquals($expectedOptions['module'], (string) $templateOptions['group']);
        }
    }

    public function testGetThemeTemplates()
    {
        $themeDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->disableOriginalConstructor()
            ->setMethods(['search'])
            ->getMockForAbstractClass();

        $templates = require __DIR__ . '/Config/_files/email_templates_merged.php';

        $templateId = 'template_one';
        $template = $templates[$templateId];

        $area = $template['area'];
        $searchThemePath = '*/*';
        $foundThemePath = 'Vendor/custom_theme';
        $module = $template['module'];
        $filename = $template['file'];
        $themeDirectory->expects($this->once())
            ->method('search')
            ->with("{$area}/{$searchThemePath}/{$module}/email/{$filename}")
            ->will($this->returnValue(["{$area}/{$foundThemePath}/{$module}/email/{$filename}"]));

        $this->fileSystem->expects($this->once())
            ->method('getDirectoryRead')
            ->will($this->returnValue($themeDirectory));

        $this->_model = $this->getMockBuilder('\Magento\Email\Model\Template\Config')
            ->setConstructorArgs(
                [
                    $this->_dataStorage,
                    $this->_moduleReader,
                    $this->fileSystem,
                    $this->viewFileSystem
                ]
            )
            ->setMethods(null)
            ->getMock();

        foreach ($this->_model->getThemeTemplates($templateId) as $templateOptions) {
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
        $this->assertEquals($this->_model->parseTemplateIdParts($input), $expectedOutput);
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
        $this->assertEquals('Template One', $this->_model->getTemplateLabel('template_one'));
    }

    public function testGetTemplateType()
    {
        $this->assertEquals('html', $this->_model->getTemplateType('template_one'));
    }

    public function testGetTemplateModule()
    {
        $this->assertEquals('Fixture_ModuleOne', $this->_model->getTemplateModule('template_one'));
    }

    public function testGetTemplateArea()
    {
        $this->assertEquals('frontend', $this->_model->getTemplateArea('template_one'));
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

        $actualResult = $this->_model->getTemplateFilename('template_one', $this->designParams);
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

        $actualResult = $this->_model->getTemplateFilename('template_one');
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
            $this->_model->{$getterMethod}('unknown');
        } else {
            $this->_model->{$getterMethod}('unknown', $argument);
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
        $model = new \Magento\Email\Model\Template\Config(
            $dataStorage,
            $this->_moduleReader,
            $this->fileSystem,
            $this->viewFileSystem
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
