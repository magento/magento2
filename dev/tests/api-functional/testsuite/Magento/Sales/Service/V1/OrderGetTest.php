<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderGetTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders';

    private const SERVICE_READ_NAME = 'salesOrderRepositoryV1';

    private const SERVICE_VERSION = 'V1';

    private const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Checks order attributes.
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderGet(): void
    {
        $expectedOrderData = [
            'base_subtotal' => '100.0000',
            'subtotal' => '100.0000',
            'customer_is_guest' => '1',
            'increment_id' => self::ORDER_INCREMENT_ID,
        ];
        $expectedPayments = [
            'method' => 'checkmo',
            'additional_information' => [
                0 => '11122', // last transaction id
                // metadata
                1 => json_encode([
                    'type' => 'free',
                    'fraudulent' => false
                ])
            ]
        ];
        $expectedBillingAddressNotEmpty = [
            'city',
            'postcode',
            'lastname',
            'street',
            'region',
            'telephone',
            'country_id',
            'firstname',
        ];
        $expectedShippingAddress = [
            'address_type' => 'shipping',
            'city' => 'Los Angeles',
            'email' => 'customer@example.com',
            'postcode' => '11111',
            'region' => 'CA'
        ];

        $result = $this->makeServiceCall(self::ORDER_INCREMENT_ID);

        foreach ($expectedOrderData as $field => $value) {
            self::assertArrayHasKey($field, $result);
            self::assertEquals($value, $result[$field]);
        }

        self::assertArrayHasKey('payment', $result);
        foreach ($expectedPayments as $field => $value) {
            self::assertEquals($value, $result['payment'][$field]);
        }

        self::assertArrayHasKey('billing_address', $result);
        foreach ($expectedBillingAddressNotEmpty as $field) {
            self::assertArrayHasKey($field, $result['billing_address']);
        }

        self::assertArrayHasKey('extension_attributes', $result);
        self::assertArrayHasKey('shipping_assignments', $result['extension_attributes']);

        $shippingAssignments = $result['extension_attributes']['shipping_assignments'];
        self::assertCount(1, $shippingAssignments);
        $shippingAddress = $shippingAssignments[0]['shipping']['address'];
        foreach ($expectedShippingAddress as $key => $value) {
            self::assertArrayHasKey($key, $shippingAddress);
            self::assertEquals($value, $shippingAddress[$key]);
        }

        //check that nullable fields were marked as optional and were not sent
        foreach ($result as $value) {
            self::assertNotNull($value);
        }
    }

    /**
     * Checks order extension attributes.
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_tax.php
     */
    public function testOrderGetExtensionAttributes(): void
    {
        $expectedTax = [
            'code' => 'US-NY-*-Rate 1',
            'type' => 'shipping'
        ];

        $result = $this->makeServiceCall(self::ORDER_INCREMENT_ID);

        $appliedTaxes = $result['extension_attributes']['applied_taxes'];
        self::assertEquals($expectedTax['code'], $appliedTaxes[0]['code']);
        $appliedTaxes = $result['extension_attributes']['item_applied_taxes'];
        self::assertEquals($expectedTax['type'], $appliedTaxes[0]['type']);
        self::assertNotEmpty($appliedTaxes[0]['applied_taxes']);
        self::assertTrue($result['extension_attributes']['converting_from_quote']);
        self::assertArrayHasKey('payment_additional_info', $result['extension_attributes']);
        self::assertNotEmpty($result['extension_attributes']['payment_additional_info']);
    }

    /**
     * Checks if the order contains product option attributes.
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_bundle.php
     */
    public function testGetOrderWithProductOption(): void
    {
        $expected = [
            'extension_attributes' => [
                'bundle_options' => [
                    [
                        'option_id' => 1,
                        'option_selections' => [1],
                        'option_qty' => 1
                    ]
                ]
            ]
        ];
        $result = $this->makeServiceCall(self::ORDER_INCREMENT_ID);

        $bundleProduct = $this->getBundleProduct($result['items']);
        self::assertNotEmpty($bundleProduct, '"Bundle Product" should not be empty.');
        self::assertNotEmpty($bundleProduct['product_option'], '"Product Option" should not be empty.');
        self::assertEquals($expected, $bundleProduct['product_option']);
    }

    /**
     * Gets order by increment ID.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId($incrementId);

        return $order;
    }

    /**
     * Makes service call.
     *
     * @param string $incrementId
     * @return array
     */
    private function makeServiceCall(string $incrementId): array
    {
        $order = $this->getOrder($incrementId);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $order->getId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['id' => $order->getId()]);
    }

    /**
     * Gets a bundle product from the result.
     *
     * @param array $items
     * @return array
     */
    private function getBundleProduct(array $items): array
    {
        foreach ($items as $item) {
            if ($item['product_type'] == 'bundle') {
                return $item;
            }
        }

        return [];
    }
}
