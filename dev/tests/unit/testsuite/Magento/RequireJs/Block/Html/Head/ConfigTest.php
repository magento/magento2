<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Block\Html\Head;

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
     * @var \Magento\Framework\View\Page\Config|\Magento\Framework\View\Asset\ConfigInterface
     */
    protected $bundleConfig;

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

        $this->fileManager->expects($this->once())->method('createRequireJsAsset')->will($this->returnValue($asset));
        $this->fileManager
            ->expects($this->once())
            ->method('createRequireJsConfigAsset')
            ->will($this->returnValue($requireJsAsset));
        $this->fileManager
            ->expects($this->once())
            ->method('createStaticJsAsset')
            ->will($this->returnValue($requireJsAsset));
        $this->fileManager
            ->expects($this->once())
            ->method('createBundleJsPool')
            ->will($this->returnValue([$asset]));

        $layout = $this->getMock('Magento\Framework\View\LayoutInterface');

        $assetCollection = $this->getMockBuilder('Magento\Framework\View\Asset\GroupedCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $assetCollection->expects($this->atLeastOnce())
            ->method('add');
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getAssetCollection')
            ->willReturn($assetCollection);

        $propertyGroup = $this->getMock('Magento\Framework\View\Asset\PropertyGroup', [], [], '', false);
        $propertyGroup
            ->expects($this->once())
            ->method('getProperty')
            ->with('content_type')
            ->willReturn('js');
        $propertyGroup
            ->expects($this->once())
            ->method('getAll')
            ->willReturn([]);
        $propertyGroup
            ->expects($this->once())
            ->method('removeAll')
            ->willReturn(true);
        $assetCollection
            ->expects($this->once())
            ->method('getGroups')
            ->willReturn([$propertyGroup]);

        $object = new Config($this->context, $this->config, $this->fileManager, $this->pageConfig, $this->bundleConfig);
        $object->setLayout($layout);
    }

    public function testToHtml()
    {
        $this->context->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($this->getMockForAbstractClass('\Magento\Framework\Event\ManagerInterface')));
        $this->context->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue(
                $this->getMockForAbstractClass('\Magento\Framework\App\Config\ScopeConfigInterface')
            ));
        $this->config->expects($this->once())->method('getBaseConfig')->will($this->returnValue('the config data'));
        $object = new Config($this->context, $this->config, $this->fileManager, $this->pageConfig, $this->bundleConfig);
        $html = $object->toHtml();
        $expectedFormat = <<<expected
<script type="text/javascript">
the config data</script>
expected;
        $this->assertStringMatchesFormat($expectedFormat, $html);
    }
}
