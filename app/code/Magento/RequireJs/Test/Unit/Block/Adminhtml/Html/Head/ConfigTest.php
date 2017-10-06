<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Test\Unit\Block\Adminhtml\Html\Head;

use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\ConfigInterface as ViewAssetConfigInterface;
use Magento\Framework\View\Asset\GroupedCollection as ViewAssetGroupedCollection;
use Magento\Framework\View\Asset\LocalInterface as ViewAssetLocalInterface;
use Magento\Framework\View\Asset\Minification as ViewAssetMinification;
use Magento\Framework\View\Element\Context as ViewElementContext;
use Magento\Framework\View\LayoutInterface as ViewLayoutInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\RequireJs\Block\Adminhtml\Html\Head\Config;
use Magento\RequireJs\Model\FileManager as RequireJsFileManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ConfigTest
 */
class ConfigTest extends TestCase
{
    /**
     * @var ViewElementContext|PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var RequireJsConfig|PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var RequireJsFileManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileManager;

    /**
     * @var PageConfig|PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfig;

    /**
     * @var Config
     */
    protected $blockConfig;

    /**
     * @var ViewAssetConfigInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleConfig;

    /**
     * @var ViewAssetMinification|PHPUnit_Framework_MockObject_MockObject
     */
    protected $minificationMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->context = $this->createMock(ViewElementContext::class);
        $this->config = $this->createMock(RequireJsConfig::class);
        $this->fileManager = $this->createMock(RequireJsFileManager::class);
        $this->pageConfig = $this->createMock(PageConfig::class);
        $this->bundleConfig = $this->createMock(ViewAssetConfigInterface::class);
    }

    /**
     * @return void
     */
    public function testSetLayout()
    {
        $this->bundleConfig
            ->expects($this->once())
            ->method('isBundlingJsFiles')
            ->willReturn(true);
        $filePath = 'require_js_fie_path';
        $asset = $this->getMockForAbstractClass(ViewAssetLocalInterface::class);
        $asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn($filePath);
        $requireJsAsset = $this->getMockForAbstractClass(ViewAssetLocalInterface::class);
        $requireJsAsset
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('/path/to/require/require.js');
        $minResolverAsset = $this->getMockForAbstractClass(ViewAssetLocalInterface::class);
        $minResolverAsset
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('/path/to/require/require-min-resolver.js');

        $this->fileManager
            ->expects($this->once())
            ->method('createRequireJsConfigAsset')
            ->will($this->returnValue($requireJsAsset));
        $this->fileManager
            ->expects($this->once())
            ->method('createRequireJsMixinsAsset')
            ->will($this->returnValue($requireJsAsset));
        $this->fileManager
            ->expects($this->once())
            ->method('createStaticJsAsset')
            ->will($this->returnValue($requireJsAsset));
        $this->fileManager
            ->expects($this->once())
            ->method('createBundleJsPool')
            ->will($this->returnValue([$asset]));
        $this->fileManager
            ->expects($this->once())
            ->method('createMinResolverAsset')
            ->will($this->returnValue($minResolverAsset));

        $layout = $this->createMock(ViewLayoutInterface::class);

        $assetCollection = $this->getMockBuilder(ViewAssetGroupedCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getAssetCollection')
            ->willReturn($assetCollection);

        $assetCollection
            ->expects($this->atLeastOnce())
            ->method('insert')
            ->willReturn(true);

        $this->minificationMock = $this->getMockBuilder(ViewAssetMinification::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->minificationMock
            ->expects($this->any())
            ->method('isEnabled')
            ->with('js')
            ->willReturn(true);

        $object = new Config(
            $this->context,
            $this->config,
            $this->fileManager,
            $this->pageConfig,
            $this->bundleConfig,
            $this->minificationMock
        );

        $object->setLayout($layout);
    }
}
