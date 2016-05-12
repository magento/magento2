<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class CreditMemoCreateRefundTest
 */
class CreditMemoCreateRefundTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/creditmemo/refund';

    const SERVICE_READ_NAME = 'salesCreditmemoManagementV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/invoice.php
     */
    public function testInvoke()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $orderCollection = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Collection');
        $order = $orderCollection->getFirstItem();
        $items = [

        ];
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getAllItems() as $orderItem) {
            $items[] = [
                'order_item_id' => $orderItem->getId(),
                'qty' => $orderItem->getQtyInvoiced(),
                'price' => $orderItem->getPrice(),
                'row_total' => $orderItem->getRowTotal(),
            ];
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME.'refund',
            ],
        ];
        $data = [
            'order_id' => $order->getId(),
            'subtotal' => $order->getSubtotal(),
            'grand_total' => $order->getGrandTotal(),
            'base_grand_total' => $order->getBaseGrandTotal(),
            'base_shipping_amount' => $order->getBaseShippingAmount(),
            'shipping_address_id' => $order->getShippigAddressId(),
            'billing_address_id' => $order->getBillingAddressId(),
            'invoiceId' => $order->getInvoiceCollection()->getFirstItem()->getId(),
            'items' => $items,
        ];
        $result = $this->_webApiCall(
            $serviceInfo,
            ['creditmemo' => $data, 'offline_requested' => true]
        );
        $this->assertNotEmpty($result);
        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($order->getId());
        $this->assertEquals(Order::STATE_CLOSED, $order->getState());
    }
}
