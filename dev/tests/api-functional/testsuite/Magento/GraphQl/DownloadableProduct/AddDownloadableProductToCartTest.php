<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\DownloadableProduct;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test cases for adding downloadable product to cart.
 */
class AddDownloadableProductToCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * Add a downloadable product into shopping cart when "Links can be purchased separately" is enabled
     *
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_purchased_separately_links.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithCustomLinks()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $sku = 'downloadable-product-with-purchased-separately-links';
        $qty = 1;
        $links = $this->getProductsLinks($sku);
        $linkId = key($links);

        $query = <<<MUTATION
mutation {
    addDownloadableProductsToCart(
        input: {
            cart_id: "{$maskedQuoteId}", 
            cart_items: [
                {
                    data: {
                        quantity: {$qty}, 
                        sku: "{$sku}"
                    },
                    downloadable_product_links: [
                        {
          	                link_id: {$linkId}
                        }
                    ]
                }
            ]
        }
    ) {
        cart {
            items {
                quantity
                ... on DownloadableCartItem {
                    links {
                        title
                        link_type
                        price
                    }
                    samples {
                        id
                        title
                    }
                }
            }
        }
    }
}
MUTATION;
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount($qty, $response['addDownloadableProductsToCart']['cart']);
        self::assertEquals(
            $links[$linkId],
            $response['addDownloadableProductsToCart']['cart']['items'][0]['links'][0]
        );
    }

    /**
     * Add a downloadable product into shopping cart when "Links can be purchased separately" is disabled
     *
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_without_purchased_separately_links.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithoutCustomLinks()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = 'downloadable-product-without-purchased-separately-links';
        $qty = 1;
        $links = $this->getProductsLinks($sku);

        $query = <<<MUTATION
mutation {
    addDownloadableProductsToCart(
        input: {
            cart_id: "{$maskedQuoteId}",
            cart_items: [
                {
                    data: {
                        quantity: {$qty},
                        sku: "{$sku}"
                    }
                }
            ]
        }
    ) {
        cart {
            items {
                quantity
                ... on DownloadableCartItem {
                    links {
                        title
                        link_type
                        price
                    }
                }
            }
        }
    }
}
MUTATION;
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount($qty, $response['addDownloadableProductsToCart']['cart']);
        self::assertEquals(
            $links[key($links)],
            $response['addDownloadableProductsToCart']['cart']['items'][0]['links'][0]
        );
    }

    /**
     * Function returns array of all product's links
     *
     * @param string $sku
     * @return array
     */
    private function getProductsLinks(string $sku) : array
    {
        $result = [];
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $product = $productRepository->get($sku, false, null, true);

        foreach ($product->getDownloadableLinks() as $linkObject) {
            $result[$linkObject->getLinkId()] = [
                'title' => $linkObject->getTitle(),
                'link_type' => null, //deprecated field
                'price' => $linkObject->getPrice(),
            ];
        }

        return $result;
    }
}
