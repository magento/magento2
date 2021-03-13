<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Api;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for cart item repository with downloadable product.
 */
class CartItemRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteCartItemRepositoryV1';

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
     * @magentoApiDataFixture Magento/Downloadable/_files/quote_with_downloadable_product.php
     */
    public function testGetList()
    {
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku('downloadable-product'));
        // use ID of the first downloadable link
        $linkId = array_values($product->getDownloadableLinks())[0]->getId();

        /** @var  CartItemInterface $item */
        $item = $quote->getAllItems()[0];
        $expectedResult = [[
            'item_id' => $item->getItemId(),
            'sku' => $item->getSku(),
            'name' => $item->getName(),
            'price' => $item->getPrice(),
            'qty' => $item->getQty(),
            'product_type' => $item->getProductType(),
            'quote_id' => $item->getQuoteId(),
            'product_option' => [
                'extension_attributes' => [
                    'downloadable_option' => [
                        'downloadable_links' => [$linkId]
                    ]
                ]
            ]
        ]];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $this->assertEquals($expectedResult, $this->_webApiCall($serviceInfo, $requestData));
    }
}
