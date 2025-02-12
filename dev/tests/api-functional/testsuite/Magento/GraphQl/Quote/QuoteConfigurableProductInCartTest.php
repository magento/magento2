<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Configurable product in cart testcases
 */
class QuoteConfigurableProductInCartTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteIdInterface;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->quoteIdToMaskedQuoteIdInterface = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->productRepository = $objectManager->get(\Magento\Catalog\Model\ProductRepository::class);
    }

    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$p1$', '$p2$']],
            'cp1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$cp1.id$', 'child_product_id' => '$p1.id$', 'qty' => 1],
        ),
    ]
    public function testConfigurableProductInCartAfterGoesOutOfStock()
    {
        $product1 = $this->fixtures->get('p1');
        $product1 = $this->productRepository->get($product1->getSku(), true);
        $stockItem = $product1->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(0);
        $stockItem->setIsInStock(false);
        $this->productRepository->save($product1);

        $product2 = $this->fixtures->get('p2');
        $product2 = $this->productRepository->get($product2->getSku(), true);
        $stockItem = $product2->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(0);
        $stockItem->setIsInStock(false);
        $this->productRepository->save($product2);
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int)$cart->getId());

        $query = <<<'QUERY'
query GetCartDetails($cartId: String!) {
    cart(cart_id: $cartId) {
        id
        items {
            uid
            product {
                uid
                name
                sku
                stock_status
                price_range {
                    minimum_price {
                        final_price {
                            currency
                            value
                        }
                        regular_price {
                            currency
                            value
                        }
                    }
                    maximum_price {
                        final_price {
                            currency
                            value
                        }
                        regular_price {
                            currency
                            value
                        }
                    }
                }
            }
            prices {
                price {
                    currency
                    value
                }
            }
            errors {
                code
                message
            }
        }
    }
}
QUERY;

        $variables = [
            'cartId' => $maskedQuoteId
        ];

        $response = $this->graphQlQuery($query, $variables);
        $this->assertEquals($maskedQuoteId, $response['cart']['id'], 'Assert that correct quote is queried');
        $this->assertEquals(
            'OUT_OF_STOCK',
            $response['cart']['items'][0]['product']['stock_status'],
            'Assert product is out of stock'
        );
        $this->assertEquals(
            0,
            $response['cart']['items'][0]['product']['price_range']['minimum_price']['final_price']['value'],
            'Assert that minimum price equals to 0'
        );
        $this->assertEquals(
            0,
            $response['cart']['items'][0]['product']['price_range']['maximum_price']['final_price']['value'],
            'Assert that maximum price equals to 0'
        );
        $this->assertEquals('ITEM_QTY', $response['cart']['items'][0]['errors'][0]['code']);
    }
}
