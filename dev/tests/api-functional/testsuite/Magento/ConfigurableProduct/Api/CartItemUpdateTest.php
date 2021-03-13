<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * API test for update cart item with configurable product.
 */
class CartItemUpdateTest extends WebapiAbstract
{
    const SERVICE_NAME = 'quoteUpdateCartItemV1';
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
    public function testUpdate(): void
    {
        $qty = 4;
        $this->updateStockForItem(10, 100);
        $this->updateStockForItem(20, 100);

        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
        $cartId = $quote->getId();

        $items = $quote->getAllItems();
        $this->assertGreaterThan(0, count($items));

        /** @var Item|null $item */
        $item = null;
        /** @var Item $quoteItem */
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
                'httpMethod' => Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
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
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testUpdateQty(): void
    {
        $qty = 1;
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
        $cartId = $quote->getId();

        $items = $quote->getAllItems();
        $this->assertGreaterThan(0, count($items));

        /** @var Item|null $item */
        $item = null;
        /** @var Item $quoteItem */
        foreach ($items as $quoteItem) {
            if ($quoteItem->getProductType() == Configurable::TYPE_CODE) {
                $item = $quoteItem;
                break;
            }
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' =>  '/V1/carts/' . $cartId . '/items/' . $item->getId(),
                'httpMethod' => Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
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
    public function testUpdateIncorrectItem(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The %1 Cart doesn\'t contain the %2 item.');

        $qty = 1;
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_cart_with_configurable', 'reserved_order_id');
        $cartId = $quote->getId();

        $requestData = $this->getRequestData($cartId, 1);
        $requestData['cartItem']['qty'] = $qty;
        $requestData['cartItem']['item_id'] = 1000;

        $serviceInfo = [
            'rest' => [
                'resourcePath' =>  '/V1/carts/' . $cartId . '/items/1000',
                'httpMethod' => Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @param int $itemId
     * @param int $qty
     */
    private function updateStockForItem($itemId, $qty): void
    {
        /** @var Status $stockStatus */
        $stockStatus = $this->objectManager->create(Status::class);
        $stockStatus->load($itemId, 'product_id');
        if (!$stockStatus->getProductId()) {
            $stockStatus->setProductId($itemId);
        }
        $stockStatus->setQty($qty);
        $stockStatus->setStockStatus(1);
        $stockStatus->save();

        /** @var StockItem $stockItem */
        $stockItem = $this->objectManager->create(StockItem::class);
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
     * @param $cartId
     * @param null $selectedOption
     * @return array
     */
    private function getRequestData($cartId, $selectedOption = null): array
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
