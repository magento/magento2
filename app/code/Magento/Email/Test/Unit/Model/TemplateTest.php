<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\Template\Filter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Filter\Template as FilterTemplate;
use Magento\Setup\Module\I18n\Locale;
use Magento\Store\Model\ScopeInterface;

/**
 * Covers \Magento\Email\Model\Template
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $design;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Store\Model\App\Emulation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Email\Model\Template\FilterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterManager;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlModel;

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailConfig;

    /**
     * @var \Magento\Email\Model\TemplateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateFactory;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->design = $this->getMockBuilder('Magento\Framework\View\DesignInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->appEmulation = $this->getMockBuilder('Magento\Store\Model\App\Emulation')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetRepo = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailConfig = $this->getMockBuilder('Magento\Email\Model\Template\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateFactory = $this->getMockBuilder('Magento\Email\Model\TemplateFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterManager = $this->getMockBuilder('Magento\Framework\Filter\FilterManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlModel = $this->getMockBuilder('Magento\Framework\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterFactory = $this->getMockBuilder('Magento\Email\Model\Template\FilterFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param array $mockedMethods
     * @return \Magento\Email\Model\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModelMock(array $mockedMethods = [])
    {
        return $this->getMockBuilder('Magento\Email\Model\Template')
            ->setMethods(array_merge($mockedMethods, ['__wakeup', '__sleep', '_init']))
            ->setConstructorArgs([
                $this->context,
                $this->design,
                $this->registry,
                $this->appEmulation,
                $this->storeManager,
                $this->assetRepo,
                $this->filesystem,
                $this->scopeConfig,
                $this->emailConfig,
                $this->templateFactory,
                $this->filterManager,
                $this->urlModel,
                $this->filterFactory,
            ])
            ->getMock();
    }

    public function testSetAndGetIsChildTemplate()
    {
        $model = $this->getModelMock();
        $model->setIsChildTemplate(true);
        $this->assertSame(true, $model->isChildTemplate());

        $model->setIsChildTemplate(false);
        $this->assertSame(false, $model->isChildTemplate());
    }

    public function testSetAndGetTemplateFilter()
    {
        $model = $this->getModelMock();
        $filterTemplate = $this->getMockBuilder('Magento\Email\Model\Template\Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $model->setTemplateFilter($filterTemplate);
        $this->assertSame($filterTemplate, $model->getTemplateFilter());
    }

    public function testGetTemplateFilterWithEmptyValue()
    {
        $filterTemplate = $this->getMockBuilder('Magento\Framework\Filter\Template')
            ->setMethods(['setUseAbsoluteLinks', 'setStoreId', 'setUrlModel'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterTemplate->expects($this->once())
            ->method('setUseAbsoluteLinks')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $this->filterFactory->method('create')
            ->will($this->returnValue($filterTemplate));
        $designConfig = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();

        $model = $this->getModelMock(['getUseAbsoluteLinks', 'getDesignConfig']);
        $model->expects($this->once())
            ->method('getDesignConfig')
            ->will($this->returnValue($designConfig));

        $this->assertSame($filterTemplate, $model->getTemplateFilter());
    }

    /**
     * @param $templateType string
     * @param $templateText string
     * @param $parsedTemplateText string
     * @param $expectedTemplateSubject string|null
     * @param $expectedOrigTemplateVariables array|null
     * @param $expectedTemplateStyles string|null
     * @dataProvider loadDefaultDataProvider
     */
    public function testLoadDefault(
        $templateType,
        $templateText,
        $parsedTemplateText,
        $expectedTemplateSubject,
        $expectedOrigTemplateVariables,
        $expectedTemplateStyles
    ) {
        $model = $this->getModelMock([
            'getDesignParams'
        ]);

        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'Magento/blank',
            'locale' => Locale::DEFAULT_SYSTEM_LOCALE,
        ];

        $model->expects($this->once())
            ->method('getDesignParams')
            ->will($this->returnValue($designParams));

        $templateId = 'templateId';

        $templateFile = 'templateFile';
        $this->emailConfig->expects($this->once())
            ->method('getTemplateFilename')
            ->with($templateId)
            ->will($this->returnValue($templateFile));
        $this->emailConfig->expects($this->once())
            ->method('getTemplateType')
            ->with($templateId)
            ->will($this->returnValue($templateType));

        $modulesDir = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->setMethods(['readFile', 'getRelativePath'])
            ->getMockForAbstractClass();

        $relativePath = 'relativePath';
        $modulesDir->expects($this->once())
            ->method('getRelativePath')
            ->with($templateFile)
            ->will($this->returnValue($relativePath));
        $modulesDir->expects($this->once())
            ->method('readFile')
            ->will($this->returnValue($templateText));

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue($modulesDir));

        $model->loadDefault($templateId);

        if ($templateType === 'html') {
            $this->assertEquals(TemplateTypesInterface::TYPE_HTML, $model->getTemplateType());
        } else {
            $this->assertEquals(TemplateTypesInterface::TYPE_TEXT, $model->getTemplateType());
        }
        $this->assertEquals($templateId, $model->getId());
        $this->assertEquals($parsedTemplateText, $model->getTemplateText());
        $this->assertEquals($expectedTemplateSubject, $model->getTemplateSubject());
        $this->assertEquals($expectedOrigTemplateVariables, $model->getData('orig_template_variables'));
        $this->assertEquals($expectedTemplateStyles, $model->getTemplateStyles());
    }

    public function loadDefaultDataProvider()
    {
        return [
            'empty' => [
                'templateType' => 'html',
                'templateText' => '',
                'parsedTemplateText' => '',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => null,
                'expectedTemplateStyles' => null,
            ],
            'copyright in Plain Text Removed' => [
                'templateType' => 'text',
                'templateText' => '<!-- Copyright © 2013-2017 Magento, Inc. All rights reserved. -->',
                'parsedTemplateText' => '',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => null,
                'expectedTemplateStyles' => null,
            ],
            'copyright in HTML Removed' => [
                'templateType' => 'html',
                'templateText' => '<!-- Copyright © 2013-2017 Magento, Inc. All rights reserved. -->',
                'parsedTemplateText' => '',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => null,
                'expectedTemplateStyles' => null,
            ],
            'subject set' => [
                'templateType' => 'html',
                'templateText' => '<!--@subject Email Subject @-->',
                'parsedTemplateText' => '',
                'expectedTemplateSubject' => 'Email Subject',
                'expectedOrigTemplateVariables' => null,
                'expectedTemplateStyles' => null,
            ],
            'orig_template_variables set' => [
                'templateType' => 'html',
                'templateText' => '<!--@vars {"store url=\"\"":"Store Url"} @-->Some Other Text',
                'parsedTemplateText' => 'Some Other Text',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => '{"store url=\"\"":"Store Url"}',
                'expectedTemplateStyles' => null,
            ],
            'styles' => [
                'templateType' => 'html',
                'templateText' => '<!--@styles p { color: #000; } @-->Some Other Text',
                'parsedTemplateText' => 'Some Other Text',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => null,
                'expectedTemplateStyles' => 'p { color: #000; }',
            ],
        ];
    }

    /**
     * Test to ensure that this method handles loading templates from DB vs filesystem, based on whether template ID is
     * numeric.
     *
     * @param bool $loadFromDatabase
     * @dataProvider loadByConfigPathDataProvider
     */
    public function testLoadByConfigPath($loadFromDatabase)
    {
        $configPath = 'design/email/header_template';
        $model = $this->getModelMock([
            'getDesignConfig',
            'loadDefault',
            'load',
            'getTemplateText',
            'setTemplateText',
        ]);

        $designConfig = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();

        $storeId = 'storeId';
        $designConfig->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeId));
        $model->expects($this->once())
            ->method('getDesignConfig')
            ->will($this->returnValue($designConfig));

        if ($loadFromDatabase) {
            $templateId = '1';
            $model->expects($this->once())
                ->method('load')
                ->with($templateId)
                ->will($this->returnSelf());
        } else {
            $templateId = 'design_email_header_template';
            $model->expects($this->once())
                ->method('loadDefault')
                ->with($templateId)
                ->will($this->returnSelf());
        }

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath, ScopeInterface::SCOPE_STORE, $storeId)
            ->will($this->returnValue($templateId));

        $model->loadByConfigPath($configPath);
    }

    /**
     * @return array
     */
    public function loadByConfigPathDataProvider()
    {
        return [
            'Load from filesystem' => [
                false,
                'Test template content',
                'Test template content',
            ],
            'Load from database' => [
                true,
                'Test template content',
                'Test template content',
            ],
        ];
    }

    public function testGetAndSetId()
    {
        $model = $this->getModelMock();
        $templateId = 'templateId';
        $this->assertEquals($model, $model->setId($templateId));
        $this->assertEquals($templateId, $model->getId());
    }

    /**
     * @param $isSMTPDisabled bool
     * @param $senderName string
     * @param $senderEmail string
     * @param $templateSubject string
     * @dataProvider isValidForSendDataProvider
     */
    public function testIsValidForSend($isSMTPDisabled, $senderName, $senderEmail, $templateSubject, $expectedValue)
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('system/smtp/disable', ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($isSMTPDisabled));
        $model = $this->getModelMock(['getSenderName', 'getSenderEmail', 'getTemplateSubject']);
        $model->expects($this->any())
            ->method('getSenderName')
            ->will($this->returnValue($senderName));
        $model->expects($this->any())
            ->method('getSenderEmail')
            ->will($this->returnValue($senderEmail));
        $model->expects($this->any())
            ->method('getTemplateSubject')
            ->will($this->returnValue($templateSubject));
        $this->assertEquals($expectedValue, $model->isValidForSend());
    }

    public function isValidForSendDataProvider()
    {
        return [
            'should be valid' => [
                'isSMTPDisabled' => false,
                'senderName' => 'sender name',
                'senderEmail' => 'email@example.com',
                'templateSubject' => 'template subject',
                'expectedValue' => true
            ],
            'no smtp so not valid' => [
                'isSMTPDisabled' => true,
                'senderName' => 'sender name',
                'senderEmail' => 'email@example.com',
                'templateSubject' => 'template subject',
                'expectedValue' => false
            ],
            'no sender name so not valid' => [
                'isSMTPDisabled' => false,
                'senderName' => '',
                'senderEmail' => 'email@example.com',
                'templateSubject' => 'template subject',
                'expectedValue' => false
            ],
            'no sender email so not valid' => [
                'isSMTPDisabled' => false,
                'senderName' => 'sender name',
                'senderEmail' => '',
                'templateSubject' => 'template subject',
                'expectedValue' => false
            ],
            'no subject so not valid' => [
                'isSMTPDisabled' => false,
                'senderName' => 'sender name',
                'senderEmail' => 'email@example.com',
                'templateSubject' => '',
                'expectedValue' => false
            ],
        ];
    }

    public function testGetProcessedTemplateSubject()
    {
        $model = $this->getModelMock(['getTemplateFilter', 'getDesignConfig', 'applyDesignConfig']);

        $templateSubject = 'templateSubject';
        $model->setTemplateSubject($templateSubject);

        $filterTemplate = $this->getMockBuilder('Magento\Framework\Filter\Template')
            ->setMethods(['setVariables', 'setStoreId', 'filter'])
            ->disableOriginalConstructor()
            ->getMock();
        $model->expects($this->once())
            ->method('getTemplateFilter')
            ->will($this->returnValue($filterTemplate));

        $model->expects($this->once())
            ->method('applyDesignConfig');

        $designConfig = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 'storeId';
        $designConfig->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeId));
        $model->expects($this->once())
            ->method('getDesignConfig')
            ->will($this->returnValue($designConfig));

        $filterTemplate->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $expectedResult = 'expected';
        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($templateSubject)
            ->will($this->returnValue($expectedResult));

        $variables = [ 'key' => 'value' ];
        $filterTemplate->expects($this->once())
            ->method('setVariables')
            ->with(array_merge($variables, ['this' => $model]));
        $this->assertEquals($expectedResult, $model->getProcessedTemplateSubject($variables));
    }

    /**
     * @param $withGroup bool
     * @param $templateVariables string
     * @param $expectedResult array
     * @dataProvider getVariablesOptionArrayDataProvider
     */
    public function testGetVariablesOptionArray($withGroup, $templateVariables, $expectedResult)
    {
        $model = $this->getModelMock();
        $model->setData('orig_template_variables', $templateVariables);
        $this->assertEquals($expectedResult, $model->getVariablesOptionArray($withGroup));
    }

    public function getVariablesOptionArrayDataProvider()
    {
        return [
            'empty variables' => [
                'withGroup' => false,
                'templateVariables' => '',
                'expectedResult' => [],
            ],
            'empty variables with grouped option' => [
                'withGroup' => true,
                'templateVariables' => '',
                'expectedResult' => [],
            ],
            'customer account new variables' => [
                'withGroup' => false,
                'templateVariables' => '{"store url=\"\"":"Store Url","var logo_url":"Email Logo Image Url",'
                . '"var customer.name":"Customer Name"}',
                'expectedResult' => [
                    [
                        'value' => '{{store url=""}}',
                        'label' => __('%1', 'Store Url'),
                    ],
                    [
                        'value' => '{{var logo_url}}',
                        'label' => __('%1', 'Email Logo Image Url'),
                    ],
                    [
                        'value' => '{{var customer.name}}',
                        'label' => __('%1', 'Customer Name'),
                    ],
                ],
            ],
            'customer account new variables with grouped option' => [
                'withGroup' => true,
                'templateVariables' => '{"store url=\"\"":"Store Url","var logo_url":"Email Logo Image Url",'
                . '"var customer.name":"Customer Name"}',
                'expectedResult' => [
                    'label' => __('Template Variables'),
                    'value' => [
                        [
                            'value' => '{{store url=""}}',
                            'label' => __('%1', 'Store Url'),
                        ],
                        [
                            'value' => '{{var logo_url}}',
                            'label' => __('%1', 'Email Logo Image Url'),
                        ],
                        [
                            'value' => '{{var customer.name}}',
                            'label' => __('%1', 'Customer Name'),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $templateId string|int
     * @param $expectedResult string
     * @dataProvider processTemplateVariable
     */
    public function testProcessTemplate($templateId, $expectedResult)
    {
        $model = $this->getModelMock([
            'load',
            'loadDefault',
            'getProcessedTemplate',
            'applyDesignConfig',
            'cancelDesignConfig',
        ]);
        $model->setId($templateId);
        if (is_numeric($templateId)) {
            $model->expects($this->once())
                ->method('load')
                ->with($templateId);
        } else {
            $model->expects($this->once())
                ->method('loadDefault')
                ->with($templateId);
        }

        $model->expects($this->once())
            ->method('applyDesignConfig')
            ->will($this->returnValue(true));
        $model->expects($this->once())
            ->method('cancelDesignConfig')
            ->will($this->returnValue(true));

        $vars = [ 'key' => 'value' ];
        $model->setVars($vars);
        $model->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($vars)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $model->processTemplate());
        $this->assertTrue($model->getUseAbsoluteLinks());
    }

    public function processTemplateVariable()
    {
        return [
            'numeric id' => [
                'templateId' => 1,
                'expectedResult' => 'expected result',
            ],
            'string id' => [
                'templateId' => 'my id',
                'expectedResult' => 'expected result',
            ],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\MailException
     */
    public function testProcessTemplateThrowsExceptionNonExistentTemplate()
    {
        $model = $this->getModelMock([
            'loadDefault',
            'applyDesignConfig',
        ]);
        $model->expects($this->once())
            ->method('loadDefault')
            ->will($this->returnValue(true));

        $model->expects($this->once())
            ->method('applyDesignConfig')
            ->will($this->returnValue(true));

        $model->processTemplate();
    }

    public function testGetSubject()
    {
        $variables = ['key' => 'value'];
        $model = $this->getModelMock(['getProcessedTemplateSubject']);
        $model->setVars($variables);
        $expectedResult = 'result';
        $model->expects($this->once())
            ->method('getProcessedTemplateSubject')
            ->with($variables)
            ->will($this->returnValue($expectedResult));
        $this->assertEquals($expectedResult, $model->getSubject());
    }

    public function testSetOptions()
    {
        $options = ['someOption' => 'someValue'];
        $model = $this->getModelMock(['setDesignConfig']);
        $model->expects($this->once())
            ->method('setDesignConfig')
            ->with($options);
        $model->setOptions($options);
    }

    /**
     * @dataProvider getTypeDataProvider
     * @param string $templateType
     * @param int $expectedResult
     */
    public function testGetType($templateType, $expectedResult)
    {
        $emailConfig = $this->getMockBuilder('\Magento\Email\Model\Template\Config')
            ->setMethods(['getTemplateType'])
            ->disableOriginalConstructor()
            ->getMock();

        $emailConfig->expects($this->once())->method('getTemplateType')->will($this->returnValue($templateType));

        /** @var \Magento\Email\Model\Template $model */
        $model = $this->getMockBuilder('Magento\Email\Model\Template')
            ->setMethods(['_init'])
            ->setConstructorArgs([
                $this->getMock('Magento\Framework\Model\Context', [], [], '', false),
                $this->getMock('Magento\Theme\Model\View\Design', [], [], '', false),
                $this->getMock('Magento\Framework\Registry', [], [], '', false),
                $this->getMock('Magento\Store\Model\App\Emulation', [], [], '', false),
                $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false),
                $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false),
                $this->getMock('Magento\Framework\Filesystem', [], [], '', false),
                $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
                $emailConfig,
                $this->getMock('Magento\Email\Model\TemplateFactory', [], [], '', false),
                $this->getMock('Magento\Framework\Filter\FilterManager', [], [], '', false),
                $this->getMock('Magento\Framework\Url', [], [], '', false),
                $this->getMock('Magento\Email\Model\Template\FilterFactory', [], [], '', false),
            ])
            ->getMock();

        $model->setTemplateId(10);

        $this->assertEquals($expectedResult, $model->getType());
    }

    public function getTypeDataProvider()
    {
        return [['text', 1], ['html', 2]];
    }
}
