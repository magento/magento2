<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceInterfaceFactory;
use Magento\Sales\Controller\Adminhtml\Order\ExportBase;

/**
 * Tests for invoice export via admin grids.
 */
class ExportTest extends ExportBase
{
    /**
     * @var InvoiceInterfaceFactory
     */
    private $invoiceFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceFactory = $this->_objectManager->get(InvoiceInterfaceFactory::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture general/locale/timezone America/Chicago
     * @magentoConfigFixture test_website general/locale/timezone America/Adak
     * @magentoDataFixture Magento/Sales/_files/order_with_invoice_shipment_creditmemo_on_second_website.php
     * @dataProvider exportInvoiceDataProvider
     * @param string $format
     * @param bool $addIdToUrl
     * @param string $namespace
     * @return void
     */
    public function testExportInvoice(
        string $format,
        bool $addIdToUrl,
        string $namespace
    ): void {
        $order = $this->getOrder('200000001');
        $url = $this->getExportUrl($format, $addIdToUrl ? (int)$order->getId() : null);
        $response = $this->dispatchExport(
            $url,
            ['namespace' => $namespace, 'filters' => ['order_increment_id' => '200000001']]
        );
        $invoices = $this->parseResponse($format, $response);
        $invoice = $this->getInvoice('200000001');
        $exportedInvoice = reset($invoices);
        $this->assertNotFalse($exportedInvoice);
        $this->assertEquals(
            $this->prepareDate($invoice->getCreatedAt(), 'America/Chicago'),
            $exportedInvoice['Invoice Date']
        );
        $this->assertEquals(
            $this->prepareDate($order->getCreatedAt(), 'America/Chicago'),
            $exportedInvoice['Order Date']
        );
    }

    /**
     * @return array
     */
    public static function exportInvoiceDataProvider(): array
    {
        return [
            'invoice_grid_in_csv' => [
                'format' => ExportBase::CSV_FORMAT,
                'addIdToUrl' => false,
                'namespace' => 'sales_order_invoice_grid',
            ],
            'invoice_grid_in_csv_from_order_view' => [
                'format' => ExportBase::CSV_FORMAT,
                'addIdToUrl' => true,
                'namespace' => 'sales_order_view_invoice_grid',
            ],
            'invoice_grid_in_xml' => [
                'format' => ExportBase::XML_FORMAT,
                'addIdToUrl' => false,
                'namespace' => 'sales_order_invoice_grid',
            ],
            'invoice_grid_in_xml_from_order_view' => [
                'format' => ExportBase::XML_FORMAT,
                'addIdToUrl' => true,
                'namespace' => 'sales_order_view_invoice_grid',
            ],
        ];
    }

    /**
     * Returns invoice by increment id.
     *
     * @param string $incrementId
     * @return InvoiceInterface
     */
    private function getInvoice(string $incrementId): InvoiceInterface
    {
        return $this->invoiceFactory->create()->loadByIncrementId($incrementId);
    }
}
