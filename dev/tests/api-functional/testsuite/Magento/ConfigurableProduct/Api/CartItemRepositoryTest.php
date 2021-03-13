<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * API test for cart item repository with configurable product.
 */
class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const CONFIGURABLE_PRODUCT_SKU = 'configurable';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testGetList()
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['cartId' => $cartId]);

        $this->assertGreaterThan(0, count($response));
        $item = $response[0];

        $this->assertNotNull($item['item_id']);
        $this->assertEquals(Configurable::TYPE_CODE, $item['product_type']);
        $this->assertArrayHasKey('product_option', $item);
        $this->assertArrayHasKey('extension_attributes', $item['product_option']);
        $this->assertArrayHasKey('configurable_item_options', $item['product_option']['extension_attributes']);

        $options = $item['product_option']['extension_attributes']['configurable_item_options'];
        $this->assertGreaterThan(0, count($options));

        $this->assertArrayHasKey('option_id', $options[0]);
        $this->assertArrayHasKey('option_value', $options[0]);

        $this->assertNotNull($options[0]['option_id']);
        $this->assertNotNull($options[0]['option_value']);
    }
}
