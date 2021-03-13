<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for cart item repository with bundle product.
 */
class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';
    const RESOURCE_PATH = '/V1/carts/';

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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_bundle_and_options.php
     */
    public function testGetAll(): void
    {
        /** @var $quote Quote */
        $quote = $this->objectManager->create(Quote::class)->load(
            'test_order_bundle',
            'reserved_order_id'
        );
        $quoteId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quoteId . '/items',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['cartId' => $quoteId]);
        $this->assertCount(1, $response);
        $response = $response[0];
        $bundleOption = $quote->getItemById($response['item_id'])->getBuyRequest()->getBundleOption();
        $bundleOptionQty = $quote->getItemById($response['item_id'])->getBuyRequest()->getBundleOptionQty();
        $actualOptions = $response['product_option']['extension_attributes']['bundle_options'];

        $this->assertEquals(array_keys($bundleOption), array_column($actualOptions, 'option_id'));
        $this->assertEquals($bundleOptionQty, array_column($actualOptions, 'option_qty', 'option_id'));
        foreach ($actualOptions as $option) {
            $id = $option['option_id'];
            $expectedSelections = is_array($bundleOption[$id]) ? $bundleOption[$id] : [$bundleOption[$id]];
            $this->assertEquals($expectedSelections, $option['option_selections']);
        }
    }
}
