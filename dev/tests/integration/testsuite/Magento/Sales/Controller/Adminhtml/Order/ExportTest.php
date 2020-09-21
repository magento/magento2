<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Tests for order export via admin grid.
 */
class ExportTest extends ExportBase
{
    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture general/locale/timezone America/Chicago
     * @magentoConfigFixture test_website general/locale/timezone America/Adak
     * @magentoDataFixture Magento/Sales/_files/order_with_invoice_shipment_creditmemo_on_second_website.php
     * @dataProvider exportOrderDataProvider
     * @param string $format
     * @param string $namespace
     * @return void
     */
    public function testExportOrder(string $format, string $namespace): void
    {
        $order = $this->getOrder('200000001');
        $url = $this->getExportUrl($format, null);
        $response = $this->dispatchExport(
            $url,
            ['namespace' => $namespace, 'filters' => ['increment_id' => '200000001']]
        );
        $orders = [];
        if ($format === ExportBase::CSV_FORMAT) {
            $orders = $this->parseCsvResponse($response);
        } elseif ($format === ExportBase::XML_FORMAT) {
            $orders = $this->parseXmlResponse($response);
        }
        $exportedOrder = reset($orders);
        $this->assertNotFalse($exportedOrder);
        $this->assertEquals(
            $this->prepareDate($order->getCreatedAt(), 'America/Chicago'),
            $exportedOrder['Purchase Date']
        );
    }

    /**
     * @return array
     */
    public function exportOrderDataProvider(): array
    {
        return [
            'order_grid_in_csv' => [
                'format' => ExportBase::CSV_FORMAT,
                'namespace' => 'sales_order_grid',
            ],
            'order_grid_in_xml' => [
                'format' => ExportBase::XML_FORMAT,
                'namespace' => 'sales_order_grid',
            ],
        ];
    }
}
