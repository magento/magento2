<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Test\Unit\Block\Html\Head;

use \Magento\RequireJs\Block\Html\Head\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\RequireJs\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var \Magento\RequireJs\Model\FileManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileManager;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfig;

    /**
     * @var Config
     */
    protected $blockConfig;

    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $bundleConfig;

    /**
     * @var \Magento\Framework\View\Asset\Minification|\PHPUnit\Framework\MockObject\MockObject
     */
    private $minificationMock;

    protected function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Context::class);
        $this->config = $this->createMock(\Magento\Framework\RequireJs\Config::class);
        $this->fileManager = $this->createMock(\Magento\RequireJs\Model\FileManager::class);
        $this->pageConfig = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->bundleConfig = $this->createMock(\Magento\Framework\View\Asset\ConfigInterface::class);
    }

    public function testSetLayout()
    {
        $this->bundleConfig
            ->expects($this->once())
            ->method('isBundlingJsFiles')
            ->willReturn(true);
        $filePath = 'require_js_fie_path';
        $asset = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\LocalInterface::class);
        $asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn($filePath);
        $requireJsAsset = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\LocalInterface::class);
        $requireJsAsset
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('/path/to/require/require.js');
        $minResolverAsset = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\LocalInterface::class);
        $minResolverAsset
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('/path/to/require/require-min-resolver.js');

        $this->fileManager
            ->expects($this->once())
            ->method('createRequireJsConfigAsset')
            ->willReturn($requireJsAsset);
        $this->fileManager
            ->expects($this->once())
            ->method('createRequireJsMixinsAsset')
            ->willReturn($requireJsAsset);
        $this->fileManager
            ->expects($this->once())
            ->method('createStaticJsAsset')
            ->willReturn($requireJsAsset);
        $this->fileManager
            ->expects($this->once())
            ->method('createBundleJsPool')
            ->willReturn([$asset]);
        $this->fileManager
            ->expects($this->once())
            ->method('createMinResolverAsset')
            ->willReturn($minResolverAsset);

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);

        $assetCollection = $this->getMockBuilder(\Magento\Framework\View\Asset\GroupedCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getAssetCollection')
            ->willReturn($assetCollection);

        $assetCollection
            ->expects($this->atLeastOnce())
            ->method('insert')
            ->willReturn(true);

        $this->minificationMock = $this->getMockBuilder(\Magento\Framework\View\Asset\Minification::class)
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
