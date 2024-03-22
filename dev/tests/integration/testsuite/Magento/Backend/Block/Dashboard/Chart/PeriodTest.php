<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\Dashboard\Chart;

use Magento\Backend\ViewModel\ChartsPeriod;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Checks chart periods on Magento dashboard
 *
 * @magentoAppArea adminhtml
 */
class PeriodTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Template */
    private $block;

    /** @var LayoutInterface */
    private $layout;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Template::class);
        $this->block->setTemplate("Magento_Backend::dashboard/chart/period.phtml");
        $this->block->setData('view_model', $this->objectManager->get(ChartsPeriod::class));
    }

    /**
     * @return void
     */
    public function testChartPeriodOptions(): void
    {
        $html = $this->block->toHtml();
        $dropDownList = [
            __('Today'),
            __('Last 24 Hours'),
            __('Last 7 Days'),
            __('Current Month'),
            __('YTD'),
            __('2YTD')
        ];
        foreach ($dropDownList as $item) {
            $xPath = "//select[@id='dashboard_chart_period']/option[normalize-space(text())='{$item}']";
            $this->assertEquals(1, Xpath::getElementsCountForXpath($xPath, $html));
        }
    }
}
