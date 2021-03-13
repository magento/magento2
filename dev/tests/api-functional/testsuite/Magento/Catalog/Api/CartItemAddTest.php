<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

/**
 * Class \Magento\Catalog\Api\CartItemRepositoryTest
 */
class CartItemAddTest extends WebapiAbstract
{
    const SERVICE_NAME = 'quoteAddCartItemV1';
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
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAddProductToCartWithCustomOptions(): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);

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
        $this->assertTrue($quote->hasProductId($product->getId()));
        $this->assertCount(1, $quote->getAllItems());
        /** @var CartItemInterface $item */
        $item = $quote->getAllItems()[0];
        $this->assertEquals(
            [
                'item_id' => $item->getItemId(),
                'sku' => $item->getSku(),
                'qty' => $item->getQty(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
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
     * Receive product options with values
     *
     * @return array
     */
    private function getOptions(): array
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
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
    private function getRequestData($cartId): array
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
