<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Wysiwyg;

/**
 * @covers \Magento\Cms\Model\Wysiwyg\Config
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $backendUrlMock;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $assetRepoMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authorizationMock;

    /**
     * @var \Magento\Variable\Model\Variable\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $variableConfigMock;

    /**
     * @var \Magento\Widget\Model\Widget\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $widgetConfigMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $assetFileMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    protected $windowSize = [];

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->backendUrlMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepoMock = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->variableConfigMock = $this->getMockBuilder(\Magento\Variable\Model\Variable\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->widgetConfigMock = $this->getMockBuilder(\Magento\Widget\Model\Widget\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetFileMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->windowSize = [
            'width' => 1200,
            'height' => 800,
        ];
        $defaultConfigProvider = new \Magento\Cms\Model\WysiwygDefaultConfig();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $configProviderFactory = $this->getMockBuilder(\Magento\Cms\Model\Wysiwyg\ConfigProviderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configProviderFactory->expects($this->any())->method('create')->willReturn($defaultConfigProvider);
        $this->configProvider = $this->getMockBuilder(\Magento\Cms\Model\Wysiwyg\CompositeConfigProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(
                [
                    'activeEditor' => $this->getMockBuilder(\Magento\Ui\Block\Wysiwyg\ActiveEditor::class)
                        ->disableOriginalConstructor()->getMock(),
                    'configProviderFactory' => $configProviderFactory,
                    'variablePluginConfigProvider' => ['default' => \Magento\Cms\Model\WysiwygDefaultConfig::class],
                    'widgetPluginConfigProvider' => ['default' => \Magento\Cms\Model\WysiwygDefaultConfig::class],
                    'wysiwygConfigPostProcessor' => ['default' => \Magento\Cms\Model\WysiwygDefaultConfig::class],
                    'galleryConfigProvider' => ['default' => \Magento\Cms\Model\WysiwygDefaultConfig::class],
                ]
            )
            ->setMethods(['processVariableConfig', 'processWidgetConfig'])
            ->getMock();

        $this->wysiwygConfig = $objectManager->getObject(
            \Magento\Cms\Model\Wysiwyg\Config::class,
            [
                'backendUrl' => $this->backendUrlMock,
                'assetRepo' => $this->assetRepoMock,
                'authorization' => $this->authorizationMock,
                'variableConfig' => $this->variableConfigMock,
                'widgetConfig' => $this->widgetConfigMock,
                'scopeConfig' => $this->scopeConfigMock,
                'windowSize' => $this->windowSize,
                'storeManager' => $this->storeManagerMock,
                'filesystem' => $this->filesystemMock,
                'configProvider' => $this->configProvider
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::getConfig
     * @param array $data
     * @param boolean $isAuthorizationAllowed
     * @param array $expectedResults
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($data, $isAuthorizationAllowed, $expectedResults)
    {
        $this->backendUrlMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->withConsecutive(
                ['cms/wysiwyg/directive'],
                ['cms/wysiwyg_images/index']
            );
        $this->backendUrlMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('localhost/index.php/');
        $this->filesystemMock->expects($this->once())
            ->method('getUri')
            ->willReturn('pub/static');
        /** @var \Magento\Framework\View\Asset\ContextInterface|\PHPUnit\Framework\MockObject\MockObject $contextMock */
        $contextMock = $this->createMock(\Magento\Framework\View\Asset\ContextInterface::class);
        $contextMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('localhost/pub/static/');
        $this->assetRepoMock->expects($this->once())
            ->method('getStaticViewFileContext')
            ->willReturn($contextMock);
        $this->authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Cms::media_gallery')
            ->willReturn($isAuthorizationAllowed);
        if ($data['add_variables']) {
            $this->configProvider->expects($this->once())
                ->method('processVariableConfig');
        }
        if ($data['add_widgets']) {
            $this->configProvider->expects($this->once())
                ->method('processWidgetConfig');
        }

        $config = $this->wysiwygConfig->getConfig($data);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $config);
        $this->assertEquals($expectedResults[0], $config->getData('someData'));
        $this->assertEquals('localhost/pub/static/', $config->getData('baseStaticUrl'));
        $this->assertEquals('localhost/pub/static/', $config->getData('baseStaticDefaultUrl'));
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            'add_variables IS FALSE, add_widgets IS FALSE, isAuthorizationAllowed IS FALSE' => [
                'data' => [
                    'add_variables' => false,
                    'add_widgets' => false,
                ],
                'isAuthorizationAllowed' => false,
                'expectedResults' => [null, null, null],
            ],
            'add_variables IS TRUE, add_widgets IS TRUE, isAuthorizationAllowed IS TRUE' => [
                'data' => [
                    'someData' => 'important data',
                    'add_variables' => true,
                    'add_widgets' => true,
                ],
                'isAuthorizationAllowed' => true,
                'expectedResults' => ['important data', 'wysiwyg is here', 'plugins are here'],
            ]
        ];
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::getSkinImagePlaceholderPath
     */
    public function testGetSkinImagePlaceholderPath()
    {
        $staticPath = 'pub/static';
        $placeholderPath = 'adminhtml/Magento/backend/en_US/Magento_Cms/images/wysiwyg_skin_image.png';
        $expectedResult = 'pub/static/adminhtml/Magento/backend/en_US/Magento_Cms/images/wysiwyg_skin_image.png';

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getBaseStaticDir')
            ->willReturn($staticPath);
        $this->assetRepoMock->expects($this->any())
            ->method('createAsset')
            ->with(\Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_SKIN_IMAGE_PLACEHOLDER_ID)
            ->willReturn($this->assetFileMock);
        $this->assetFileMock->expects($this->once())
            ->method('getPath')
            ->willReturn($placeholderPath);

        $this->assertEquals($expectedResult, $this->wysiwygConfig->getSkinImagePlaceholderPath());
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::isEnabled
     * @param string $wysiwygState
     * @param boolean $expectedResult
     *
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($wysiwygState, $expectedResult)
    {
        $storeId = 1;
        $this->wysiwygConfig->setStoreId($storeId);

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->with('cms/wysiwyg/enabled', 'store', $storeId)
            ->willReturn($wysiwygState);

        $this->assertEquals($expectedResult, $this->wysiwygConfig->isEnabled());
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [
            ['wysiwygState' => 'enabled', 'expectedResult' => true],
            ['wysiwygState' => 'hidden', 'expectedResult' => true],
            ['wysiwygState' => 'masked', 'expectedResult' => false]
        ];
    }

    /**
     * @covers \Magento\Cms\Model\Wysiwyg\Config::isHidden
     * @param string $status
     * @param boolean $expectedResult
     *
     * @dataProvider isHiddenDataProvider
     */
    public function testIsHidden($status, $expectedResult)
    {
        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->with('cms/wysiwyg/enabled', 'store')
            ->willReturn($status);

        $this->assertEquals($expectedResult, $this->wysiwygConfig->isHidden());
    }

    /**
     * @return array
     */
    public function isHiddenDataProvider()
    {
        return [
            ['status' => 'hidden', 'expectedResult' => true],
            ['status' => 'enabled', 'expectedResult' => false],
            ['status' => 'masked', 'expectedResult' => false]
        ];
    }
}
