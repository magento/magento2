<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class InvoiceCreateTest
 */
class InvoiceCreateTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/invoices';

    const SERVICE_READ_NAME = 'salesInvoiceRepositoryV1';

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
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testInvoke()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
        $orderItems = $order->getAllItems();
        $data = [
            'order_id' => $order->getId(),
            'base_currency_code' => null,
            'base_discount_amount' => null,
            'base_grand_total' => null,
            'base_discount_tax_compensation_amount' => null,
            'base_shipping_amount' => null,
            'base_shipping_discount_tax_compensation_amnt' => null,
            'base_shipping_incl_tax' => null,
            'base_shipping_tax_amount' => null,
            'base_subtotal' => null,
            'base_subtotal_incl_tax' => null,
            'base_tax_amount' => null,
            'base_total_refunded' => null,
            'base_to_global_rate' => null,
            'base_to_order_rate' => null,
            'billing_address_id' => null,
            'can_void_flag' => null,
            'created_at' => null,
            'discount_amount' => null,
            'discount_description' => null,
            'email_sent' => null,
            'entity_id' => null,
            'global_currency_code' => null,
            'grand_total' => null,
            'discount_tax_compensation_amount' => null,
            'increment_id' => null,
            'is_used_for_refund' => null,
            'order_currency_code' => null,
            'shipping_address_id' => null,
            'shipping_amount' => null,
            'shipping_discount_tax_compensation_amount' => null,
            'shipping_incl_tax' => null,
            'shipping_tax_amount' => null,
            'state' => null,
            'store_currency_code' => null,
            'store_id' => null,
            'store_to_base_rate' => null,
            'store_to_order_rate' => null,
            'subtotal' => null,
            'subtotal_incl_tax' => null,
            'tax_amount' => null,
            'total_qty' => '1',
            'transaction_id' => null,
            'updated_at' => null,
            'items' => [
                [
                    'orderItemId' => $orderItems[0]->getId(),
                    'qty' => 2,
                    'additionalData' => null,
                    'baseCost' => null,
                    'baseDiscountAmount' => null,
                    'baseDiscountTaxCompensationAmount' => null,
                    'basePrice' => null,
                    'basePriceInclTax' => null,
                    'baseRowTotal' => null,
                    'baseRowTotalInclTax' => null,
                    'baseTaxAmount' => null,
                    'description' => null,
                    'discountAmount' => null,
                    'discountTaxCompensationAmount' => null,
                    'name' => null,
                    'entity_id' => null,
                    'parentId' => null,
                    'price' => null,
                    'priceInclTax' => null,
                    'productId' => null,
                    'rowTotal' => null,
                    'rowTotalInclTax' => null,
                    'sku' => 'sku' . uniqid(),
                    'taxAmount' => null,
                ],
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['entity' => $data]);
        $this->assertNotEmpty($result);
    }
}
