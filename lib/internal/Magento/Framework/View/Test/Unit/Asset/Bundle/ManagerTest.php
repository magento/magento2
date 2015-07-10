<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\Bundle;

use Magento\Framework\View\Asset\Bundle\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\View\Asset\Bundle\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    /** @var  \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystem;

    /** @var  \Magento\Framework\View\Asset\Bundle|\PHPUnit_Framework_MockObject_MockObject */
    protected $bundle;

    /** @var  \Magento\Framework\View\Asset\Bundle\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bundleConfig;

    /** @var  \Magento\Framework\View\Asset\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $assetConfig;

    /** @var  \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $asset;

    public function setUp()
    {
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundle = $this->getMockBuilder('Magento\Framework\View\Asset\Bundle')
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleConfig = $this->getMockBuilder('Magento\Framework\View\Asset\Bundle\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetConfig = $this->getMockBuilder('Magento\Framework\View\Asset\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->asset = $this->getMockForAbstractClass(
            'Magento\Framework\View\Asset\LocalInterface',
            [],
            '',
            false,
            false,
            true,
            ['getContentType']
        );

        $this->manager = new Manager($this->filesystem, $this->bundle, $this->bundleConfig, $this->assetConfig);
    }

    public function testAddAssetWithInvalidType()
    {
        $this->asset->expects($this->once())
            ->method('getContentType')
            ->willReturn('testType');

        $this->assertFalse($this->manager->addAsset($this->asset));
    }

    public function testAddAssetWithHtmlTypeAndWithoutModule()
    {
        $this->asset->expects($this->once())
            ->method('getContentType')
            ->willReturn('html');
        $this->asset->expects($this->once())
            ->method('getModule')
            ->willReturn('');

        $this->assertFalse($this->manager->addAsset($this->asset));
    }

    public function testAddAssetWithExcludedFile()
    {
        $context = $this->getMockBuilder('Magento\Framework\View\Asset\File\FallbackContext')
                ->disableOriginalConstructor()
                ->getMock();
        $configView = $this->getMockBuilder('Magento\Framework\Config\View')
                ->disableOriginalConstructor()
                ->getMock();

        $this->asset->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $this->asset->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/source/file');
        $this->asset->expects($this->once())
            ->method('getModule')
            ->willReturn('');
        $this->asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('file/path.js');
        $this->assetConfig->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(false);
        $this->asset->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $this->bundleConfig
            ->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with($context)
            ->willReturn($configView);
        $configView->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn(['Lib:' . ':file/path.js']);

        $this->assertFalse($this->manager->addAsset($this->asset));
    }

    public function testAddAssetWithExcludedDirectory()
    {
        $context = $this->getMockBuilder('Magento\Framework\View\Asset\File\FallbackContext')
                ->disableOriginalConstructor()
                ->getMock();
        $configView = $this->getMockBuilder('Magento\Framework\Config\View')
                ->disableOriginalConstructor()
                ->getMock();

        $this->asset->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $this->asset->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/source/file');
        $this->asset->expects($this->atLeastOnce())
            ->method('getModule')
            ->willReturn('');
        $this->asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('file/path.js');
        $this->assetConfig->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(false);
        $this->asset->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $this->bundleConfig
            ->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with($context)
            ->willReturn($configView);
        $configView->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $configView->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn(['Lib:' . ':file']);

        $this->assertFalse($this->manager->addAsset($this->asset));
    }

    public function testAddAsset()
    {
        $context = $this->getMockBuilder('Magento\Framework\View\Asset\File\FallbackContext')
            ->disableOriginalConstructor()
            ->getMock();
        $configView = $this->getMockBuilder('Magento\Framework\Config\View')
            ->disableOriginalConstructor()
            ->getMock();

        $this->asset->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $this->asset->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('/source/file');
        $this->asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('file/path.js');
        $this->assetConfig->expects($this->once())
            ->method('isAssetMinification')
            ->willReturn(false);
        $this->asset->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $this->bundleConfig
            ->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with($context)
            ->willReturn($configView);
        $configView->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $configView->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);
        $this->bundle->expects($this->once())
            ->method('addAsset')
            ->with($this->asset);

        $this->assertTrue($this->manager->addAsset($this->asset));
    }

    public function testFlush()
    {
        $this->bundle->expects($this->once())
            ->method('flush');
        $this->manager->flush();
    }
}
