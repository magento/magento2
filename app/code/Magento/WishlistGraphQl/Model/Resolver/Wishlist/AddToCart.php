<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types = 1);

namespace Magento\WishlistGraphQl\Model\Resolver\Wishlist;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\Quote\Model\Cart\Data\Error;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;
use Magento\WishlistGraphQl\Model\CartItems\CartItemsRequestBuilder;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemsCollection;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;

/**
 * Adding products to wishlist resolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToCart implements ResolverInterface
{
    /**
     * @var WishlistDataMapper
     */
    private $wishlistDataMapper;

    /**
     * @var WishlistConfig
     */
    private $wishlistConfig;

    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var CreateEmptyCartForCustomer
     */
    private $createEmptyCartForCustomer;

    /**
     * @var AddProductsToCartService
     */
    private $addProductsToCartService;

    /**
     * @var CartItemsRequestBuilder
     */
    private $cartItemsRequestBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @param WishlistResourceModel $wishlistResource
     * @param WishlistFactory $wishlistFactory
     * @param WishlistConfig $wishlistConfig
     * @param WishlistDataMapper $wishlistDataMapper
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param AddProductsToCartService $addProductsToCart
     * @param CartItemsRequestBuilder $cartItemsRequestBuilder
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        WishlistResourceModel $wishlistResource,
        WishlistFactory $wishlistFactory,
        WishlistConfig $wishlistConfig,
        WishlistDataMapper $wishlistDataMapper,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        AddProductsToCartService $addProductsToCart,
        CartItemsRequestBuilder $cartItemsRequestBuilder,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
    ) {
        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistConfig = $wishlistConfig;
        $this->wishlistDataMapper = $wishlistDataMapper;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->addProductsToCartService = $addProductsToCart;
        $this->cartItemsRequestBuilder = $cartItemsRequestBuilder;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$this->wishlistConfig->isEnabled()) {
            throw new GraphQlInputException(__('The wishlist configuration is currently disabled.'));
        }

        $customerId = $context->getUserId();

        /* Guest checking */
        if (null === $customerId || 0 === $customerId) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }

        if (empty($args['wishlistId'])) {
            throw new GraphQlInputException(__('"wishlistId" value should be specified'));
        }
        $wishlistId = (int) $args['wishlistId'];
        $wishlist = $this->getWishlist($wishlistId, $customerId);
        $isOwner = $wishlist->isOwner($customerId);

        if (null === $wishlist->getId() || $customerId !== (int) $wishlist->getCustomerId()) {
            throw new GraphQlInputException(__('The wishlist was not found.'));
        }

        $itemIds = [];
        if (isset($args['wishlistItemIds'])) {
            $itemIds = $args['wishlistItemIds'];
        }

        $collection = $this->getWishlistItems($wishlist, $itemIds);

        if (!empty($itemIds)) {
            $unknownItemIds = array_diff($itemIds, array_keys($collection->getItems()));
            if (!empty($unknownItemIds)) {
                throw new GraphQlInputException(
                    __('The wishlist item ids "'.implode(',', $unknownItemIds).'" were not found.')
                );
            }
        }
        $maskedCartId = $this->createEmptyCartForCustomer->execute($customerId);

        $cartErrors = [];
        $addedProducts = [];
        $errors = [];
        foreach ($collection as $item) {
            $disableAddToCart = $item->getProduct()->getDisableAddToCart();
            $item->getProduct()->setDisableAddToCart($disableAddToCart);

            $cartItemData = $this->cartItemsRequestBuilder->build($item);
            $cartItem = (new CartItemFactory())->create($cartItemData);

            /** @var AddProductsToCartOutput $addProductsToCartOutput */
            $addProductsToCartOutput = $this->addProductsToCartService->execute($maskedCartId, [$cartItem]);
            $errors = array_map(
                function (Error $error) use ($item, $wishlist) {
                    return [
                        'wishlistItemId' =>  $item->getID(),
                        'wishlistId' => $wishlist->getId(),
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                    ];
                },
                $addProductsToCartOutput->getErrors()
            );
            if ($isOwner && empty($errors)) {
                $item->delete();
                $addedProducts[] = $item->getProductId();
            }
            if (!empty($errors)) {
                $this->saveCart($maskedCartId);
            }
            $cartErrors = [...$cartErrors, ...$errors];
        }
        if (!empty($addedProducts)) {
            $wishlist->save();
        }
        return [
            'wishlist' => $this->wishlistDataMapper->map($wishlist),
            'status' => empty($cartErrors) ? true : false,
            'add_wishlist_items_to_cart_user_errors' => $cartErrors,
        ];
    }

    /**
     * Get customer wishlist
     *
     * @param int|null $wishlistId
     * @param int|null $customerId
     *
     * @return Wishlist
     */
    private function getWishlist(?int $wishlistId, ?int $customerId): Wishlist
    {
        $wishlist = $this->wishlistFactory->create();

        if ($wishlistId !== null && $wishlistId > 0) {
            $this->wishlistResource->load($wishlist, $wishlistId);
        } elseif ($customerId !== null) {
            $wishlist->loadByCustomerId($customerId, true);
        }

        return $wishlist;
    }

    /**
     * Get customer wishlist items
     *
     * @param Wishlist $wishlist
     * @param array $itemIds
     *
     * @return WishlistItemsCollection
     * @throws NoSuchEntityException
     */
    private function getWishlistItems(Wishlist $wishlist, array $itemIds): WishlistItemsCollection
    {
        if (!empty($itemIds)) {
            $collection = $wishlist->getItemCollection()->addFieldToFilter('wishlist_item_id', $itemIds)
                ->setVisibilityFilter();
        } else {
            $collection = $wishlist->getItemCollection()->setVisibilityFilter();
        }
        return $collection;
    }

    /**
     * Save cart on error while adding wishlist product to cart
     *
     * @param string $maskedCartId
     * @return void
     * @throws GraphQlInputException
     */
    private function saveCart(string $maskedCartId): void
    {
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
            $cart = $this->cartRepository->get($cartId);
            $this->cartRepository->save($cart);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('The wishlist could not be saved.'));
        }
    }
}
