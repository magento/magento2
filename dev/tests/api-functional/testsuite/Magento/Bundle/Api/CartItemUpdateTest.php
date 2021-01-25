<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Api;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for update cart item with bundle product.
 */
class CartItemUpdateTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteUpdateCartItemV1';
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
    public function testUpdate(): void
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class)->load(
            'test_order_bundle',
            'reserved_order_id'
        );
        $cartId = $quote->getId();
        $cartItem = $quote->getAllVisibleItems()[0];
        $itemSku = $cartItem->getSku();
        $itemId = $cartItem->getId();

        $product = $cartItem->getProduct();
        /** @var $typeInstance Type */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $typeInstance->getOptionsCollection($product);
        $bundleOptions = [];
        /** @var $option Option */
        foreach ($optionCollection as $option) {
            if (!$option->getRequired()) {
                continue;
            }
            $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
            $option = ['option_id' => $option->getId(), 'option_qty' => 1];
            $option['option_selections'] = [$selectionsCollection->getLastItem()->getSelectionId()];
            $bundleOptions[] = $option;
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/items/' . $itemId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        $requestData = [
            "cartItem" => [
                "sku" => $itemSku,
                "qty" => 2,
                "quote_id" => $cartId,
                "item_id" => $itemId,
                "product_option" => [
                    "extension_attributes" => [
                        "bundle_options" => $bundleOptions
                    ]
                ]
            ]
        ];
        $this->_webApiCall($serviceInfo, $requestData);

        $quoteUpdated = $this->objectManager->create(Quote::class)->load(
            'test_order_bundle',
            'reserved_order_id'
        );
        $cartItems = $quoteUpdated->getAllVisibleItems();
        $buyRequest = $cartItems[0]->getBuyRequest()->toArray();

        $this->assertCount(1, $cartItems);
        $this->assertEquals(count($buyRequest['bundle_option']), count($bundleOptions));
        foreach ($bundleOptions as $option) {
            $optionId = $option['option_id'];
            $optionQty = $option['option_qty'];
            $optionSelections = $option['option_selections'];
            $this->assertArrayHasKey($optionId, $buyRequest['bundle_option']);
            $this->assertEquals($optionQty, $buyRequest['bundle_option_qty'][$optionId]);
            $this->assertEquals($optionSelections, $buyRequest['bundle_option'][$optionId]);
        }
    }
}
