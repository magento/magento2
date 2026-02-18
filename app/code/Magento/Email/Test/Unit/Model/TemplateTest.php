<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\Template;
use Magento\Email\Model\Template\Config;
use Magento\Email\Model\Template\Filter;
use Magento\Email\Model\Template\FilterFactory;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\DesignInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Setup\Module\I18n\Locale;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\View\Design;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Email\Model\ResourceModel\Template as TemplateResourceModel;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Filter\Template as TemplateFilter;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Covers \Magento\Email\Model\Template
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class TemplateTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var DesignInterface|MockObject
     */
    private $design;

    /**
     * @var Registry|MockObject
     * @deprecated since 2.3.0 in favor of stateful global objects elimination.
     * @see \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Emulation|MockObject
     */
    private $appEmulation;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var FilterFactory|MockObject
     */
    private $filterFactory;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManager;

    /**
     * @var Url|MockObject
     */
    private $urlModel;

    /**
     * @var Config|MockObject
     */
    private $emailConfig;

    /**
     * @var TemplateFactory|MockObject
     */
    private $templateFactory;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                Database::class,
                $this->createMock(Database::class)
            ],
            [
                TemplateResourceModel::class,
                $this->createMock(TemplateResourceModel::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->design = $this->createMock(DesignInterface::class);

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appEmulation = $this->getMockBuilder(Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $this->assetRepo = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->emailConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateFactory = $this->getMockBuilder(TemplateFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterManager = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlModel = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterFactory = $this->getMockBuilder(FilterFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param array $mockedMethods
     * @param array $addMockedMethods
     * @return Template|MockObject
     */
    protected function getModelMock(array $mockedMethods = [], array $addMockedMethods = [])
    {
        $allMethods = array_merge($mockedMethods, $addMockedMethods, ['__wakeup', '__sleep', '_init']);
        $mock = $this->createPartialMockWithReflection(Template::class, $allMethods);
        
        $this->addPropertyValue($mock, [
            'context' => $this->context,
            'design' => $this->design,
            '_registry' => $this->registry,
            'appEmulation' => $this->appEmulation,
            'storeManager' => $this->storeManager,
            'assetRepo' => $this->assetRepo,
            'filesystem' => $this->filesystem,
            'scopeConfig' => $this->scopeConfig,
            'emailConfig' => $this->emailConfig,
            'templateFactory' => $this->templateFactory,
            '_filterManager' => $this->filterManager,
            'urlModel' => $this->urlModel,
            'filterFactory' => $this->filterFactory,
            'serializer' => $this->serializerMock,
        ], null, false);
        
        return $mock;
    }

    public function testSetAndGetIsChildTemplate()
    {
        $model = $this->getModelMock();
        $model->setIsChildTemplate(true);
        $this->assertTrue($model->isChildTemplate());

        $model->setIsChildTemplate(false);
        $this->assertFalse($model->isChildTemplate());
    }

    public function testSetAndGetTemplateFilter()
    {
        $model = $this->getModelMock();
        $filterTemplate = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model->setTemplateFilter($filterTemplate);
        $this->assertSame($filterTemplate, $model->getTemplateFilter());
    }

    public function testGetTemplateFilterWithEmptyValue()
    {
        $filterTemplate = $this->createPartialMockWithReflection(
            TemplateFilter::class,
            ['setUseAbsoluteLinks', 'setStoreId', 'setUrlModel']
        );
        $filterTemplate->expects($this->once())
            ->method('setUseAbsoluteLinks')->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setStoreId')->willReturnSelf();
        $this->filterFactory->method('create')
            ->willReturn($filterTemplate);
        $designConfig = $this->createPartialMockWithReflection(DataObject::class, ['getStore']);

        $model = $this->getModelMock(['getDesignConfig'], ['getUseAbsoluteLinks']);
        $model->expects($this->once())
            ->method('getDesignConfig')
            ->willReturn($designConfig);

        $this->assertSame($filterTemplate, $model->getTemplateFilter());
    }

    /**
     * @param $templateType string
     * @param $templateText string
     * @param $parsedTemplateText string
     * @param $expectedTemplateSubject string|null
     * @param $expectedOrigTemplateVariables array|null
     * @param $expectedTemplateStyles string|null
     */
    #[DataProvider('loadDefaultDataProvider')]
    public function testLoadDefault(
        $templateType,
        $templateText,
        $parsedTemplateText,
        $expectedTemplateSubject,
        $expectedOrigTemplateVariables,
        $expectedTemplateStyles
    ) {
        $model = $this->getModelMock(['getDesignParams']);

        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'Magento/blank',
            'locale' => Locale::DEFAULT_SYSTEM_LOCALE,
        ];

        $model->expects($this->once())
            ->method('getDesignParams')
            ->willReturn($designParams);

        $templateId = 'templateId';

        $templateFile = 'templateFile';
        $this->emailConfig->expects($this->once())
            ->method('getTemplateFilename')
            ->with($templateId)
            ->willReturn($templateFile);
        $this->emailConfig->expects($this->once())
            ->method('getTemplateType')
            ->with($templateId)
            ->willReturn($templateType);

        $modulesDir = $this->createMock(ReadInterface::class);

        $relativePath = 'relativePath';
        $modulesDir->expects($this->once())
            ->method('getRelativePath')
            ->with($templateFile)
            ->willReturn($relativePath);
        $modulesDir->expects($this->once())
            ->method('readFile')
            ->willReturn($templateText);

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($modulesDir);

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

    /**
     * @return array
     */
    public static function loadDefaultDataProvider()
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
                'templateText' => '<!-- Copyright 2024 Adobe All Rights Reserved. -->',
                'parsedTemplateText' => '',
                'expectedTemplateSubject' => null,
                'expectedOrigTemplateVariables' => null,
                'expectedTemplateStyles' => null,
            ],
            'copyright in HTML Removed' => [
                'templateType' => 'html',
                'templateText' => '<!-- Copyright 2024 Adobe All Rights Reserved. -->',
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
     */
    #[DataProvider('loadByConfigPathDataProvider')]
    public function testLoadByConfigPath($loadFromDatabase)
    {
        $configPath = 'design/email/header_template';
        $model = $this->getModelMock(
            [
                'getDesignConfig',
                'loadDefault',
                'load',
            ],
            [
                'getTemplateText',
                'setTemplateText',
            ]
        );

        $designConfig = $this->createPartialMockWithReflection(DataObject::class, ['getStore']);

        $storeId = 'storeId';
        $designConfig->expects($this->once())
            ->method('getStore')
            ->willReturn($storeId);
        $model->expects($this->once())
            ->method('getDesignConfig')
            ->willReturn($designConfig);

        if ($loadFromDatabase) {
            $templateId = '1';
            $model->expects($this->once())
                ->method('load')
                ->with($templateId)->willReturnSelf();
        } else {
            $templateId = 'design_email_header_template';
            $model->expects($this->once())
                ->method('loadDefault')
                ->with($templateId)->willReturnSelf();
        }

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($templateId);

        $model->loadByConfigPath($configPath);
    }

    /**
     * @return array
     */
    public static function loadByConfigPathDataProvider()
    {
        return [
            'Load from filesystem' => [
                false,
            ],
            'Load from database' => [
                true,
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
     * @param $senderName string
     * @param $senderEmail string
     * @param $templateSubject string
     */
    #[DataProvider('isValidForSendDataProvider')]
    public function testIsValidForSend($senderName, $senderEmail, $templateSubject, $expectedValue)
    {
        $model = $this->getModelMock([], ['getSenderName', 'getSenderEmail', 'getTemplateSubject']);
        $model->expects($this->any())
            ->method('getSenderName')
            ->willReturn($senderName);
        $model->expects($this->any())
            ->method('getSenderEmail')
            ->willReturn($senderEmail);
        $model->expects($this->any())
            ->method('getTemplateSubject')
            ->willReturn($templateSubject);
        $this->assertEquals($expectedValue, $model->isValidForSend());
    }

    /**
     * @return array
     */
    public static function isValidForSendDataProvider()
    {
        return [
            'should be valid' => [
                'senderName' => 'sender name',
                'senderEmail' => 'email@example.com',
                'templateSubject' => 'template subject',
                'expectedValue' => true
            ],
            'no sender name so not valid' => [
                'senderName' => '',
                'senderEmail' => 'email@example.com',
                'templateSubject' => 'template subject',
                'expectedValue' => false
            ],
            'no sender email so not valid' => [
                'senderName' => 'sender name',
                'senderEmail' => '',
                'templateSubject' => 'template subject',
                'expectedValue' => false
            ],
            'no subject so not valid' => [
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
        $model->setTemplateId('123');

        class_exists(Template::class, true);
        $filterTemplate = $this->createPartialMockWithReflection(
            TemplateFilter::class,
            ['setStoreId', 'setVariables', 'filter']
        );
        $model->expects($this->once())
            ->method('getTemplateFilter')
            ->willReturn($filterTemplate);

        $model->expects($this->once())
            ->method('applyDesignConfig');

        $designConfig = $this->createPartialMockWithReflection(DataObject::class, ['getStore']);
        $storeId = 'storeId';
        $designConfig->expects($this->once())
            ->method('getStore')
            ->willReturn($storeId);
        $model->expects($this->once())
            ->method('getDesignConfig')
            ->willReturn($designConfig);

        $filterTemplate->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)->willReturnSelf();
        $expectedResult = 'expected';
        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($templateSubject)
            ->willReturn($expectedResult);

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
     */
    #[DataProvider('getVariablesOptionArrayDataProvider')]
    public function testGetVariablesOptionArray($withGroup, $templateVariables, $expectedResult)
    {
        $model = $this->getModelMock();
        $model->setData('orig_template_variables', $templateVariables);

        $this->serializerMock->expects($this->any())->method('unserialize')
            ->willReturn(
                json_decode($templateVariables, true)
            );
        $this->assertEquals($expectedResult, $model->getVariablesOptionArray($withGroup));
    }

    /**
     * @return array
     */
    public static function getVariablesOptionArrayDataProvider()
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
     */
    #[DataProvider('processTemplateVariable')]
    public function testProcessTemplate($templateId, $expectedResult)
    {
        $model = $this->getModelMock(
            [
                'load',
                'loadDefault',
                'getProcessedTemplate',
                'applyDesignConfig',
                'cancelDesignConfig',
            ]
        );
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
            ->willReturn(true);
        $model->expects($this->once())
            ->method('cancelDesignConfig')
            ->willReturn(true);

        $vars = [ 'key' => 'value' ];
        $model->setVars($vars);
        $model->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($vars)
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $model->processTemplate());
        $this->assertTrue($model->getUseAbsoluteLinks());
    }

    /**
     * @return array
     */
    public static function processTemplateVariable()
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

    public function testProcessTemplateThrowsExceptionNonExistentTemplate()
    {
        $this->expectException('Magento\Framework\Exception\MailException');
        $model = $this->getModelMock(['loadDefault', 'applyDesignConfig']);
        $model->expects($this->once())
            ->method('loadDefault')
            ->willReturn(true);

        $model->expects($this->once())
            ->method('applyDesignConfig')
            ->willReturn(true);

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
            ->willReturn($expectedResult);
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
     * @param string $templateType
     * @param int $expectedResult
     */
    #[DataProvider('getTypeDataProvider')]
    public function testGetType($templateType, $expectedResult)
    {
        $emailConfig = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getTemplateType'])
            ->disableOriginalConstructor()
            ->getMock();

        $emailConfig->expects($this->once())->method('getTemplateType')->willReturn($templateType);

        /** @var Template $model */
        $model = $this->getMockBuilder(Template::class)
            ->onlyMethods(['_init'])
            ->setConstructorArgs(
                [
                    $this->createMock(Context::class),
                    $this->createMock(Design::class),
                    $this->createMock(Registry::class),
                    $this->createMock(Emulation::class),
                    $this->createMock(StoreManager::class),
                    $this->createMock(Repository::class),
                    $this->createMock(Filesystem::class),
                    $this->createMock(ScopeConfigInterface::class),
                    $emailConfig,
                    $this->createMock(TemplateFactory::class),
                    $this->createMock(FilterManager::class),
                    $this->createMock(Url::class),
                    $this->createMock(FilterFactory::class),
                    [],
                    $this->createMock(Json::class)
                ]
            )
            ->getMock();

        $model->setTemplateId(10);

        $this->assertEquals($expectedResult, $model->getType());
    }

    /**
     * @return array
     */
    public static function getTypeDataProvider()
    {
        return [['text', 1], ['html', 2]];
    }
}
