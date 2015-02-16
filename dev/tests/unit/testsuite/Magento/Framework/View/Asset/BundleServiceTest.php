<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

class BundleServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\ConfigInterface */
    protected $conf;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem */
    protected $filesystem;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State */
    protected $appState;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\BundleFactory */
    protected $bundleFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Design\Theme\ListInterface */
    protected $list;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\ConfigInterface */
    protected $viewConf;

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
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->bundleFactory = $this->getMock('Magento\Framework\View\Asset\BundleFactory', [], [], '', false);
        $this->list = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\Theme\ListInterface',
            [],
            '',
            false
        );
        $this->viewConf = $this->getMock('Magento\Framework\Config\View', [], [], '', false);
    }

    protected function tearDown()
    {
        unset($this->conf, $this->asset, $this->filesystem, $this->appState, $this->bundleFactory, $this->viewConf);
    }

    protected function getBundleService()
    {
        return new BundleService(
            $this->filesystem,
            $this->bundleFactory,
            $this->conf,
            $this->appState,
            $this->list
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

    public function testCollectWithDefaultAppStateMode()
    {
        $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $context
            ->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn('frontend');

        $assetMock
            ->expects($this->any())
            ->method('getModule')
            ->willReturn(substr(md5(5), 0, 2));
        $assetMock
            ->expects($this->once())
            ->method('getContentType')
            ->willReturn('js');
        $assetMock
            ->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($context);

        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->willReturn('default');

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

        $this->assertFalse($this->getBundleService()->collect($assetMock));
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
            ->with('/bundle')
            ->willReturn(true);
        $bundle
            ->expects($this->once())
            ->method('setType')
            ->with('js')
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

        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->willReturn('production');

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
            ->with('/bundle')
            ->willReturn(true);
        $bundle
            ->expects($this->once())
            ->method('setType')
            ->with('js')
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

        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->willReturn('production');

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
