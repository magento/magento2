<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\Template\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filter\Template as FilterTemplate;

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
     * @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewFileSystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Email\Model\Template\FilterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailFilterFactory;

    /**
     * @var \Magento\Email\Model\Template\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailConfig;

    public function setUp()
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
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepo = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewFileSystem = $this->getMockBuilder('Magento\Framework\View\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailFilterFactory = $this->getMockBuilder('Magento\Email\Model\Template\FilterFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailConfig = $this->getMockBuilder('Magento\Email\Model\Template\Config')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param $mockedMethods array
     * @return \Magento\Email\Model\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModelMock(array $mockedMethods = [])
    {
        return $this->getMockBuilder('Magento\Email\Model\Template')
            ->setMethods(array_merge($mockedMethods, ['__wakeup', '__sleep', '_init']))
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->design,
                    $this->registry,
                    $this->appEmulation,
                    $this->storeManager,
                    $this->filesystem,
                    $this->assetRepo,
                    $this->viewFileSystem,
                    $this->scopeConfig,
                    $this->emailFilterFactory,
                    $this->emailConfig
                ]
            )
            ->getMock();
    }

    public function testGetDefaultEmailLogo()
    {
        $model = $this->getModelMock();
        $value = 'urlWithParamsValue';
        $this->assetRepo->method('getUrlWithParams')
            ->with('Magento_Email::logo_email.png', ['area' => \Magento\Framework\App\Area::AREA_FRONTEND])
            ->will($this->returnValue($value));
        $this->assertEquals($value, $model->getDefaultEmailLogo());
    }

    public function testSetAndGetTemplateFilter()
    {
        $model = $this->getModelMock();
        $filterTemplate = $this->getMockBuilder('Magento\Framework\Filter\Template')
            ->disableOriginalConstructor()
            ->getMock();
        $model->setTemplateFilter($filterTemplate);
        $this->assertSame($filterTemplate, $model->getTemplateFilter());
    }

    public function testGetTemplateFilterWithEmptyValue()
    {
        $filterTemplate = $this->getMockBuilder('Magento\Framework\Filter\Template')
            ->setMethods(['setUseAbsoluteLinks', 'setStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterTemplate->expects($this->once())
            ->method('setUseAbsoluteLinks')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $this->emailFilterFactory->method('create')
            ->will($this->returnValue($filterTemplate));
        $designConfig = $this->getMockBuilder('Magento\Framework\Object')
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
        $model = $this->getModelMock();

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
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MODULES)
            ->will($this->returnValue($modulesDir));

        $model->loadDefault($templateId);

        if ($templateType === 'html') {
            $this->assertEquals(\Magento\Email\Model\Template::TYPE_HTML, $model->getTemplateType());
        } else {
            $this->assertEquals(\Magento\Email\Model\Template::TYPE_TEXT, $model->getTemplateType());
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
                'templateText' => '<!-- Copyright © 2015 Magento. All rights reserved. -->',
                'parsedTemplateText' => '',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => null,
                'expectedTemplateStyles' => null,
            ],
            'copyright in HTML Remains' => [
                'templateType' => 'html',
                'templateText' => '<!-- Copyright © 2015 Magento. All rights reserved. -->',
                'parsedTemplateText' => '<!-- Copyright © 2015 Magento. All rights reserved. -->',
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
                'templateText' => '<!--@vars {"store url=\"\"":"Store Url"} @-->Some Other Text',
                'parsedTemplateText' => 'Some Other Text',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => '{"store url=\"\"":"Store Url"}',
                'expectedTemplateStyles' => null,
            ],
        ];
    }

    public function testLoadByCode()
    {
        $templateCode = 'templateCode';
        $templateData = ['templateData'];
        $resource = $this->getMockBuilder('Magento\Email\Model\Resource\Template')
            ->setMethods(['loadByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->once())
            ->method('loadByCode')
            ->with($templateCode)
            ->will($this->returnValue($templateData));
        $model = $this->getModelMock(['addData', 'getResource']);
        $model->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($resource));
        $model->expects($this->once())
            ->method('addData')
            ->with($templateData);
        $this->assertEquals($model, $model->loadByCode($templateCode));
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
            ->with('system/smtp/disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
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

    /**
     * @param $variables array
     * @param $templateType string
     * @param $storeId int
     * @param $expectedVariables array
     * @param $expectedResult string
     * @dataProvider getProcessedTemplateProvider
     */
    public function testGetProcessedTemplate($variables, $templateType, $storeId, $expectedVariables, $expectedResult)
    {
        $filterTemplate = $this->getMockBuilder('Magento\Framework\Filter\Template')
            ->setMethods([
                'setUseSessionInUrl',
                'setPlainTemplateMode',
                'setVariables',
                'setStoreId',
                'filter',
                'getStoreId',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $filterTemplate->expects($this->once())
            ->method('setUseSessionInUrl')
            ->with(false)
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')
            ->with($templateType === \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT)
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getFrontendName'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontendName'));
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $model = $this->getModelMock(['getDesignConfig', '_applyDesignConfig', 'getPreparedTemplateText']);
        $model->setTemplateFilter($filterTemplate);
        $model->setTemplateType($templateType);

        $designConfig = $this->getMockBuilder('Magento\Framework\Object')
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
            ->method('setVariables')
            ->with(array_merge([ 'this' => $model], $expectedVariables));

        $preparedTemplateText = 'prepared text';
        $model->expects($this->once())
            ->method('getPreparedTemplateText')
            ->will($this->returnValue($preparedTemplateText));
        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($preparedTemplateText)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $model->getProcessedTemplate($variables));
    }

    /**
     * @return array
     */
    public function getProcessedTemplateProvider()
    {
        return [
            'default' => [
                'variables' => [],
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT,
                'storeId' => 1,
                'expectedVariables' => [
                    'logo_url' => null,
                    'logo_alt' => 'frontendName',
                ],
                'expectedResult' => 'expected result',
            ],
            'logo variables set' => [
                'variables' => [
                    'logo_url' => 'http://example.com/logo',
                    'logo_alt' => 'Logo Alt',
                ],
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML,
                'storeId' => 1,
                'expectedVariables' => [
                    'logo_url' => 'http://example.com/logo',
                    'logo_alt' => 'Logo Alt',
                ],
                'expectedResult' => 'expected result',
            ],
        ];
    }

    /**
     * @param $templateType string
     * @param $templateStyles string
     * @param $templateText string
     * @param $expectedResult string
     * @dataProvider getPreparedTemplateTextProvider
     */
    public function testGetPreparedTemplateText($templateType, $templateStyles, $templateText, $expectedResult)
    {
        $model = $this->getModelMock();
        $model->setTemplateType($templateType);
        $model->setTemplateStyles($templateStyles);
        $model->setTemplateText($templateText);
        $this->assertEquals($expectedResult, $model->getPreparedTemplateText());
    }

    public function getPreparedTemplateTextProvider()
    {
        return [
            'plain text' => [
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT,
                'templateStyles' => '<style>',
                'templateText' => 'template text',
                'expectedResult' => 'template text',
            ],
            'html no style' => [
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML,
                'templateStyles' => '',
                'templateText' => 'template text',
                'expectedResult' => 'template text',
            ],
            'html with style' => [
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML,
                'templateStyles' => '.body { color: orange }',
                'templateText' => 'template text',
                'expectedResult' =>
                    '<style type="text/css">' . "\n.body { color: orange }\n</style>\n" . 'template text',
            ],
        ];
    }

    public function testGetProcessedTemplateSubject()
    {
        $model = $this->getModelMock(['getTemplateFilter', 'getDesignConfig', '_applyDesignConfig']);

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
            ->method('_applyDesignConfig');

        $designConfig = $this->getMockBuilder('Magento\Framework\Object')
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
                . '"escapehtml var=$customer.name":"Customer Name"}',
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
                        'value' => '{{escapehtml var=$customer.name}}',
                        'label' => __('%1', 'Customer Name'),
                    ],
                ],
            ],
            'customer account new variables with grouped option' => [
                'withGroup' => true,
                'templateVariables' => '{"store url=\"\"":"Store Url","var logo_url":"Email Logo Image Url",'
                . '"escapehtml var=$customer.name":"Customer Name"}',
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
                            'value' => '{{escapehtml var=$customer.name}}',
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
            'getProcessedTemplate'
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

        $vars = [ 'key' => 'value' ];
        $model->setVars($vars);
        $model->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($vars, true)
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

    public function testGetSubject()
    {
        $variables = [ 'key' => 'value' ];
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
        $emailConfig = $this->getMockBuilder(
            '\Magento\Email\Model\Template\Config'
        )->setMethods(
            ['getTemplateType']
        )->disableOriginalConstructor()->getMock();
        $emailConfig->expects($this->once())->method('getTemplateType')->will($this->returnValue($templateType));
        /** @var \Magento\Email\Model\Template $model */
        $model = $this->getMockBuilder(
            'Magento\Email\Model\Template'
        )->setMethods(
            ['_init']
        )->setConstructorArgs(
            [
                $this->getMock('Magento\Framework\Model\Context', [], [], '', false),
                $this->getMock('Magento\Theme\Model\View\Design', [], [], '', false),
                $this->getMock('Magento\Framework\Registry', [], [], '', false),
                $this->getMock('Magento\Store\Model\App\Emulation', [], [], '', false),
                $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false),
                $this->getMock('Magento\Framework\Filesystem', [], [], '', false),
                $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false),
                $this->getMock('Magento\Framework\View\FileSystem', [], [], '', false),
                $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
                $this->getMock('Magento\Email\Model\Template\FilterFactory', [], [], '', false),
                $emailConfig,
                ['template_id' => 10],
            ]
        )->getMock();
        $this->assertEquals($expectedResult, $model->getType());
    }

    public function getTypeDataProvider()
    {
        return [['text', 1], ['html', 2]];
    }
}
