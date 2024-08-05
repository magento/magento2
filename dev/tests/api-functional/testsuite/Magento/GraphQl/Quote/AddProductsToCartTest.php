<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2024 Adobe
 *  All Rights Reserved.
 *
 *  NOTICE: All information contained herein is, and remains
 *  the property of Adobe and its suppliers, if any. The intellectual
 *  and technical concepts contained herein are proprietary to Adobe
 *  and its suppliers and are protected by all applicable intellectual
 *  property laws, including trade secret and copyright laws.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from Adobe.
 *  ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Get add to cart through GraphQl query and variables
 */
class AddProductsToCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteIdToMaskedQuoteId = Bootstrap::getObjectManager()->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test addProductsToCart mutation by passing SKU Upper & Lower case
     *
     * @param string $sku
     * @dataProvider skuDataProvider
     * @throws NoSuchEntityException
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'Upper_And_Lower_Test_Prod']),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAddProductsToCartWithSKUCaseInsensitive(string $sku): void
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());

        $query = $this->getAddToCartMutation($maskedQuoteId, $sku);
        $response = $this->graphQlMutation($query);
        $result = $response['addProductsToCart'];

        self::assertEmpty($result['user_errors']);
        self::assertCount(1, $result['cart']['items']);

        $cartItem = $result['cart']['items'][0];
        self::assertEquals('Upper_And_Lower_Test_Prod', $cartItem['product']['sku']);
        self::assertEquals(1, $cartItem['quantity']);
    }

    /**
     * Data provider with sku in uppercase and lowercase
     *
     * @return array
     */
    public static function skuDataProvider(): array
    {
        return [
            'upper' => ['UPPER_AND_LOWER_TEST_PROD'],
            'lower' => ['upper_and_lower_test_prod'],
        ];
    }

    /**
     * Returns GraphQl mutation for (addProductsToCart) adding item to cart
     *
     * @param string $maskedQuoteId
     * @param string $sku
     * @return string
     */
    private function getAddToCartMutation(string $maskedQuoteId, string $sku): string
    {
        return <<<MUTATION
mutation {
  addProductsToCart(
        cartId: "{$maskedQuoteId}",
        cartItems: [
            {
                sku: "{$sku}"
                quantity: 1
            }
        ]
    ) {
    cart {
      id
      items {
        uid
        quantity
        product {
          sku
          name
        }
      }
    }
    user_errors {
      code
      message
    }
  }
}
MUTATION;
    }
}
