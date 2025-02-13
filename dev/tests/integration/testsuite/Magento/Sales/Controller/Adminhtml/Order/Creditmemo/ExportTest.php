<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory;
use Magento\Sales\Controller\Adminhtml\Order\ExportBase;

/**
 * Tests for creditmemo export via admin grids.
 */
class ExportTest extends ExportBase
{
    /**
     * @var CollectionFactory
     */
    private $creditmemoCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->creditmemoCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture general/locale/timezone America/Chicago
     * @magentoConfigFixture test_website general/locale/timezone America/Adak
     * @magentoDataFixture Magento/Sales/_files/order_with_invoice_shipment_creditmemo_on_second_website.php
     * @dataProvider exportCreditmemoDataProvider
     * @param string $format
     * @param bool $addIdToUrl
     * @param string $namespace
     * @return void
     */
    public function testExportCreditmemo(
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
        $creditmemos = $this->parseResponse($format, $response);
        $creditmemo = $this->getCreditmemo('200000001');
        $exportedCreditmemo = reset($creditmemos);
        $this->assertNotFalse($exportedCreditmemo);
        $this->assertEquals(
            $this->prepareDate($creditmemo->getCreatedAt(), 'America/Chicago'),
            $exportedCreditmemo['Created']
        );
        $this->assertEquals(
            $this->prepareDate($order->getCreatedAt(), 'America/Chicago'),
            $exportedCreditmemo['Order Date']
        );
    }

    /**
     * @return array
     */
    public static function exportCreditmemoDataProvider(): array
    {
        return [
            'creditmemo_grid_in_csv' => [
                'format' => ExportBase::CSV_FORMAT,
                'addIdToUrl' => false,
                'namespace' => 'sales_order_creditmemo_grid',
            ],
            'creditmemo_grid_in_csv_from_order_view' => [
                'format' => ExportBase::CSV_FORMAT,
                'addIdToUrl' => true,
                'namespace' => 'sales_order_view_creditmemo_grid',
            ],
            'creditmemo_grid_in_xml' => [
                'format' => ExportBase::XML_FORMAT,
                'addIdToUrl' => false,
                'namespace' => 'sales_order_creditmemo_grid',
            ],
            'creditmemo_grid_in_xml_from_order_view' => [
                'format' => ExportBase::XML_FORMAT,
                'addIdToUrl' => true,
                'namespace' => 'sales_order_view_creditmemo_grid',
            ],
        ];
    }

    /**
     * Returns creditmemo by increment id.
     *
     * @param string $incrementId
     * @return CreditmemoInterface
     */
    private function getCreditmemo(string $incrementId): CreditmemoInterface
    {
        /** @var CreditmemoInterface $creditmemo */
        $creditmemo = $this->creditmemoCollectionFactory->create()
            ->addAttributeToFilter(CreditmemoInterface::INCREMENT_ID, $incrementId)
            ->getFirstItem();

        return $creditmemo;
    }
}
