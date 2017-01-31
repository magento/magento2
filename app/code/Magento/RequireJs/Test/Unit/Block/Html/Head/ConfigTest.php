<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Test\Unit\Block\Html\Head;

use \Magento\RequireJs\Block\Html\Head\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\RequireJs\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\RequireJs\Model\FileManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileManager;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfig;

    /**
     * @var Config
     */
    protected $blockConfig;

    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleConfig;

    /**
     * @var \Magento\Framework\View\Asset\Minification|\PHPUnit_Framework_MockObject_MockObject
     */
    private $minificationMock;

    protected function setUp()
    {
        $this->context = $this->getMock('\Magento\Framework\View\Element\Context', [], [], '', false);
        $this->config = $this->getMock('\Magento\Framework\RequireJs\Config', [], [], '', false);
        $this->fileManager = $this->getMock('\Magento\RequireJs\Model\FileManager', [], [], '', false);
        $this->pageConfig = $this->getMock('\Magento\Framework\View\Page\Config', [], [], '', false);
        $this->bundleConfig = $this->getMock('Magento\Framework\View\Asset\ConfigInterface', [], [], '', false);
    }

    public function testSetLayout()
    {
        $this->bundleConfig
            ->expects($this->once())
            ->method('isBundlingJsFiles')
            ->willReturn(true);
        $filePath = 'require_js_fie_path';
        $asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn($filePath);
        $requireJsAsset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $requireJsAsset
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn('/path/to/require/require.js');
        $minResolverAsset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
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

        $layout = $this->getMock('Magento\Framework\View\LayoutInterface');

        $assetCollection = $this->getMockBuilder('Magento\Framework\View\Asset\GroupedCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getAssetCollection')
            ->willReturn($assetCollection);

        $assetCollection
            ->expects($this->atLeastOnce())
            ->method('insert')
            ->willReturn(true);

        $this->minificationMock = $this->getMockBuilder('Magento\Framework\View\Asset\Minification')
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
