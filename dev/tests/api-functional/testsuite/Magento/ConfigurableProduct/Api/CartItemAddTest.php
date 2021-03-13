<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * API test for add cart item with configurable product.
 */
class CartItemAddTest extends WebapiAbstract
{
    const SERVICE_NAME = 'quoteAddCartItemV1';
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     */
    public function testAddProduct(): void
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $this->getRequestData($cartId));
        $this->assertNotNull($response['item_id']);
        $this->assertEquals(Configurable::TYPE_CODE, $response['product_type']);

        $quote->load('test_order_1', 'reserved_order_id');
        $items = $quote->getAllItems();
        $this->assertGreaterThan(0, count($items));

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item|null $item */
        $item = null;
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $quoteItem */
        foreach ($items as $quoteItem) {
            if ($quoteItem->getProductType() == Configurable::TYPE_CODE && !$quoteItem->getParentItemId()) {
                $item = $quoteItem;
                break;
            }
        }
        $this->assertNotNull($item);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     */
    public function testAddProductWithIncorrectOptions(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You need to choose options for your item.');

        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' =>  '/V1/carts/' . $cartId . '/items',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $requestData = $this->getRequestData($cartId);
        $requestData['cartItem']['product_option']['extension_attributes']
        ['configurable_item_options'][0]['option_id'] = 1000;

        $requestData['cartItem']['product_option']['extension_attributes']
        ['configurable_item_options'][0]['option_value'] = 2000;

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @param $cartId
     * @param null $selectedOption
     * @return array
     */
    protected function getRequestData($cartId, $selectedOption = null): array
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get(self::CONFIGURABLE_PRODUCT_SKU);

        $configurableProductOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();

        $optionKey = 0;
        if ($selectedOption && isset($options[$selectedOption])) {
            $optionKey = $selectedOption;
        }

        $attributeId = $configurableProductOptions[0]->getAttributeId();
        $options = $configurableProductOptions[0]->getOptions();
        $optionId = $options[$optionKey]['value_index'];

        return [
            'cartItem' => [
                'sku' => self::CONFIGURABLE_PRODUCT_SKU,
                'qty' => 1,
                'quote_id' => $cartId,
                'product_option' => [
                    'extension_attributes' => [
                        'configurable_item_options' => [
                            [
                                'option_id' => $attributeId,
                                'option_value' => $optionId
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
