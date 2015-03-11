<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\App;
use Magento\Framework\View\Asset;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem */
    protected $filesystem;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Asset\ConfigInterface */
    protected $assetConf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Manager */
    protected $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Asset\Bundle */
    protected $bundle;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle\ConfigInterface */
    protected $bundleConf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle\ConfigInterface */
    protected $asset;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\File\FallbackContext */
    protected $context;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->assetConf = $this->getMock('Magento\Framework\View\Asset\ConfigInterface', [], [], '', false);
        $this->bundle = $this->getMock('Magento\Framework\View\Asset\Bundle', [], [], '', false);
        $this->bundleConf = $this->getMock('Magento\Framework\View\Asset\Bundle\ConfigInterface', [], [], '', false);
        $this->asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $this->context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
    }

    protected function getManager()
    {
        return new Manager($this->filesystem, $this->bundle, $this->bundleConf, $this->assetConf);
    }

    public function testAddAssetWithInvalidAssetType()
    {
        $this->asset
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('png');
        $this->assertFalse($this->getManager()->addAsset($this->asset));
    }

    public function testAddAssetWithInvalidModule()
    {
        $this->asset
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('html');
        $this->asset
            ->expects($this->once())
            ->method('getModule')
            ->willReturn('');
        $this->assertFalse($this->getManager()->addAsset($this->asset));
    }

    public function testAddAssetAndMinificationEnableAndAssetIsMinifyed()
    {
        $viewConfig = $this->getMock('Magento\Framework\Config\View', [], [], '', false);

        $this->asset
            ->expects($this->exactly(2))
            ->method('getContentType')
            ->willReturn('js');
        $this->asset
            ->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/path/to/assets/asset.min.js');
        $this->asset
            ->expects($this->exactly(3))
            ->method('getFilePath')
            ->willReturn('/path/to/assets/asset.min.js');
        $this->asset
            ->expects($this->exactly(2))
            ->method('getContext')
            ->willReturn($this->context);

        $this->assetConf
            ->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(true);

        $viewConfig
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $viewConfig
            ->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);

        $this->bundleConf
            ->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($viewConfig);

        $this->assertTrue($this->getManager()->addAsset($this->asset));
    }

    public function testAddAssetAndMinificationEnableAndAssetIsNotMinifyed()
    {
        $viewConfig = $this->getMock('Magento\Framework\Config\View', [], [], '', false);

        $this->asset
            ->expects($this->exactly(3))
            ->method('getContentType')
            ->willReturn('js');
        $this->asset
            ->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(3))
            ->method('getFilePath')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(2))
            ->method('getContext')
            ->willReturn($this->context);

        $this->assetConf
            ->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(true);

        $viewConfig
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $viewConfig
            ->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);

        $this->bundleConf
            ->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($viewConfig);

        $this->assertTrue($this->getManager()->addAsset($this->asset));
    }

    public function testAddAssetAndMinificationDisableAndAssetIsNotMinifyed()
    {
        $viewConfig = $this->getMock('Magento\Framework\Config\View', [], [], '', false);

        $this->asset
            ->expects($this->exactly(3))
            ->method('getContentType')
            ->willReturn('js');
        $this->asset
            ->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(4))
            ->method('getFilePath')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(2))
            ->method('getContext')
            ->willReturn($this->context);

        $this->assetConf
            ->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(false);

        $viewConfig
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $viewConfig
            ->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);

        $this->bundleConf
            ->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($viewConfig);

        $this->assertTrue($this->getManager()->addAsset($this->asset));
    }

    public function testAddAssetAndFileIsExcluded()
    {
        $viewConfig = $this->getMock('Magento\Framework\Config\View', [], [], '', false);

        $this->asset
            ->expects($this->exactly(3))
            ->method('getContentType')
            ->willReturn('js');
        $this->asset
            ->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(3))
            ->method('getFilePath')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(1))
            ->method('getContext')
            ->willReturn($this->context);

        $this->assetConf
            ->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(false);

        $viewConfig
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn(['/path/to/assets/asset.js']);
        $viewConfig
            ->expects($this->never())
            ->method('getExcludedDir')
            ->willReturn([]);

        $this->bundleConf
            ->expects($this->exactly(1))
            ->method('getConfig')
            ->willReturn($viewConfig);

        $this->assertFalse($this->getManager()->addAsset($this->asset));
    }

    public function testAddAssetAndFileInExcludedDirectory()
    {
        $viewConfig = $this->getMock('Magento\Framework\Config\View', [], [], '', false);

        $this->asset
            ->expects($this->exactly(3))
            ->method('getContentType')
            ->willReturn('js');
        $this->asset
            ->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(4))
            ->method('getFilePath')
            ->willReturn('/path/to/assets/asset.js');
        $this->asset
            ->expects($this->exactly(2))
            ->method('getContext')
            ->willReturn($this->context);

        $this->assetConf
            ->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(false);

        $viewConfig
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $viewConfig
            ->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn(['/path/to/assets']);

        $this->bundleConf
            ->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($viewConfig);

        $this->assertFalse($this->getManager()->addAsset($this->asset));
    }
}
