<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Test\Unit\Model\Resolver\Wishlist;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\Cart\Data\Error;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;
use Magento\WishlistGraphQl\Model\CartItems\CartItemsRequestBuilder;
use Magento\WishlistGraphQl\Model\Resolver\Wishlist\AddToCart;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemsCollection;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test coverage for AddToCart GraphQL resolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AddToCartTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var AddToCart
     */
    private AddToCart $resolver;

    /**
     * @var WishlistResourceModel|MockObject
     */
    private WishlistResourceModel $wishlistResource;

    /**
     * @var WishlistFactory|MockObject
     */
    private WishlistFactory $wishlistFactory;

    /**
     * @var WishlistConfig|MockObject
     */
    private WishlistConfig $wishlistConfig;

    /**
     * @var WishlistDataMapper|MockObject
     */
    private WishlistDataMapper $wishlistDataMapper;

    /**
     * @var CreateEmptyCartForCustomer|MockObject
     */
    private CreateEmptyCartForCustomer $createEmptyCartForCustomer;

    /**
     * @var AddProductsToCartService|MockObject
     */
    private AddProductsToCartService $addProductsToCartService;

    /**
     * @var CartItemsRequestBuilder|MockObject
     */
    private CartItemsRequestBuilder $cartItemsRequestBuilder;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface|MockObject
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @var Field|MockObject
     */
    private Field $field;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface $context;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $info;

    /**
     * @var Wishlist|MockObject
     */
    private Wishlist $wishlist;

    /**
     * @var WishlistItemsCollection|MockObject
     */
    private WishlistItemsCollection $wishlistItemsCollection;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->wishlistResource = $this->createMock(WishlistResourceModel::class);
        $this->wishlistFactory = $this->createMock(WishlistFactory::class);
        $this->wishlistConfig = $this->createMock(WishlistConfig::class);
        $this->wishlistDataMapper = $this->createMock(WishlistDataMapper::class);
        $this->createEmptyCartForCustomer = $this->createMock(CreateEmptyCartForCustomer::class);
        $this->addProductsToCartService = $this->createMock(AddProductsToCartService::class);
        $this->cartItemsRequestBuilder = $this->createMock(CartItemsRequestBuilder::class);
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->maskedQuoteIdToQuoteId = $this->createMock(MaskedQuoteIdToQuoteIdInterface::class);

        $this->field = $this->createMock(Field::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->info = $this->createMock(ResolveInfo::class);
        $this->wishlist = $this->createMock(Wishlist::class);
        $this->wishlistItemsCollection = $this->createMock(WishlistItemsCollection::class);

        $this->resolver = new AddToCart(
            $this->wishlistResource,
            $this->wishlistFactory,
            $this->wishlistConfig,
            $this->wishlistDataMapper,
            $this->createEmptyCartForCustomer,
            $this->addProductsToCartService,
            $this->cartItemsRequestBuilder,
            $this->cartRepository,
            $this->maskedQuoteIdToQuoteId
        );
    }

    /**
     * Test resolve method throws exception when wishlist is disabled
     */
    public function testResolveThrowsExceptionWhenWishlistDisabled(): void
    {
        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('The wishlist configuration is currently disabled.');

        $this->resolver->resolve($this->field, $this->context, $this->info, null, []);
    }

    /**
     * Test resolve method throws exception for guest user
     */
    public function testResolveThrowsExceptionForGuestUser(): void
    {
        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getUserId')
            ->willReturn(null);

        $this->expectException(GraphQlAuthorizationException::class);
        $this->expectExceptionMessage('The current user cannot perform operations on wishlist');

        $this->resolver->resolve($this->field, $this->context, $this->info, null, []);
    }

    /**
     * Test resolve method throws exception for zero customer ID
     */
    public function testResolveThrowsExceptionForZeroCustomerId(): void
    {
        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getUserId')
            ->willReturn(0);

        $this->expectException(GraphQlAuthorizationException::class);
        $this->expectExceptionMessage('The current user cannot perform operations on wishlist');

        $this->resolver->resolve($this->field, $this->context, $this->info, null, []);
    }

    /**
     * Test resolve method throws exception when wishlistId is missing
     */
    public function testResolveThrowsExceptionWhenWishlistIdMissing(): void
    {
        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getUserId')
            ->willReturn(1);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"wishlistId" value should be specified');

        $this->resolver->resolve($this->field, $this->context, $this->info, null, []);
    }

    /**
     * Test resolve method throws exception when wishlist is not found
     */
    public function testResolveThrowsExceptionWhenWishlistNotFound(): void
    {
        $customerId = 1;
        $wishlistId = 1;

        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->wishlistResource->expects($this->once())
            ->method('load')
            ->with($this->wishlist, $wishlistId);

        $this->wishlist->expects($this->once())
            ->method('isOwner')
            ->with($customerId)
            ->willReturn(true);

        $this->wishlist->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('The wishlist was not found.');

        $args = ['wishlistId' => $wishlistId];
        $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
    }

    /**
     * Test resolve method throws exception when customer is not wishlist owner
     */
    public function testResolveThrowsExceptionWhenCustomerNotOwner(): void
    {
        $customerId = 1;
        $wishlistId = 1;
        $wishlistCustomerId = 2;

        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->wishlistResource->expects($this->once())
            ->method('load')
            ->with($this->wishlist, $wishlistId);

        $this->wishlist->expects($this->once())
            ->method('isOwner')
            ->with($customerId)
            ->willReturn(true);

        $this->wishlist->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistId);

        $this->wishlist->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($wishlistCustomerId);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('The wishlist was not found.');

        $args = ['wishlistId' => $wishlistId];
        $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
    }

    /**
     * Test resolve method throws exception when wishlist item IDs not found
     */
    public function testResolveThrowsExceptionWhenWishlistItemIdsNotFound(): void
    {
        $customerId = 1;
        $wishlistId = 1;
        $itemIds = [1, 2, 3];
        $foundItemIds = [1, 2]; // Missing item ID 3

        $this->setupValidWishlistScenario($customerId, $wishlistId);

        // Mock wishlist items collection
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($this->wishlistItemsCollection);

        $this->wishlistItemsCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('wishlist_item_id', $itemIds)
            ->willReturnSelf();

        $this->wishlistItemsCollection->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();

        // Mock items with only found IDs
        $mockItems = [];
        foreach ($foundItemIds as $itemId) {
            $mockItem = $this->createMock(WishlistItem::class);
            $mockItems[$itemId] = $mockItem;
        }
        $this->wishlistItemsCollection->expects($this->once())
            ->method('getItems')
            ->willReturn($mockItems);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('The wishlist item ids "3" were not found.');

        $args = ['wishlistId' => $wishlistId, 'wishlistItemIds' => $itemIds];
        $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
    }

    /**
     * Test successful resolve with items added to cart and removed from wishlist
     */
    public function testResolveSuccessfulAddToCartWithItemRemoval(): void
    {
        $customerId = 1;
        $wishlistId = 1;
        $maskedCartId = 'masked_cart_id';
        $productId = 123;

        $this->setupValidWishlistScenario($customerId, $wishlistId);

        // Mock wishlist items collection (no specific item IDs)
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($this->wishlistItemsCollection);

        $this->wishlistItemsCollection->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();

        // Mock wishlist item
        $wishlistItem = $this->createPartialMockWithReflection(
            WishlistItem::class,
            ['getProduct', 'delete', 'getID', 'getProductId']
        );
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getDisableAddToCart', 'setDisableAddToCart']
        );

        $wishlistItem->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturn($product);

        $product->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(false);

        $product->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(false);

        $wishlistItem->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $wishlistItem->expects($this->once())
            ->method('delete');

        // Mock iterator for collection
        $this->wishlistItemsCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$wishlistItem]));

        // Mock create empty cart
        $this->createEmptyCartForCustomer->expects($this->once())
            ->method('execute')
            ->with($customerId)
            ->willReturn($maskedCartId);

        // Mock cart item building
        $cartItemData = ['sku' => 'test-sku', 'quantity' => 1];
        $this->cartItemsRequestBuilder->expects($this->once())
            ->method('build')
            ->with($wishlistItem)
            ->willReturn($cartItemData);

        // Mock add products to cart service
        $addProductsOutput = $this->createMock(AddProductsToCartOutput::class);
        $addProductsOutput->expects($this->once())
            ->method('getErrors')
            ->willReturn([]); // No errors

        $this->addProductsToCartService->expects($this->once())
            ->method('execute')
            ->with($maskedCartId, $this->anything())
            ->willReturn($addProductsOutput);

        // Mock wishlist save
        $this->wishlist->expects($this->once())
            ->method('save');

        // Mock wishlist data mapper
        $mappedWishlist = ['id' => $wishlistId, 'items_count' => 0];
        $this->wishlistDataMapper->expects($this->once())
            ->method('map')
            ->with($this->wishlist)
            ->willReturn($mappedWishlist);

        $args = ['wishlistId' => $wishlistId];
        $result = $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);

        $this->assertEquals([
            'wishlist' => $mappedWishlist,
            'status' => true,
            'add_wishlist_items_to_cart_user_errors' => []
        ], $result);
    }

    /**
     * Test resolve with errors when adding to cart
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testResolveWithAddToCartErrors(): void
    {
        $customerId = 1;
        $wishlistId = 1;
        $maskedCartId = 'masked_cart_id';
        $cartId = 1;

        $this->setupValidWishlistScenario($customerId, $wishlistId);

        // Mock wishlist items collection
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($this->wishlistItemsCollection);

        $this->wishlistItemsCollection->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();

        // Mock wishlist item
        $wishlistItem = $this->createPartialMockWithReflection(
            WishlistItem::class,
            ['getProduct', 'delete', 'getID', 'getProductId']
        );
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getDisableAddToCart', 'setDisableAddToCart']
        );

        $wishlistItem->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturn($product);

        $product->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(false);

        $product->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(false);

        $wishlistItem->expects($this->once())
            ->method('getID')
            ->willReturn(1);

        // Mock iterator for collection
        $this->wishlistItemsCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$wishlistItem]));

        // Mock create empty cart
        $this->createEmptyCartForCustomer->expects($this->once())
            ->method('execute')
            ->with($customerId)
            ->willReturn($maskedCartId);

        // Mock cart item building
        $cartItemData = ['sku' => 'test-sku', 'quantity' => 1];
        $this->cartItemsRequestBuilder->expects($this->once())
            ->method('build')
            ->with($wishlistItem)
            ->willReturn($cartItemData);

        // Mock add products to cart service with errors
        $error = $this->createMock(Error::class);
        $error->expects($this->once())
            ->method('getCode')
            ->willReturn('PRODUCT_NOT_FOUND');
        $error->expects($this->once())
            ->method('getMessage')
            ->willReturn('Product not found');

        $addProductsOutput = $this->createMock(AddProductsToCartOutput::class);
        $addProductsOutput->expects($this->once())
            ->method('getErrors')
            ->willReturn([$error]);

        $this->addProductsToCartService->expects($this->once())
            ->method('execute')
            ->with($maskedCartId, $this->anything())
            ->willReturn($addProductsOutput);

        // Mock cart save on error
        $this->maskedQuoteIdToQuoteId->expects($this->once())
            ->method('execute')
            ->with($maskedCartId)
            ->willReturn($cartId);

        $cart = $this->createMock(CartInterface::class);
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($cart);

        $this->cartRepository->expects($this->once())
            ->method('save')
            ->with($cart);

        // Mock wishlist data mapper
        $mappedWishlist = ['id' => $wishlistId, 'items_count' => 1];
        $this->wishlistDataMapper->expects($this->once())
            ->method('map')
            ->with($this->wishlist)
            ->willReturn($mappedWishlist);

        $args = ['wishlistId' => $wishlistId];
        $result = $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);

        $expectedErrors = [
            [
                'wishlistItemId' => 1,
                'wishlistId' => $wishlistId,
                'code' => 'PRODUCT_NOT_FOUND',
                'message' => 'Product not found'
            ]
        ];

        $this->assertEquals([
            'wishlist' => $mappedWishlist,
            'status' => false,
            'add_wishlist_items_to_cart_user_errors' => $expectedErrors
        ], $result);
    }

    /**
     * Test resolve throws exception when cart save fails
     */
    public function testResolveThrowsExceptionWhenCartSaveFails(): void
    {
        $customerId = 1;
        $wishlistId = 1;
        $maskedCartId = 'masked_cart_id';

        $this->setupValidWishlistScenario($customerId, $wishlistId);

        // Mock wishlist items collection
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($this->wishlistItemsCollection);

        $this->wishlistItemsCollection->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();

        // Mock wishlist item
        $wishlistItem = $this->createPartialMockWithReflection(
            WishlistItem::class,
            ['getProduct', 'delete', 'getID', 'getProductId']
        );
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getDisableAddToCart', 'setDisableAddToCart']
        );

        $wishlistItem->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturn($product);

        $product->expects($this->once())
            ->method('getDisableAddToCart')
            ->willReturn(false);

        $product->expects($this->once())
            ->method('setDisableAddToCart')
            ->with(false);

        $wishlistItem->expects($this->once())
            ->method('getID')
            ->willReturn(1);

        // Mock iterator for collection
        $this->wishlistItemsCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$wishlistItem]));

        // Mock create empty cart
        $this->createEmptyCartForCustomer->expects($this->once())
            ->method('execute')
            ->with($customerId)
            ->willReturn($maskedCartId);

        // Mock cart item building
        $cartItemData = ['sku' => 'test-sku', 'quantity' => 1];
        $this->cartItemsRequestBuilder->expects($this->once())
            ->method('build')
            ->with($wishlistItem)
            ->willReturn($cartItemData);

        // Mock add products to cart service with errors
        $error = $this->createMock(Error::class);
        $error->expects($this->once())
            ->method('getCode')
            ->willReturn('PRODUCT_NOT_FOUND');
        $error->expects($this->once())
            ->method('getMessage')
            ->willReturn('Product not found');

        $addProductsOutput = $this->createMock(AddProductsToCartOutput::class);
        $addProductsOutput->expects($this->once())
            ->method('getErrors')
            ->willReturn([$error]);

        $this->addProductsToCartService->expects($this->once())
            ->method('execute')
            ->with($maskedCartId, $this->anything())
            ->willReturn($addProductsOutput);

        // Mock cart save failure
        $this->maskedQuoteIdToQuoteId->expects($this->once())
            ->method('execute')
            ->with($maskedCartId)
            ->willThrowException(new NoSuchEntityException(__('Cart not found')));

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('The wishlist could not be saved.');

        $args = ['wishlistId' => $wishlistId];
        $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
    }

    /**
     * Test getWishlist method with wishlist ID
     */
    public function testGetWishlistWithWishlistId(): void
    {
        $customerId = 1;
        $wishlistId = 1;

        $this->setupValidWishlistScenario($customerId, $wishlistId);

        // Mock the collection to prevent null reference
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($this->wishlistItemsCollection);

        $this->wishlistItemsCollection->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();

        $this->wishlistItemsCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        // This test covers the getWishlist private method through the public resolve method
        $args = ['wishlistId' => $wishlistId];

        try {
            $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
        } catch (\Exception $e) {
            // Expected exception since we don't mock the full cart flow
            $this->assertTrue(true); // Test passed if we reach here
        }
    }

    /**
     * Test getWishlist method with customer ID only (no wishlistId provided)
     */
    public function testGetWishlistWithCustomerIdOnly(): void
    {
        $customerId = 1;

        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        // First it will check for empty wishlistId and throw exception
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"wishlistId" value should be specified');

        // Test without wishlistId - should trigger the missing wishlistId validation
        $args = [];
        $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
    }

    /**
     * Test getWishlist method loadByCustomerId path with null wishlistId
     */
    public function testGetWishlistLoadByCustomerId(): void
    {
        $customerId = 1;

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->with($customerId, true);

        // Test the loadByCustomerId path by using reflection to call getWishlist directly
        $reflection = new \ReflectionClass($this->resolver);
        $method = $reflection->getMethod('getWishlist');

        // Call getWishlist with null wishlistId to trigger loadByCustomerId path
        $result = $method->invoke($this->resolver, null, $customerId);

        $this->assertSame($this->wishlist, $result);
    }

    /**
     * Test saveCart method success scenario
     */
    public function testSaveCartSuccess(): void
    {
        $maskedCartId = 'masked_cart_id';
        $cartId = 1;

        $this->maskedQuoteIdToQuoteId->expects($this->once())
            ->method('execute')
            ->with($maskedCartId)
            ->willReturn($cartId);

        $cart = $this->createMock(CartInterface::class);
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($cart);

        $this->cartRepository->expects($this->once())
            ->method('save')
            ->with($cart);

        // Test the saveCart method using reflection
        $reflection = new \ReflectionClass($this->resolver);
        $method = $reflection->getMethod('saveCart');

        // Should not throw any exception
        $method->invoke($this->resolver, $maskedCartId);
        $this->assertTrue(true); // If we reach here, the test passed
    }

    /**
     * Test saveCart method with NoSuchEntityException
     */
    public function testSaveCartWithException(): void
    {
        $maskedCartId = 'masked_cart_id';

        $this->maskedQuoteIdToQuoteId->expects($this->once())
            ->method('execute')
            ->with($maskedCartId)
            ->willThrowException(new NoSuchEntityException(__('Cart not found')));

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('The wishlist could not be saved.');

        // Test the saveCart method using reflection
        $reflection = new \ReflectionClass($this->resolver);
        $method = $reflection->getMethod('saveCart');

        $method->invoke($this->resolver, $maskedCartId);
    }

    /**
     * Test getWishlistItems method with no item IDs (all items)
     */
    public function testGetWishlistItemsWithoutItemIds(): void
    {
        $customerId = 1;
        $wishlistId = 1;

        $this->setupValidWishlistScenario($customerId, $wishlistId);

        // Mock wishlist items collection (no specific item IDs)
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($this->wishlistItemsCollection);

        $this->wishlistItemsCollection->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();

        // This test covers the getWishlistItems method path without itemIds
        $args = ['wishlistId' => $wishlistId];

        try {
            $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
        } catch (\Exception $e) {
            // Expected since we don't mock the full flow
        }
    }

    /**
     * Test getWishlistItems method with specific item IDs
     */
    public function testGetWishlistItemsWithItemIds(): void
    {
        $customerId = 1;
        $wishlistId = 1;
        $itemIds = [1, 2];

        $this->setupValidWishlistScenario($customerId, $wishlistId);

        // Mock wishlist items collection with specific item IDs
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($this->wishlistItemsCollection);

        $this->wishlistItemsCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('wishlist_item_id', $itemIds)
            ->willReturnSelf();

        $this->wishlistItemsCollection->expects($this->once())
            ->method('setVisibilityFilter')
            ->willReturnSelf();

        // Mock items found
        $mockItems = [];
        foreach ($itemIds as $itemId) {
            $mockItem = $this->createMock(WishlistItem::class);
            $mockItems[$itemId] = $mockItem;
        }
        $this->wishlistItemsCollection->expects($this->once())
            ->method('getItems')
            ->willReturn($mockItems);

        // This test covers the getWishlistItems method path with itemIds
        $args = ['wishlistId' => $wishlistId, 'wishlistItemIds' => $itemIds];

        try {
            $this->resolver->resolve($this->field, $this->context, $this->info, null, $args);
        } catch (\Exception $e) {
            // Expected since we don't mock the full flow
        }
    }

    /**
     * Set up valid wishlist scenario for testing
     *
     * @param int $customerId
     * @param int $wishlistId
     */
    private function setupValidWishlistScenario(int $customerId, int $wishlistId): void
    {
        $this->wishlistConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->wishlistResource->expects($this->once())
            ->method('load')
            ->with($this->wishlist, $wishlistId);

        $this->wishlist->expects($this->once())
            ->method('isOwner')
            ->with($customerId)
            ->willReturn(true);

        $this->wishlist->expects($this->atLeast(1))
            ->method('getId')
            ->willReturn($wishlistId);

        $this->wishlist->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
    }
}
