<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model\Template;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Template\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    protected function setUp()
    {
        $scopeConfig = $this->getMockForAbstractClass(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->storeManager = $this->getMockForAbstractClass(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false
        );
        $logger = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface', [], '', false);
        $layout = $this->getMockForAbstractClass('\Magento\Framework\View\LayoutInterface', [], '', false);
        $urlModel = $this->getMockForAbstractClass('\Magento\Framework\UrlInterface', [], '', false);
        $string = $this->getMock('\Magento\Framework\Stdlib\StringUtils', [], [], '', false);
        $escaper = $this->getMock('\Magento\Framework\Escaper', [], [], '', false);
        $assetRepo = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $coreVariableFactory = $this->getMock('\Magento\Variable\Model\VariableFactory', ['create'], [], '', false);
        $layoutFactory = $this->getMock('\Magento\Framework\View\LayoutFactory', ['create'], [], '', false);
        $this->appState = $this->getMock('\Magento\Framework\App\State', [], [], '', false);
        $emogrifier = $this->getMock('\Pelago\Emogrifier', [], [], '', false);
        $configVariables = $this->getMock('\Magento\Email\Model\Source\Variables', [], [], '', false);
        $widgetResource = $this->getMock('\Magento\Widget\Model\ResourceModel\Widget', [], [], '', false);
        $widget = $this->getMock('\Magento\Widget\Model\Widget', [], [], '', false);

        $this->filter = new \Magento\Newsletter\Model\Template\Filter(
            $string,
            $logger,
            $escaper,
            $assetRepo,
            $scopeConfig,
            $coreVariableFactory,
            $this->storeManager,
            $layout,
            $layoutFactory,
            $this->appState,
            $urlModel,
            $emogrifier,
            $configVariables,
            $widgetResource,
            $widget
        );

    }

    public function testWidgetDirective()
    {
        $subscriber = $this->getMock('\Magento\Newsletter\Model\Subscriber', [], [], '', false);
        $this->filter->setVariables(['subscriber' => $subscriber]);

        $construction = '{{widget type="\Magento\Cms\Block\Widget\Page\Link" page_id="1"}}';

        $store = $this->getMockForAbstractClass('Magento\Store\Api\Data\StoreInterface', [], '', false);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $this->appState->expects($this->once())
            ->method('emulateAreaCode')
            ->with(
                'frontend',
                [$this->filter, 'generateWidget'],
                [
                    [
                        1 => $construction,
                        2 => 'type="\Magento\Cms\Block\Widget\Page\Link" page_id="1" store_id ="1"'
                    ]
                ]
            )
            ->willReturn(
                '<div class="widget block block-cms-link-inline">
                    <a href="http://magento.test/">
                        <span>Home page</span>
                    </a>
                </div>'
            );

        $this->filter->widgetDirective([
                1 => $construction,
                2 => 'type="\Magento\Cms\Block\Widget\Page\Link" page_id="1"'
            ]);
    }

    public function testWidgetDirectiveWithoutRequiredVariable()
    {
        $construction = '{{widget type="\Magento\Cms\Block\Widget\Page\Link" page_id="1"}}';

        $this->storeManager->expects($this->never())
            ->method('getStore');
        $result = $this->filter->widgetDirective(
            [
                0 => $construction,
                1 => 'type="\Magento\Cms\Block\Widget\Page\Link" page_id="1"'
            ]
        );

        $this->assertEquals($construction, $result);
    }
}
