<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteIdMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for GraphQL cart query when customizable option no longer exists on product
 */
class DeletedCustomOptionInCartTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Uid
     */
    private $uidEncoder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->optionRepository = $objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->uidEncoder = $objectManager->create(Uid::class);
    }

    /**
     * Test that cart query doesn't fail when selected customizable option no longer exists
     *
     * Steps to reproduce:
     * 1. Add a product to cart with customizable options
     * 2. Delete the customizable option from the product
     * 3. Query the cart using GraphQL
     * 4. Verify item is returned without the deleted option (should not return null for label)
     *
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple_with_custom_options',
                'options' => [
                    [
                        'title' => 'Test Option',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                        'is_require' => true,
                        'values' => [
                            [
                                'title' => 'Option Value 1',
                                'price' => 10.00,
                                'price_type' => 'fixed',
                            ],
                            [
                                'title' => 'Option Value 2',
                                'price' => 20.00,
                                'price_type' => 'fixed',
                            ]
                        ]
                    ]
                ]
            ],
            'product'
        ),
    ]
    public function testCartQueryWithDeletedCustomizableOption(): void
    {
        $productFixture = $this->fixtures->get('product');
        $sku = $productFixture->getSku();

        // Get the product with its customizable options
        $product = $this->productRepository->get($sku);
        $optionData = $this->getFirstOptionAndValue($product);

        // Get cart ID from fixture
        $quoteIdMask = $this->fixtures->get('quoteIdMask');
        $maskedQuoteId = $quoteIdMask->getMaskedId();

        // Step 1: Add product with customizable option to cart
        $this->addProductToCartWithOption(
            $maskedQuoteId,
            $sku,
            $optionData['optionId'],
            $optionData['optionValueId']
        );

        // Step 2: Delete the customizable option from the product
        $this->deleteProductOptionAndVerify($product, $sku);

        // Step 3 & 4: Query the cart and verify response
        $this->verifyCartQueryAfterOptionDeletion($maskedQuoteId, $sku);
    }

    /**
     * Add product with customizable option to cart and verify
     *
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $optionId
     * @param int $optionValueId
     * @return void
     * @throws \Exception
     */
    private function addProductToCartWithOption(
        string $maskedQuoteId,
        string $sku,
        int $optionId,
        int $optionValueId
    ): void {
        $mutation = $this->getAddProductToCartMutation(
            $maskedQuoteId,
            $sku,
            1,
            $optionId,
            $optionValueId
        );

        $response = $this->graphQlMutation($mutation);

        $this->assertArrayHasKey('addProductsToCart', $response);
        $this->assertArrayHasKey('cart', $response['addProductsToCart']);
        $cartItems = $response['addProductsToCart']['cart']['items'];
        $this->assertNotEmpty($cartItems, 'Cart should have items');
        $this->assertGreaterThan(0, count($cartItems), 'Cart should have at least one item');

        // Verify option was added
        $cartItem = $cartItems[0];
        $this->assertArrayHasKey('customizable_options', $cartItem);
        $customizableOptions = $cartItem['customizable_options'];
        $this->assertNotEmpty($customizableOptions, 'Cart item should have customizable options');
        $this->assertGreaterThan(0, count($customizableOptions), 'Should have at least one customizable option');

        $customizableOption = $customizableOptions[0];
        $this->assertArrayHasKey('label', $customizableOption);
        $this->assertNotNull($customizableOption['label'], 'Option label should not be null initially');
    }

    /**
     * Delete product option and verify deletion
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param string $sku
     * @return void
     * @throws NoSuchEntityException
     */
    private function deleteProductOptionAndVerify($product, string $sku): void
    {
        $firstOption = $product->getOptions()[0];
        $this->optionRepository->delete($firstOption);

        // Clear product cache to ensure changes are reflected
        $forceReload = true;
        $product = $this->productRepository->get($sku, false, null, $forceReload);
        $remainingOptions = $product->getOptions();
        $this->assertEmpty(
            $remainingOptions,
            'Product should have no customizable options after deletion'
        );
    }

    /**
     * Verify cart query works after option deletion
     *
     * @param string $maskedQuoteId
     * @param string $sku
     * @return void
     */
    private function verifyCartQueryAfterOptionDeletion(string $maskedQuoteId, string $sku): void
    {
        $cartQuery = $this->getCartQuery($maskedQuoteId);

        try {
            $cartResponse = $this->graphQlQuery($cartQuery);

            // Verify cart is returned successfully
            $this->assertArrayHasKey('cart', $cartResponse);
            $this->assertArrayHasKey('items', $cartResponse['cart']);
            $responseCartItems = $cartResponse['cart']['items'];
            $this->assertNotEmpty($responseCartItems, 'Cart should still have items');
            $this->assertGreaterThan(0, count($responseCartItems), 'Cart should have at least one item');

            // Verify item is returned
            $responseCartItem = $responseCartItems[0];
            $this->assertArrayHasKey('product', $responseCartItem);
            $this->assertEquals($sku, $responseCartItem['product']['sku']);

            // Expected behavior: customizable_options should either be:
            // 1. Empty array (deleted options are not returned)
            // 2. Or the field is present with no null labels
            if (isset($responseCartItem['customizable_options'])) {
                foreach ($responseCartItem['customizable_options'] as $option) {
                    // Option labels should not be null
                    if (isset($option['label'])) {
                        $this->assertNotNull(
                            $option['label'],
                            'Option label should not be null in cart response'
                        );
                    }
                }
            }

            // Verify cart is accessible after option deletion
            $this->assertCount(
                1,
                $responseCartItems,
                'Cart should still contain the product after option deletion'
            );

        } catch (GraphQlInputException $e) {
            // This is the current bug - cart query fails with null label error
            if (strpos($e->getMessage(), 'Cannot return null for non-nullable field') !== false) {
                $this->fail(
                    'BUG CONFIRMED: Cart query fails when customizable option is deleted. ' .
                    'GraphQL returns null for non-nullable field "SelectedCustomizableOption.label". ' .
                    'Error: ' . $e->getMessage()
                );
            }
            // Re-throw if it's a different GraphQL error
            throw $e;
        } catch (\Exception $e) {
            // Unexpected error
            $this->fail(
                'Unexpected error occurred during cart query. ' .
                'Error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get option ID and value ID from product with proper validation
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array{optionId: int, optionValueId: int}
     */
    private function getFirstOptionAndValue($product): array
    {
        $options = $product->getOptions();
        $this->assertNotEmpty($options, 'Product should have customizable options');

        $firstOption = reset($options);
        $this->assertNotFalse($firstOption, 'Product should have at least one option');

        $optionId = $firstOption->getOptionId();
        $this->assertNotNull($optionId, 'Option ID should not be null');

        $optionValues = $firstOption->getValues();
        $this->assertNotEmpty($optionValues, 'Option should have values');

        $firstValue = reset($optionValues);
        $this->assertNotFalse($firstValue, 'Option should have at least one value');

        $optionValueId = $firstValue->getOptionTypeId();
        $this->assertNotNull($optionValueId, 'Option value ID should not be null');

        return [
            'optionId' => (int) $optionId,
            'optionValueId' => (int) $optionValueId
        ];
    }

    /**
     * Get mutation to add product with customizable options to cart
     *
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $quantity
     * @param int $optionId
     * @param int $optionValueId
     * @return string
     */
    private function getAddProductToCartMutation(
        string $maskedQuoteId,
        string $sku,
        int $quantity,
        int $optionId,
        int $optionValueId
    ): string {
        return <<<MUTATION
mutation {
    addProductsToCart(
        cartId: "{$maskedQuoteId}"
        cartItems: [
            {
                quantity: {$quantity}
                sku: "{$sku}"
                selected_options: [
                    "{$this->encodeSelectedOption($optionId, $optionValueId)}"
                ]
            }
        ]
    ) {
        cart {
            items {
                quantity
                product {
                    sku
                }
                ... on SimpleCartItem {
                    customizable_options {
                        label
                        values {
                            label
                            value
                        }
                    }
                }
            }
        }
    }
}
MUTATION;
    }

    /**
     * Get cart query
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
query {
    cart(cart_id: "{$maskedQuoteId}") {
        items {
            quantity
            product {
                sku
                name
            }
            ... on SimpleCartItem {
                customizable_options {
                    label
                    values {
                        label
                        value
                    }
                }
            }
        }
    }
}
QUERY;
    }

    /**
     * Encode selected option UID for GraphQL
     *
     * @param int $optionId
     * @param int $valueId
     * @return string
     */
    private function encodeSelectedOption(int $optionId, int $valueId): string
    {
        return $this->uidEncoder->encode('custom-option' . '/' . $optionId . '/' . $valueId);
    }
}
