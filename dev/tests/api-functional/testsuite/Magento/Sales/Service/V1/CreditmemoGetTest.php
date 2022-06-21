<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

class CreditmemoGetTest extends WebapiAbstract
{
    /**
     * Resource path
     */
    private const RESOURCE_PATH = '/V1/creditmemo';

    /**
     * Service read name
     */
    private const SERVICE_READ_NAME = 'salesCreditmemoRepositoryV1';

    /**
     * Service version
     */
    private const SERVICE_VERSION = 'V1';

    /**
     * Creditmemo id
     */
    private const CREDITMEMO_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Required fields are in the answer
     *
     * @var array
     */
    protected $requiredFields = [
        'entity_id',
        'store_id',
        'base_shipping_tax_amount',
        'base_discount_amount',
        'grand_total',
        'base_subtotal_incl_tax',
        'shipping_amount',
        'subtotal_incl_tax',
        'base_shipping_amount',
        'base_adjustment',
        'base_subtotal',
        'discount_amount',
        'subtotal',
        'adjustment',
        'base_grand_total',
        'base_tax_amount',
        'shipping_tax_amount',
        'tax_amount',
        'order_id',
        'state',
        'increment_id',
    ];

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test creditmemo get service
     *
     * @magentoApiDataFixture Magento\Catalog\Test\Fixture\Product as:product
     * @magentoApiDataFixture Magento\Quote\Test\Fixture\GuestCart as:cart
     * @magentoApiDataFixture Magento\Quote\Test\Fixture\AddProductToCart as:item1
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetShippingAddress with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetBillingAddress with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetGuestEmail with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetDeliveryMethod with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetPaymentMethod with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\PlaceOrder with:{"cart_id":"$cart.id$"} as:order
     * @magentoApiDataFixture Magento\Sales\Test\Fixture\Invoice with:{"order_id":"$order.id$"}
     * @magentoApiDataFixture Magento\Sales\Test\Fixture\Shipment with:{"order_id":"$order.id$"}
     * @magentoApiDataFixture Magento\Sales\Test\Fixture\Creditmemo with:{"order_id":"$order.id$"} as:creditmemo
     * @magentoDataFixtureDataProvider {"item1":{"cart_id":"$cart.id$","product_id":"$product.id$","qty":1}}
     */
    public function testCreditmemoGet()
    {
        $creditmemo = $this->fixtures->get('creditmemo');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $creditmemo->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];

        $actual = $this->_webApiCall($serviceInfo, ['id' => $creditmemo->getId()]);
        $expected = $creditmemo->getData();

        foreach ($this->requiredFields as $field) {
            $this->assertArrayHasKey($field, $actual);
            $this->assertEquals($expected[$field], $actual[$field]);
        }

        //check that nullable fields were marked as optional and were not sent
        foreach ($actual as $value) {
            $this->assertNotNull($value);
        }
    }
}
