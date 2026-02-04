<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\RequireJs\Test\Unit\Block\Html\Head;

use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\RequireJs\Block\Html\Head\Config;
use Magento\RequireJs\Model\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\RequireJs\Config|MockObject
     */
    private $config;

    /**
     * @var FileManager|MockObject
     */
    private $fileManager;

    /**
     * @var \Magento\Framework\View\Page\Config|MockObject
     */
    protected $pageConfig;

    /**
     * @var Config
     */
    protected $blockConfig;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $bundleConfig;

    /**
     * @var Minification|MockObject
     */
    private $minificationMock;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->config = $this->createMock(\Magento\Framework\RequireJs\Config::class);
        $this->fileManager = $this->createMock(FileManager::class);
        $this->pageConfig = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->bundleConfig = $this->createMock(ConfigInterface::class);
    }

    public function testSetLayout()
    {
        $this->bundleConfig
            ->expects($this->once())
            ->method('isBundlingJsFiles')
            ->willReturn(true);
        $filePath = 'require_js_fie_path';
        $asset = $this->createMock(LocalInterface::class);
        $asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn($filePath);
        $requireJsAsset = $this->createMock(LocalInterface::class);
        $requireJsAsset
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('/path/to/require/require.js');
        $minResolverAsset = $this->createMock(LocalInterface::class);
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

        $layout = $this->createMock(LayoutInterface::class);

        $assetCollection = $this->createMock(GroupedCollection::class);
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getAssetCollection')
            ->willReturn($assetCollection);

        $assetCollection
            ->expects($this->atLeastOnce())
            ->method('insert')
            ->willReturn(true);

        $this->minificationMock = $this->createMock(Minification::class);
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
