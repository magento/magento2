<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\Dashboard\Tab;

use Magento\Backend\Block\Dashboard\Graph;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Backend\Block\Dashboard\Tab\Orders class.
 *
 * @magentoAppArea adminhtml
 */
class OrdersTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Graph
     */
    private $graphBlock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->graphBlock = $this->layout->createBlock(Graph::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list_with_invoice.php
     * @dataProvider chartUrlDataProvider
     * @param string $period
     * @return void
     */
    public function testGetChartUrl(string $period): void
    {
        $this->graphBlock->getRequest()->setParams(['period' => $period]);
        /** @var Orders $ordersBlock */
        $ordersBlock = $this->layout->createBlock(Orders::class);
        $decodedChartUrl = urldecode($ordersBlock->getChartUrl());
        $this->assertEquals(
            $this->getExpectedAxisRange($ordersBlock),
            $this->getUrlParamData($decodedChartUrl, 'chxr')
        );
    }

    /**
     * @return array
     */
    public function chartUrlDataProvider(): array
    {
        return [
            'Last 24 Hours' => ['24h'],
            'Last 7 Days' => ['7d'],
            'Current Month' => ['1m'],
            'YTD' => ['1y'],
            '2YTD' => ['2y'],
        ];
    }

    /**
     * @param string $chartUrl
     * @param string $paramName
     * @return string
     */
    private function getUrlParamData(string $chartUrl, string $paramName): string
    {
        $chartUrlSegments = explode('&', $chartUrl);
        foreach ($chartUrlSegments as $chartUrlSegment) {
            [$paramKey, $paramValue] = explode('=', $chartUrlSegment);
            if ($paramKey === $paramName) {
                return $paramValue;
            }
        }

        return '';
    }

    /**
     * @param Orders $ordersBlock
     * @return string
     */
    private function getExpectedAxisRange(Orders $ordersBlock): string
    {
        $dashboardOrders = $ordersBlock->getDataHelper()->getItems();
        $ordersQty = array_map(function ($order) {
            return $order->getQuantity();
        }, $dashboardOrders);

        $axisPeak = max(array_values($ordersQty)) + 1;

        return "1,0,{$axisPeak},1";
    }
}
