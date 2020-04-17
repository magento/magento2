<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model\Template;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\Template\Filter;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\VariableFactory;
use Magento\Widget\Model\ResourceModel\Widget;
use Pelago\Emogrifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends TestCase
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var State|MockObject
     */
    protected $appState;

    protected function setUp(): void
    {
        $scopeConfig = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            [],
            '',
            false
        );
        $this->storeManager = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );
        $logger = $this->getMockForAbstractClass(LoggerInterface::class, [], '', false);
        $layout = $this->getMockForAbstractClass(LayoutInterface::class, [], '', false);
        $urlModel = $this->getMockForAbstractClass(UrlInterface::class, [], '', false);
        $string = $this->createMock(StringUtils::class);
        $escaper = $this->createMock(Escaper::class);
        $assetRepo = $this->createMock(Repository::class);
        $coreVariableFactory = $this->createPartialMock(VariableFactory::class, ['create']);
        $layoutFactory = $this->createPartialMock(LayoutFactory::class, ['create']);
        $this->appState = $this->createMock(State::class);
        $emogrifier = $this->createMock(Emogrifier::class);
        $configVariables = $this->createMock(Variables::class);
        $widgetResource = $this->createMock(Widget::class);
        $widget = $this->createMock(\Magento\Widget\Model\Widget::class);

        $this->filter = new Filter(
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
        $subscriber = $this->createMock(Subscriber::class);
        $this->filter->setVariables(['subscriber' => $subscriber]);

        $construction = '{{widget type="\Magento\Cms\Block\Widget\Page\Link" page_id="1"}}';

        $store = $this->getMockForAbstractClass(StoreInterface::class, [], '', false);
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
