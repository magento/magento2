<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\ConfigInterface */
    protected $viewConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Design\Theme\ListInterface */
    protected $themeList;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\File\FallbackContext */
    protected $context;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\ConfigInterface */
    protected $viewConfData;

    protected function setUp()
    {
        $this->viewConfig = $this->getMock('Magento\Framework\View\ConfigInterface', [], [], 'viewConfig', false);
        $this->themeList = $this->getMock('Magento\Framework\View\Design\Theme\ListInterface', [], [], '', false);
        $this->context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $this->viewConfData = $this->getMock(
            'Magento\Framework\Config\View',
            ['getVarValue'],
            [],
            'viewConfData',
            false
        );
    }

    protected function getResolver()
    {
        return new Resolver(
            $this->viewConfig,
            $this->themeList
        );
    }

    protected function getAssets($dinamicKey = null)
    {
        $assets = [];
        for ($i = 0; $i < 10; $i++) {
            $assetMock = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
            $assetMock
                ->expects($this->any())
                ->method('getModule')
                ->willReturn(substr(md5($i . $dinamicKey), 0, 5));
            $assetMock
                ->expects($this->any())
                ->method('getFilePath')
                ->willReturn(substr(md5($i . $dinamicKey), 0, 5) . '.js');
            $assetMock
                ->expects($this->any())
                ->method('getContent')
                ->willReturn('Content');

            $amountInvocations = ($i == 0 && !$dinamicKey) ? 1 : 0;
            $assetMock
                ->expects($this->exactly($amountInvocations))
                ->method('getContext')
                ->willReturn($this->context);
            
            $assetKey = $assetMock->getModule() . '/' .$assetMock->getFilePath();
            $assets[$assetKey] = $assetMock;
        }
        
        return $assets;
    }

    public function testResolveWithoutBundleSize()
    {
        $asset = $this->getAssets();

        $this->viewConfData
            ->expects($this->once())
            ->method('getVarValue')
            ->willReturn(0);

        $this->context
            ->expects($this->exactly(2))
            ->method('getAreaCode')
            ->willReturn('backend');
        $this->context
            ->expects($this->exactly(1))
            ->method('getThemePath')
            ->willReturn('Magento/backend');

        $this->viewConfig
            ->expects($this->once())
            ->method('getViewConfig')
            ->willReturn($this->viewConfData);

        $result = $this->getResolver()->resolve($asset);
        $this->assertArrayHasKey(0, $result);
        $part = $result[0];
        $this->assertEquals(10, count($part));
    }

    public function testResolve()
    {
        $asset = $this->getAssets();

        $this->viewConfData
            ->expects($this->once())
            ->method('getVarValue')
            ->willReturn('15');

        $this->context
            ->expects($this->exactly(2))
            ->method('getAreaCode')
            ->willReturn('backend');
        $this->context
            ->expects($this->exactly(1))
            ->method('getThemePath')
            ->willReturn('Magento/backend');

        $this->viewConfig
            ->expects($this->once())
            ->method('getViewConfig')
            ->willReturn($this->viewConfData);

        $result = $this->getResolver()->resolve($asset);
        $this->assertEquals(5, count($result));
    }

    public function testAppendHtmlPartWithoutBundleSize()
    {
        $asset = $this->getAssets();
        $htmlAssets = $this->getAssets('html');

            $this->viewConfData
                ->expects($this->once())
                ->method('getVarValue')
                ->willReturn(null);

            $this->context
                ->expects($this->exactly(2))
                ->method('getAreaCode')
                ->willReturn('backend');
            $this->context
                ->expects($this->exactly(1))
                ->method('getThemePath')
                ->willReturn('Magento/backend');

            $this->viewConfig
                ->expects($this->once())
                ->method('getViewConfig')
                ->willReturn($this->viewConfData);

        $result = $this->getResolver()->appendHtmlPart([$asset, $htmlAssets]);
        $this->assertArrayHasKey(0, $result);
        $this->assertEquals(20, count($result[0]));
    }
}
