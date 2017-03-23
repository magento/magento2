<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const CONFIGURABLE_PRODUCT_SKU = 'configurable';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testAddProduct()
    {
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
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @expectedException \Exception
     * @expectedExceptionMessage You need to choose options for your item.
     */
    public function testAddProductWithIncorrectOptions()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' =>  '/V1/carts/' . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
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
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     * @expectedException \Exception
     * @expectedExceptionMessage Cart %1 does not contain item %2
     */
    public function testUpdateIncorrectItem()
    {
        $qty = 1;
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
        $cartId = $quote->getId();

        $requestData = $this->getRequestData($cartId, 1);
        $requestData['cartItem']['qty'] = $qty;
        $requestData['cartItem']['item_id'] = 1000;

        $serviceInfo = [
            'rest' => [
                'resourcePath' =>  '/V1/carts/' . $cartId . '/items/1000',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testUpdate()
    {
        $qty = 4;
        $this->updateStockForItem(10, 100);
        $this->updateStockForItem(20, 100);

        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
        $cartId = $quote->getId();

        $items = $quote->getAllItems();
        $this->assertGreaterThan(0, count($items));

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item|null $item */
        $item = null;
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $quoteItem */
        foreach ($items as $quoteItem) {
            if ($quoteItem->getProductType() == Configurable::TYPE_CODE) {
                $item = $quoteItem;
                break;
            }
        }

        $this->assertNotNull($item);
        $this->assertNotNull($item->getId());
        $this->assertEquals(Configurable::TYPE_CODE, $item->getProductType());

        $requestData = $this->getRequestData($cartId, 1);
        $requestData['cartItem']['qty'] = $qty;
        $requestData['cartItem']['item_id'] = $item->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' =>  '/V1/carts/' . $cartId . '/items/' . $item->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotNull($response['item_id']);
        $this->assertEquals(Configurable::TYPE_CODE, $response['product_type']);
        $this->assertEquals($cartId, $response['quote_id']);
        $this->assertEquals($qty, $response['qty']);
        $this->assertEquals(
            $response['product_option']['extension_attributes']['configurable_item_options'][0],
            $requestData['cartItem']['product_option']['extension_attributes']['configurable_item_options'][0]
        );
    }

    /**
     * @param int $itemId
     * @param int $qty
     */
    protected function updateStockForItem($itemId, $qty)
    {
        /** @var \Magento\CatalogInventory\Model\Stock\Status $stockStatus */
        $stockStatus = $this->objectManager->create(\Magento\CatalogInventory\Model\Stock\Status::class);
        $stockStatus->load($itemId, 'product_id');
        if (!$stockStatus->getProductId()) {
            $stockStatus->setProductId($itemId);
        }
        $stockStatus->setQty($qty);
        $stockStatus->setStockStatus(1);
        $stockStatus->save();

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $this->objectManager->create(\Magento\CatalogInventory\Model\Stock\Item::class);
        $stockItem->load($itemId, 'product_id');

        if (!$stockItem->getProductId()) {
            $stockItem->setProductId($itemId);
        }
        $stockItem->setUseConfigManageStock(1);
        $stockItem->setQty($qty);
        $stockItem->setIsQtyDecimal(0);
        $stockItem->setIsInStock(1);
        $stockItem->save();
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testUpdateQty()
    {
        $qty = 1;
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
        $cartId = $quote->getId();

        $items = $quote->getAllItems();
        $this->assertGreaterThan(0, count($items));

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item|null $item */
        $item = null;
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $quoteItem */
        foreach ($items as $quoteItem) {
            if ($quoteItem->getProductType() == Configurable::TYPE_CODE) {
                $item = $quoteItem;
                break;
            }
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' =>  '/V1/carts/' . $cartId . '/items/' . $item->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $this->assertNotNull($item);
        $this->assertNotNull($item->getId());
        $this->assertEquals(Configurable::TYPE_CODE, $item->getProductType());

        $requestData = $this->getRequestData($cartId);
        $requestData['cartItem']['qty'] = $qty;
        $requestData['cartItem']['item_id'] = $item->getId();
        $requestData['cartItem']['product_option'] = null;

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotNull($response['item_id']);
        $this->assertEquals($item->getId(), $response['item_id']);
        $this->assertEquals(Configurable::TYPE_CODE, $response['product_type']);
        $this->assertEquals($cartId, $response['quote_id']);
        $this->assertEquals($qty, $response['qty']);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
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

    /**
     * @param $cartId
     * @param null $selectedOption
     * @return array
     */
    protected function getRequestData($cartId, $selectedOption = null)
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
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
