<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\DownloadableProduct;

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
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quoteResource = $this->objectManager->get(QuoteResource::class);
        $this->quote = $this->objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * Add a downloadable product into shopping cart when "Links can be purchased separately" is enabled
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
                    downloadable_product_links {
                        title
                        link_type
                        price
                    }
                    downloadable_product_samples {
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
            $response['addDownloadableProductsToCart']['cart']['items'][0]['downloadable_product_links'][0]
        );
    }

    /**
     * Add a downloadable product into shopping cart when "Links can be purchased separately" is enabled
     * There is the same value in couple of `link_id` options
     *
     * @magentoApiDataFixture Magento/GraphQl/_files/product_downloadable_with_purchased_separately_links.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithCustomLinks2()
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
                    downloadable_product_links {
                        title
                        link_type
                        price
                    }
                    downloadable_product_samples {
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
            $response['addDownloadableProductsToCart']['cart']['items'][0]['downloadable_product_links'][0]
        );
    }

    /**
     * Add a downloadable product into shopping cart when "Links can be purchased separately" is disabled
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
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount($qty, $response['addDownloadableProductsToCart']['cart']);
        self::assertEquals(
            $links[key($links)],
            $response['addDownloadableProductsToCart']['cart']['items'][0]['downloadable_product_links'][0]
        );
    }

    /**
     * Add a downloadable product into shopping cart when "Links can be purchased separately" is disabled
     * Unnecessary `link_id` option is provided
     *
     * @magentoApiDataFixture Magento/GraphQl/_files/product_downloadable_without_purchased_separately_links.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithoutCustomLinks2()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $sku    = 'graphql-downloadable-product-without-purchased-separately-links';
        $qty    = 1;
        $links  = $this->getProductsLinks($sku);
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
        $response = $this->graphQlMutation($query);
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
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

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
