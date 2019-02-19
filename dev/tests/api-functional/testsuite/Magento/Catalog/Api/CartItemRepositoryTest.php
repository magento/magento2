<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const SIMPLE_PRODUCT_SKU = 'simple';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAddProductToCartWithCustomOptions()
    {
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $this->getRequestData($cartId));
        $this->assertTrue($quote->hasProductId($product->getId()));
        $this->assertEquals(1, count($quote->getAllItems()));
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        $item = $quote->getAllItems()[0];
        $this->assertEquals(
            [
                'item_id' => $item->getItemId(),
                'sku' => $item->getSku(),
                'qty' => $item->getQty(),
                'name' => $item->getName(),
                
                'product_type' => $item->getProductType(),
                'quote_id' => $item->getQuoteId(),
                'product_option' => [
                    'extension_attributes' => [
                        'custom_options' => $this->getOptions(),
                    ],
                ],
            ],
            $response
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/quote_with_product_and_custom_options.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
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

    /**
     * Receive product options with values
     *
     * @return array
     */
    protected function getOptions()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);
        $options = [];
        /** @var ProductCustomOptionInterface $option */
        foreach ($product->getOptions() as $option) {
            $options[] = [
                'option_id' => $option->getId(),
                'option_value' => $this->getOptionRequestValue($option),
            ];
        }

        return $options;
    }

    /**
     * @param $cartId
     * @return array
     */
    protected function getRequestData($cartId)
    {
        return [
            'cartItem' => [
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'qty' => 1,
                'quote_id' => $cartId,
                'product_option' => [
                    'extension_attributes' => [
                        'custom_options' => $this->getOptions(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Receive option value based on option type
     *
     * @param ProductCustomOptionInterface $option
     * @return null|string
     */
    protected function getOptionRequestValue(ProductCustomOptionInterface $option)
    {
        $returnValue = null;
        switch ($option->getType()) {
            case 'field':
                $returnValue = 'Test value';
                break;
            case 'date_time':
                $returnValue = '2015-09-09 07:16:00';
                break;
            case 'drop_down':
                $returnValue = '3-1-select';
                break;
            case 'radio':
                $returnValue = '4-1-radio';
                break;
        }
        return $returnValue;
    }
}
