<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Email\Model\AbstractTemplate.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\AbstractTemplate;
use Magento\Email\Model\Template;
use Magento\Email\Model\Template\Config;
use Magento\Email\Model\Template\Filter;
use Magento\Email\Model\Template\FilterFactory;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTemplateTest extends TestCase
{
    /**
     * @var DesignInterface|MockObject
     */
    private $design;

    /**
     * @var Emulation|MockObject
     */
    private $appEmulation;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

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
     * @var Config|MockObject
     */
    private $emailConfig;

    /**
     * @var TemplateFactory|MockObject
     */
    private $templateFactory;

    protected function setUp(): void
    {
        $this->design = $this->getMockBuilder(DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->appEmulation = $this->getMockBuilder(Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->store = $this->getMockBuilder(Store::class)
            ->setMethods(['getFrontendName', 'getId', 'getFormattedAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('frontendName');
        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('storeId');
        $this->store->expects($this->any())
            ->method('getFormattedAddress')
            ->willReturn("Test Store\n Street 1");
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepo = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->emailConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterFactory = $this->getMockBuilder(FilterFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFactory = $this->getMockBuilder(TemplateFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return the model under test with additional methods mocked.
     *
     * @param  array $mockedMethods
     * @param  array $data
     * @return Template|MockObject
     */
    protected function getModelMock(array $mockedMethods = [], array $data = [])
    {
        $helper = new ObjectManager($this);
        return $this->getMockForAbstractClass(
            AbstractTemplate::class,
            $helper->getConstructArguments(
                AbstractTemplate::class,
                [
                    'design' => $this->design,
                    'appEmulation' => $this->appEmulation,
                    'storeManager' => $this->storeManager,
                    'filesystem' => $this->filesystem,
                    'assetRepo' => $this->assetRepo,
                    'scopeConfig' => $this->scopeConfig,
                    'emailConfig' => $this->emailConfig,
                    'filterFactory' => $this->filterFactory,
                    'templateFactory' => $this->templateFactory,
                    'data' => $data,
                ]
            ),
            '',
            true,
            true,
            true,
            array_merge($mockedMethods, ['__wakeup', '__sleep', '_init'])
        );
    }

    /**
     * @param        $variables array
     * @param        $templateType string
     * @param        $storeId int
     * @param        $expectedVariables array
     * @param        $expectedResult string
     * @dataProvider getProcessedTemplateProvider
     */
    public function testGetProcessedTemplate($variables, $templateType, $storeId, $expectedVariables, $expectedResult)
    {
        $filterTemplate = $this->getMockBuilder(Filter::class)
            ->setMethods(
                [
                    'setUseSessionInUrl',
                    'setPlainTemplateMode',
                    'setIsChildTemplate',
                    'setDesignParams',
                    'setVariables',
                    'setStoreId',
                    'filter',
                    'getStoreId',
                    'getInlineCssFiles',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $filterTemplate->expects($this->never())
            ->method('setUseSessionInUrl')
            ->with(false)->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')
            ->with($templateType === TemplateTypesInterface::TYPE_TEXT)->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setDesignParams')->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('setStoreId')->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);

        $expectedVariables['store'] = $this->store;

        $model = $this->getModelMock(
            [
                'getDesignParams',
                'applyDesignConfig',
                'getTemplateText',
                'isPlain',
            ]
        );
        $filterTemplate->expects($this->any())
            ->method('setVariables')
            ->with(array_merge(['this' => $model], $expectedVariables));
        $model->setTemplateFilter($filterTemplate);
        $model->setTemplateType($templateType);
        $model->setTemplateId('123');

        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->any())
            ->method('getDesignParams')
            ->willReturn($designParams);

        $model->expects($this->atLeastOnce())
            ->method('isPlain')
            ->willReturn($templateType === TemplateTypesInterface::TYPE_TEXT);

        $preparedTemplateText = $expectedResult; //'prepared text';
        $model->expects($this->once())
            ->method('getTemplateText')
            ->willReturn($preparedTemplateText);

        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($preparedTemplateText)
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $model->getProcessedTemplate($variables));
    }

    public function testGetProcessedTemplateException()
    {
        $this->expectException('LogicException');
        $filterTemplate = $this->getMockBuilder(Filter::class)
            ->setMethods(
                [
                    'setPlainTemplateMode',
                    'setIsChildTemplate',
                    'setDesignParams',
                    'setVariables',
                    'setStoreId',
                    'filter',
                    'getStoreId',
                    'getInlineCssFiles',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')->willReturnSelf();
        $filterTemplate->expects($this->once())
            ->method('setDesignParams')->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('setStoreId')->willReturnSelf();
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);

        $model = $this->getModelMock(
            [
                'getDesignParams',
                'applyDesignConfig',
                'getTemplateText',
                'isPlain',
            ]
        );

        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->any())
            ->method('getDesignParams')
            ->willReturn($designParams);
        $model->setTemplateFilter($filterTemplate);
        $model->setTemplateType(TemplateTypesInterface::TYPE_TEXT);
        $model->setTemplateId('abc');

        $filterTemplate->expects($this->once())
            ->method('filter')
            ->willThrowException(new \Exception());
        $model->getProcessedTemplate([]);
    }

    /**
     * @return array
     */
    public function getProcessedTemplateProvider()
    {
        return [
            'default' => [
                'variables' => [],
                'templateType' => TemplateTypesInterface::TYPE_TEXT,
                'storeId' => 1,
                'expectedVariables' => [
                    'logo_url' => null,
                    'logo_alt' => 'frontendName',
                    'store' => null,
                    'logo_width' => null,
                    'logo_height' => null,
                    'store_phone' => null,
                    'store_hours' => null,
                    'store_email' => null,
                ],
                'expectedResult' => 'expected result',
            ],
            'logo variables set' => [
                'variables' => [
                    'logo_url' => 'http://example.com/logo',
                    'logo_alt' => 'Logo Alt',
                ],
                'templateType' => TemplateTypesInterface::TYPE_HTML,
                'storeId' => 1,
                'expectedVariables' => [
                    'logo_url' => 'http://example.com/logo',
                    'logo_alt' => 'Logo Alt',
                    'store' => null,
                    'logo_width' => null,
                    'logo_height' => null,
                    'store_phone' => null,
                    'store_hours' => null,
                    'store_email' => null,
                    'template_styles' => null,
                ],
                'expectedResult' => 'expected result',
            ],
        ];
    }

    public function testGetDefaultEmailLogo()
    {
        $model = $this->getModelMock(['getDesignParams']);
        $value = 'urlWithParamsValue';
        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->once())
            ->method('getDesignParams')
            ->willReturn($designParams);
        $this->assetRepo->method('getUrlWithParams')
            ->with(AbstractTemplate::DEFAULT_LOGO_FILE_ID, $designParams)
            ->willReturn($value);
        $this->assertEquals($value, $model->getDefaultEmailLogo());
    }

    /**
     * @param             array $config
     * @dataProvider      invalidInputParametersDataProvider
     */
    public function testSetDesignConfigWithInvalidInputParametersThrowsException($config)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->getModelMock()->setDesignConfig($config);
    }

    public function testSetDesignConfigWithValidInputParametersReturnsSuccess()
    {
        $config = ['area' => 'some_area', 'store' => 1];
        $model = $this->getModelMock();
        $model->setDesignConfig($config);
        $this->assertEquals($config, $model->getDesignConfig()->getData());
    }

    /**
     * @return array
     */
    public function invalidInputParametersDataProvider()
    {
        return [[[]], [['area' => 'some_area']], [['store' => 'any_store']]];
    }

    public function testEmulateDesignAndRevertDesign()
    {
        $model = $this->getModelMock();
        $originalConfig = ['area' => 'some_area', 'store' => 1];
        $model->setDesignConfig($originalConfig);

        $expectedConfigs = [
            ['in' => ['area' => 'frontend', 'store' => null], 'out' => $originalConfig],
            ['in' => ['area' => 'frontend', 'store' => false], 'out' => $originalConfig],
            ['in' => ['area' => 'frontend', 'store' => 0], 'out' => ['area' => 'frontend', 'store' => 0]],
            ['in' => ['area' => 'frontend', 'store' => 1], 'out' => ['area' => 'frontend', 'store' => 1]],
            ['in' => ['area' => 'frontend', 'store' => 2], 'out' => ['area' => 'frontend', 'store' => 2]],
        ];
        foreach ($expectedConfigs as $set) {
            $model->emulateDesign($set['in']['store'], $set['in']['area']);
            // assert config data has been emulated
            $this->assertEquals($set['out'], $model->getDesignConfig()->getData());

            $model->revertDesign();
            // assert config data has been reverted to the original state
            $this->assertEquals($originalConfig, $model->getDesignConfig()->getData());
        }
    }

    public function testGetDesignConfig()
    {
        $helper = new ObjectManager($this);

        $designMock = $this->getMockForAbstractClass(DesignInterface::class);
        $designMock->expects($this->any())->method('getArea')->willReturn('test_area');

        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())->method('getId')->willReturn(2);
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $model = $this->getMockForAbstractClass(
            AbstractTemplate::class,
            $helper->getConstructArguments(
                AbstractTemplate::class,
                [
                    'design' => $designMock,
                    'storeManager' => $storeManagerMock
                ]
            )
        );

        $expectedConfig = ['area' => 'test_area', 'store' => 2];
        $this->assertEquals($expectedConfig, $model->getDesignConfig()->getData());
    }

    /**
     * @return void
     */
    public function testSetForcedAreaWhenAreaIsNotSet(): void
    {
        $templateId = 'test_template';
        $model = $this->getModelMock([], ['area' => null]);

        $this->emailConfig->expects($this->once())
            ->method('getTemplateArea')
            ->with($templateId);

        $model->setForcedArea($templateId);
    }

    /**
     * @return void
     */
    public function testSetForcedAreaWhenAreaIsSet(): void
    {
        $templateId = 'test_template';
        $model = $this->getModelMock([], ['area' => 'frontend']);

        $this->emailConfig->expects($this->never())
            ->method('getTemplateArea');

        $model->setForcedArea($templateId);
    }
}
