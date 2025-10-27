<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentInterfaceFactory;
use Magento\Sales\Controller\Adminhtml\Order\ExportBase;

/**
 * Tests for shipment export via admin grids.
 */
class ExportTest extends ExportBase
{
    /**
     * @var ShipmentInterfaceFactory
     */
    private $shipmentFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->shipmentFactory = $this->_objectManager->get(ShipmentInterfaceFactory::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture general/locale/timezone America/Chicago
     * @magentoConfigFixture test_website general/locale/timezone America/Adak
     * @magentoDataFixture Magento/Sales/_files/order_with_invoice_shipment_creditmemo_on_second_website.php
     * @dataProvider exportShipmentDataProvider
     * @param string $format
     * @param bool $addIdToUrl
     * @param string $namespace
     * @return void
     */
    public function testExportShipment(
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
        $shipments = $this->parseResponse($format, $response);
        $shipment = $this->getShipment('200000001');
        $exportedShipment = reset($shipments);
        $this->assertNotFalse($exportedShipment);
        $this->assertEquals(
            $this->prepareDate($shipment->getCreatedAt(), 'America/Chicago'),
            $exportedShipment['Ship Date']
        );
        $this->assertEquals(
            $this->prepareDate($order->getCreatedAt(), 'America/Chicago'),
            $exportedShipment['Order Date']
        );
    }

    /**
     * @return array
     */
    public static function exportShipmentDataProvider(): array
    {
        return [
            'shipment_grid_in_csv' => [
                'format' => ExportBase::CSV_FORMAT,
                'addIdToUrl' => false,
                'namespace' => 'sales_order_shipment_grid',
            ],
            'shipment_grid_in_csv_from_order_view' => [
                'format' => ExportBase::CSV_FORMAT,
                'addIdToUrl' => true,
                'namespace' => 'sales_order_view_shipment_grid',
            ],
            'shipment_grid_in_xml' => [
                'format' => ExportBase::XML_FORMAT,
                'addIdToUrl' => false,
                'namespace' => 'sales_order_shipment_grid',
            ],
            'shipment_grid_in_xml_from_order_view' => [
                'format' => ExportBase::XML_FORMAT,
                'addIdToUrl' => true,
                'namespace' => 'sales_order_view_shipment_grid',
            ],
        ];
    }

    /**
     * Returns shipment by increment id.
     *
     * @param string $incrementId
     * @return ShipmentInterface
     */
    private function getShipment(string $incrementId): ShipmentInterface
    {
        return $this->shipmentFactory->create()->loadByIncrementId($incrementId);
    }
}
