<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    protected function setUp()
    {
        $this->context = $this->getMock('\Magento\Framework\View\Element\Context', array(), array(), '', false);
        $this->config = $this->getMock('\Magento\Framework\RequireJs\Config', array(), array(), '', false);
        $this->fileManager = $this->getMock('\Magento\RequireJs\Model\FileManager', array(), array(), '', false);
        $this->pageConfig = $this->getMock('\Magento\Framework\View\Page\Config', array(), array(), '', false);
    }

    public function testSetLayout()
    {
        $filePath = 'require_js_fie_path';
        $asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn($filePath);
        $this->fileManager->expects($this->once())->method('createRequireJsAsset')->will($this->returnValue($asset));
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface');

        $assetCollection = $this->getMockBuilder('Magento\Framework\View\Asset\GroupedCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $assetCollection->expects($this->once())
            ->method('add')
            ->with($filePath, $asset);
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getAssetCollection')
            ->willReturn($assetCollection);

        $object = new Config($this->context, $this->config, $this->fileManager, $this->pageConfig);
        $object->setLayout($layout);

    }

    public function testToHtml()
    {
        $this->context->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($this->getMockForAbstractClass('\Magento\Framework\Event\ManagerInterface')))
        ;
        $this->context->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue(
                $this->getMockForAbstractClass('\Magento\Framework\App\Config\ScopeConfigInterface')
            ))
        ;
        $this->config->expects($this->once())->method('getBaseConfig')->will($this->returnValue('the config data'));
        $object = new Config($this->context, $this->config, $this->fileManager, $this->pageConfig);
        $html = $object->toHtml();
        $expectedFormat = <<<expected
<script type="text/javascript">
the config data</script>
expected;
        $this->assertStringMatchesFormat($expectedFormat, $html);
    }
}
