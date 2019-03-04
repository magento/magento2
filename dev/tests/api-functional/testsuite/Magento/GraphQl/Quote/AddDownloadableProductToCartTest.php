<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Class AddDownloadableProductToCartTest
 */
class AddDownloadableProductToCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * Test adding a downloadable product to the shopping cart
     *
     * @magentoApiDataFixture Magento/GraphQl/_files/product_downloadable_with_purchased_separately_links.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithCustomLinks()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $sku    = 'graphql-downloadable-product-with-purchased-separately-links';
        $qty    = 1;
        $links  = $this->getProductsLinks($sku);
        $linkId = key($links);

        $query = <<<MUTATION
mutation {
    addDownloadableProductsToCart(
        input: {
            cart_id: "{$maskedQuoteId}", 
            cartItems: [
                {
                    data: {
                        qty: {$qty}, 
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
                qty
                ... on DownloadableCartItem {
                    downloadable_product_links {
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
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount($qty, $response['addDownloadableProductsToCart']['cart']);
        self::assertEquals(
            $links[$linkId],
            $response['addDownloadableProductsToCart']['cart']['items'][0]['downloadable_product_links'][0]
        );
    }

    /**
     * Test adding a downloadable product to the shopping cart
     *
     * @magentoApiDataFixture Magento/GraphQl/_files/product_downloadable_without_purchased_separately_links.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithoutCustomLinks()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $sku   = 'graphql-downloadable-product-without-purchased-separately-links';
        $qty   = 1;
        $links = $this->getProductsLinks($sku);

        $query = <<<MUTATION
mutation {
    addDownloadableProductsToCart(
        input: {
            cart_id: "{$maskedQuoteId}", 
            cartItems: [
                {
                    data: {
                        qty: {$qty}, 
                        sku: "{$sku}"
                    }
                }
            ]
        }
    ) {
        cart {
            items {
                qty
                ... on DownloadableCartItem {
                    downloadable_product_links {
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
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount($qty, $response['addDownloadableProductsToCart']['cart']);
        self::assertEquals(
            $links[key($links)],
            $response['addDownloadableProductsToCart']['cart']['items'][0]['downloadable_product_links'][0]
        );
    }

    /**
     * Function returns array of all product's links
     *
     * @param string $sku
     * @return array
     */
    private function getProductsLinks($sku = '')
    {
        $result = [];
        $productRepository = Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $product = $productRepository->get($sku, false, null, true);

        foreach ($product->getDownloadableLinks() as $linkObject) {
            $result[$linkObject->getLinkId()] = [
                'title'     => $linkObject->getTitle(),
                'link_type' => strtoupper($linkObject->getLinkType()),
                'price'     => $linkObject->getPrice(),
            ];
        }

        return $result;
    }
}
