<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Wysiwyg;

use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Model\Wysiwyg\CompositeConfigProvider;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Cms\Model\Wysiwyg\ConfigProviderFactory;
use Magento\Cms\Model\WysiwygDefaultConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Block\Wysiwyg\ActiveEditor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cms\Model\Wysiwyg\Config
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $wysiwygConfig;

    /**
     * @var UrlInterface|MockObject
     */
    protected $backendUrlMock;

    /**
     * @var Repository|MockObject
     */
    protected $assetRepoMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    protected $authorizationMock;

    /**
     * @var \Magento\Variable\Model\Variable\Config|MockObject
     */
    protected $variableConfigMock;

    /**
     * @var \Magento\Widget\Model\Widget\Config|MockObject
     */
    protected $widgetConfigMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var File|MockObject
     */
    protected $assetFileMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    protected $windowSize = [];

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->backendUrlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assetRepoMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->variableConfigMock = $this->getMockBuilder(\Magento\Variable\Model\Variable\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->widgetConfigMock = $this->getMockBuilder(\Magento\Widget\Model\Widget\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetFileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->windowSize = [
            'width' => 1200,
            'height' => 800,
        ];
        $defaultConfigProvider = new WysiwygDefaultConfig();
        $objectManager = new ObjectManager($this);
        $configProviderFactory = $this->getMockBuilder(ConfigProviderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configProviderFactory->expects($this->any())->method('create')->willReturn($defaultConfigProvider);
        $this->configProvider = $this->getMockBuilder(CompositeConfigProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(
                [
                    'activeEditor' => $this->getMockBuilder(ActiveEditor::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    'configProviderFactory' => $configProviderFactory,
                    'variablePluginConfigProvider' => ['default' => WysiwygDefaultConfig::class],
                    'widgetPluginConfigProvider' => ['default' => WysiwygDefaultConfig::class],
                    'wysiwygConfigPostProcessor' => ['default' => WysiwygDefaultConfig::class],
                    'galleryConfigProvider' => ['default' => WysiwygDefaultConfig::class],
                ]
            )
            ->setMethods(['processVariableConfig', 'processWidgetConfig'])
            ->getMock();

        $this->wysiwygConfig = $objectManager->getObject(
            Config::class,
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
            ->willReturn('static');
        /** @var ContextInterface|MockObject $contextMock */
        $contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $contextMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('localhost/static/');
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
        $this->assertInstanceOf(DataObject::class, $config);
        $this->assertEquals($expectedResults[0], $config->getData('someData'));
        $this->assertEquals('localhost/static/', $config->getData('baseStaticUrl'));
        $this->assertEquals('localhost/static/', $config->getData('baseStaticDefaultUrl'));
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
            ->with(Config::WYSIWYG_SKIN_IMAGE_PLACEHOLDER_ID)
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
