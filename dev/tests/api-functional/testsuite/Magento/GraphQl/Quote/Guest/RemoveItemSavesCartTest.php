<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 *
 * Test for PR #40248: Call RemoveItem on cart instead of ItemRepository
 * Validates that removing items properly saves cart state to database
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteItemIdByReservedQuoteIdAndSku;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test that removeItemFromCart mutation saves cart state to database
 *
 * This test validates the fix for PR #40248 where cart state wasn't being
 * persisted after item removal, causing is_virtual status to remain stale.
 *
 * @magentoAppArea graphql
 */
class RemoveItemSavesCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GetQuoteItemIdByReservedQuoteIdAndSku
     */
    private $getQuoteItemIdByReservedQuoteIdAndSku;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getQuoteItemIdByReservedQuoteIdAndSku = $objectManager->get(
            GetQuoteItemIdByReservedQuoteIdAndSku::class
        );
    }

    /**
     * Test that removing item saves cart state - validates PR #40248 fix
     *
     * Before Fix: Cart state (including is_virtual) wasn't saved after removeItem()
     * After Fix: cartRepository->save() ensures cart state persists to database
     *
     * Test Scenario:
     * 1. Create cart with both physical (simple_product) and virtual products
     * 2. Verify cart.is_virtual = false (because physical item exists)
     * 3. Remove the physical product via removeItemFromCart mutation
     * 4. Re-query cart and verify is_virtual = true (cart was saved to database)
     *
     * The key validation is step 4 - the re-query proves the cart was actually
     * saved to the database, not just modified in memory.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_virtual_product.php
     */
    public function testRemoveItemSavesCartState()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $simpleProductItemId = $this->getQuoteItemIdByReservedQuoteIdAndSku->execute(
            'test_quote',
            'simple_product'
        );

        // Step 1: Verify initial cart state (has both physical and virtual items)
        $initialCartQuery = $this->getCartQuery($maskedQuoteId);
        $initialResponse = $this->graphQlQuery($initialCartQuery);

        $this->assertArrayHasKey('cart', $initialResponse);
        $this->assertArrayHasKey('is_virtual', $initialResponse['cart']);
        $this->assertFalse(
            $initialResponse['cart']['is_virtual'],
            'Cart should not be virtual when it contains physical items'
        );
        $this->assertCount(
            2,
            $initialResponse['cart']['items'],
            'Cart should have 2 items initially'
        );

        // Step 2: Remove physical item using removeItemFromCart mutation
        $removeItemMutation = $this->getRemoveItemMutation($maskedQuoteId, $simpleProductItemId);
        $removeResponse = $this->graphQlMutation($removeItemMutation);

        $this->assertArrayHasKey('removeItemFromCart', $removeResponse);
        $this->assertArrayHasKey('cart', $removeResponse['removeItemFromCart']);

        // Step 3: CRITICAL - Re-query cart to verify state was persisted to database
        // This proves that cartRepository->save() was called (the PR fix)
        $finalCartQuery = $this->getCartQuery($maskedQuoteId);
        $finalResponse = $this->graphQlQuery($finalCartQuery);

        $this->assertArrayHasKey('cart', $finalResponse);
        $this->assertArrayHasKey('is_virtual', $finalResponse['cart']);
        
        // This assertion validates the PR fix - is_virtual updated because cart was saved
        $this->assertTrue(
            $finalResponse['cart']['is_virtual'],
            'Cart should be virtual after removing all physical items. ' .
            'This proves cartRepository->save() was called (PR #40248 fix).'
        );
        
        $this->assertCount(
            1,
            $finalResponse['cart']['items'],
            'Cart should have 1 virtual item remaining'
        );

        // Additional validation - verify the remaining item is indeed virtual
        $remainingItem = $finalResponse['cart']['items'][0];
        $this->assertEquals(
            'virtual-product',
            $remainingItem['product']['sku'],
            'Remaining item should be the virtual product'
        );
    }

    /**
     * Get cart query with is_virtual and items
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedQuoteId}") {
    is_virtual
    total_quantity
    items {
      id
      quantity
      product {
        name
        sku
        type_id
      }
    }
  }
}
QUERY;
    }

    /**
     * Get removeItemFromCart mutation
     *
     * @param string $maskedQuoteId
     * @param int $itemId
     * @return string
     */
    private function getRemoveItemMutation(string $maskedQuoteId, int $itemId): string
    {
        return <<<MUTATION
mutation {
  removeItemFromCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_item_id: {$itemId}
    }
  ) {
    cart {
      is_virtual
      total_quantity
      items {
        id
        quantity
        product {
          sku
        }
      }
    }
  }
}
MUTATION;
    }
}
