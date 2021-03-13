<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class \Magento\Catalog\Api\CartItemRepositoryTest
 */
class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const SIMPLE_PRODUCT_SKU = 'simple';

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
     * @magentoApiDataFixture Magento/Catalog/_files/quote_with_product_and_custom_options.php
     */
    public function testGetList(): void
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
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
        $this->assertArrayHasKey('product_option', $item);
        $this->assertArrayHasKey('extension_attributes', $item['product_option']);
        $this->assertArrayHasKey('custom_options', $item['product_option']['extension_attributes']);
        $this->assertGreaterThan(3, count($item['product_option']['extension_attributes']['custom_options']));
        $option = reset($item['product_option']['extension_attributes']['custom_options']);
        $this->assertArrayHasKey('option_id', $option);
        $this->assertArrayHasKey('option_value', $option);
    }
}
