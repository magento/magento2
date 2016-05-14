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
        $items = [];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $items = $this->getItemsForRest($order);
        } else {
            $items = $this->getItemsForSoap($order);
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
            'adjustment' => null,
            'adjustment_negative' => null,
            'adjustment_positive' => null,
            'base_adjustment' => null,
            'base_adjustment_negative' => null,
            'base_adjustment_positive' => null,
            'base_currency_code' => null,
            'base_discount_amount' => null,
            'base_discount_tax_compensation_amount' => null,
            'base_shipping_discount_tax_compensation_amnt' => null,
            'base_shipping_incl_tax' => null,
            'base_shipping_tax_amount' => null,
            'base_subtotal' => null,
            'base_subtotal_incl_tax' => null,
            'base_tax_amount' => null,
            'base_to_global_rate' => null,
            'base_to_order_rate' => null,
            'created_at' => null,
            'creditmemo_status' => null,
            'discount_amount' => null,
            'discount_description' => null,
            'email_sent' => null,
            'entity_id' => null,
            'global_currency_code' => null,
            'discount_tax_compensation_amount' => null,
            'increment_id' => null,
            'invoice_id' => null,
            'order_currency_code' => null,
            'shipping_amount' => null,
            'shipping_discount_tax_compensation_amount' => null,
            'shipping_incl_tax' => null,
            'shipping_tax_amount' => null,
            'state' => null,
            'store_currency_code' => null,
            'store_id' => null,
            'store_to_base_rate' => null,
            'store_to_order_rate' => null,
            'subtotal_incl_tax' => null,
            'tax_amount' => null,
            'transaction_id' => null,
            'updated_at' => null,
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

    private function getItemsForRest($order)
    {
        $items = [];
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getAllItems() as $orderItem) {
            $items[] = [
                'order_item_id' => $orderItem->getId(),
                'qty' => $orderItem->getQtyInvoiced(),
                'price' => $orderItem->getPrice(),
                'row_total' => $orderItem->getRowTotal(),
                'entity_id' => null,
            ];
        }
        return $items;
    }

    private function getItemsForSoap($order)
    {
        $items = [];
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getAllItems() as $orderItem) {
            $items[] = array_merge($orderItem->getData(), [
                'order_item_id' => $orderItem->getId(),
                'qty' => $orderItem->getQtyInvoiced(),
                'price' => $orderItem->getPrice(),
                'row_total' => $orderItem->getRowTotal(),
                'entity_id' => null,
            ]);
        }
        return $items;
    }
}
