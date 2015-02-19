<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\App;
use Magento\Framework\View\Asset;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\ConfigInterface */
    protected $conf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem */
    protected $filesystem;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\BundleFactory */
    protected $bundleFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Design\Theme\ListInterface */
    protected $list;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\ConfigInterface */
    protected $viewConf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Asset\ConfigInterface */
    protected $bundleConf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|App\State */
    protected $appState;

    protected function setUp()
    {
        $this->conf = $this->getMockForAbstractClass(
            'Magento\Framework\View\ConfigInterface',
            [],
            '',
            false
        );
        $this->asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->bundleFactory = $this->getMock('Magento\Framework\View\Asset\BundleFactory', ['create'], [], '', false);
        $this->list = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\Theme\ListInterface',
            [],
            '',
            false
        );
        $this->viewConf = $this->getMock('Magento\Framework\Config\View', [], [], '', false);
        $this->bundleConf = $this->getMockForAbstractClass(
            'Magento\Framework\View\Asset\ConfigInterface',
            ['isMergeJsFiles'],
            '',
            false
        );
        $this->bundleConf
            ->expects($this->once())
            ->method('isMergeJsFiles')
            ->willReturn(true);
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->willReturn('production');
    }

    protected function tearDown()
    {
        unset($this->conf, $this->asset, $this->filesystem, $this->bundleFactory, $this->viewConf);
    }

    protected function getBundleService()
    {
        return new Service(
            $this->filesystem,
            $this->bundleFactory,
            $this->conf,
            $this->list,
            $this->bundleConf,
            $this->appState
        );
    }

    public function testCollectWithInvalidAsset()
    {
        $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $assetMock
            ->expects($this->any())
            ->method('getModule')
            ->willReturn(substr(md5(5), 0, 2));
        $assetMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('undefined');

        $this->assertFalse($this->getBundleService()->collect($assetMock));
    }
    public function testCollectWithMinifiedAsset()
    {
        $bundle = $this->getMock('Magento\Framework\View\Asset\Bundle', [], [], '', false);
        $context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $context
            ->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn('frontend');
        $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $assetMock
            ->expects($this->any())
            ->method('getModule')
            ->willReturn(substr(md5(5), 0, 2));
        $assetMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('js');
        $assetMock
            ->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('path/to/asset.min.js');
        $assetMock
            ->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);
        $assetMock
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('path/to/asset.min.js');

        $this->viewConf
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $this->viewConf
            ->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);

        $this->conf
            ->expects($this->atLeastOnce())
            ->method('getViewConfig')
            ->willReturn($this->viewConf);

        $bundle
            ->expects($this->once())
            ->method('setPath')
            ->with('/js/bundle/bundle')
            ->willReturn(true);
        $this->bundleFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($bundle);

        $this->assertTrue($this->getBundleService()->collect($assetMock));
    }

    public function testCollect()
    {
        $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $bundle = $this->getMock('Magento\Framework\View\Asset\Bundle', [], [], '', false);
        $context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $context
            ->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn('frontend');

        $bundle
            ->expects($this->once())
            ->method('setPath')
            ->with('/js/bundle/bundle')
            ->willReturn(true);

        $assetMock
            ->expects($this->any())
            ->method('getModule')
            ->willReturn(substr(md5(5), 0, 2));
        $assetMock
            ->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $assetMock
            ->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);

        $this->viewConf
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $this->viewConf
            ->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);

        $this->conf
            ->expects($this->atLeastOnce())
            ->method('getViewConfig')
            ->willReturn($this->viewConf);

        $this->bundleFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($bundle);

        $this->assertTrue($this->getBundleService()->collect($assetMock));
    }

    public function testSave()
    {
        $bundleService = $this->getBundleService();
        $dirWrite = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($dirWrite);
        $dirWrite
            ->expects($this->once())
            ->method('writeFile')
            ->with('path/to/bundle0.js', 'Bundle content')
            ->willReturn(true);

        $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $bundle = $this->getMock('Magento\Framework\View\Asset\Bundle', [], [], '', false);
        $context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $context
            ->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn('frontend');

        $bundle
            ->expects($this->once())
            ->method('setPath')
            ->with('/js/bundle/bundle')
            ->willReturn(true);
        $bundle
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/bundle');
        $bundle
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(['Bundle content']);

        $assetMock
            ->expects($this->any())
            ->method('getModule')
            ->willReturn(substr(md5(5), 0, 2));
        $assetMock
            ->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('js');
        $assetMock
            ->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);

        $this->viewConf
            ->expects($this->once())
            ->method('getExcludedFiles')
            ->willReturn([]);
        $this->viewConf
            ->expects($this->once())
            ->method('getExcludedDir')
            ->willReturn([]);

        $this->conf
            ->expects($this->atLeastOnce())
            ->method('getViewConfig')
            ->willReturn($this->viewConf);

        $this->bundleFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($bundle);

        $bundleService->collect($assetMock);
        $this->assertTrue($bundleService->save());
    }
}
