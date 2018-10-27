<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Email\Model\AbstractTemplate.
 */
namespace Magento\Email\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTemplateTest extends \PHPUnit\Framework\TestCase
{
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
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

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
     * @var \Magento\Email\Model\Template\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailConfig;

    /**
     * @var \Magento\Email\Model\TemplateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateFactory;

    protected function setUp()
    {
        $this->design = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appEmulation = $this->getMockBuilder(\Magento\Store\Model\App\Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getFrontendName', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontendName'));
        $this->store->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('storeId'));
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepo = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailConfig = $this->getMockBuilder(\Magento\Email\Model\Template\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterFactory = $this->getMockBuilder(\Magento\Email\Model\Template\FilterFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFactory = $this->getMockBuilder(\Magento\Email\Model\TemplateFactory::class)
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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        return $this->getMockForAbstractClass(
            \Magento\Email\Model\AbstractTemplate::class,
            $helper->getConstructArguments(
                \Magento\Email\Model\AbstractTemplate::class,
                [
                    'design' => $this->design,
                    'registry' => $this->registry,
                    'appEmulation' => $this->appEmulation,
                    'storeManager' => $this->storeManager,
                    'filesystem' => $this->filesystem,
                    'assetRepo' => $this->assetRepo,
                    'scopeConfig' => $this->scopeConfig,
                    'emailConfig' => $this->emailConfig,
                    'filterFactory' => $this->filterFactory,
                    'templateFactory' => $this->templateFactory
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
     * @param $variables array
     * @param $templateType string
     * @param $storeId int
     * @param $expectedVariables array
     * @param $expectedResult string
     * @dataProvider getProcessedTemplateProvider
     */
    public function testGetProcessedTemplate($variables, $templateType, $storeId, $expectedVariables, $expectedResult)
    {
        $filterTemplate = $this->getMockBuilder(\Magento\Email\Model\Template\Filter::class)
            ->setMethods([
                'setUseSessionInUrl',
                'setPlainTemplateMode',
                'setIsChildTemplate',
                'setDesignParams',
                'setVariables',
                'setStoreId',
                'filter',
                'getStoreId',
                'getInlineCssFiles',
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
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setDesignParams')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));

        $expectedVariables['store'] = $this->store;

        $model = $this->getModelMock([
            'getDesignParams',
            'applyDesignConfig',
            'getTemplateText',
            'isPlain',
        ]);
        $filterTemplate->expects($this->any())
            ->method('setVariables')
            ->with(array_merge(['this' => $model], $expectedVariables));
        $model->setTemplateFilter($filterTemplate);
        $model->setTemplateType($templateType);

        $designParams = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->any())
            ->method('getDesignParams')
            ->will($this->returnValue($designParams));

        $model->expects($this->atLeastOnce())
            ->method('isPlain')
            ->will($this->returnValue($templateType === \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT));

        $preparedTemplateText = $expectedResult; //'prepared text';
        $model->expects($this->once())
            ->method('getTemplateText')
            ->will($this->returnValue($preparedTemplateText));

        $filterTemplate->expects($this->once())
            ->method('filter')
            ->with($preparedTemplateText)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $model->getProcessedTemplate($variables));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetProcessedTemplateException() {
        $filterTemplate = $this->getMockBuilder(\Magento\Email\Model\Template\Filter::class)
            ->setMethods([
                'setUseSessionInUrl',
                'setPlainTemplateMode',
                'setIsChildTemplate',
                'setDesignParams',
                'setVariables',
                'setStoreId',
                'filter',
                'getStoreId',
                'getInlineCssFiles',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $filterTemplate->expects($this->once())
            ->method('setUseSessionInUrl')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setPlainTemplateMode')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setIsChildTemplate')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->once())
            ->method('setDesignParams')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $filterTemplate->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $model = $this->getModelMock([
            'getDesignParams',
            'applyDesignConfig',
            'getTemplateText',
            'isPlain',
        ]);

        $designParams = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->any())
            ->method('getDesignParams')
            ->will($this->returnValue($designParams));
        $model->setTemplateFilter($filterTemplate);
        $model->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT);

        $filterTemplate->expects($this->once())
            ->method('filter')
            ->will($this->throwException(new \Exception));
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
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT,
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
                'templateType' => \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML,
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
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $model->expects($this->once())
            ->method('getDesignParams')
            ->will($this->returnValue($designParams));
        $this->assetRepo->method('getUrlWithParams')
            ->with(\Magento\Email\Model\AbstractTemplate::DEFAULT_LOGO_FILE_ID, $designParams)
            ->will($this->returnValue($value));
        $this->assertEquals($value, $model->getDefaultEmailLogo());
    }

    /**
     * @param array $config
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider invalidInputParametersDataProvider
     */
    public function testSetDesignConfigWithInvalidInputParametersThrowsException($config)
    {
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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $designMock = $this->createMock(\Magento\Framework\View\DesignInterface::class);
        $designMock->expects($this->any())->method('getArea')->willReturn('test_area');

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->any())->method('getId')->willReturn(2);
        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $model = $this->getMockForAbstractClass(
            \Magento\Email\Model\AbstractTemplate::class,
            $helper->getConstructArguments(
                \Magento\Email\Model\AbstractTemplate::class,
                [
                    'design' => $designMock,
                    'storeManager' => $storeManagerMock
                ]
            )
        );

        $expectedConfig = ['area' => 'test_area', 'store' => 2];
        $this->assertEquals($expectedConfig, $model->getDesignConfig()->getData());
    }
}
