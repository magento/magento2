<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config as WishlistConfig;
use Magento\Wishlist\Model\Wishlist\Data\Error;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItemFactory;
use Magento\Wishlist\Model\Wishlist\UpdateProductsInWishlist as UpdateProductsInWishlistModel;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;
use Magento\Framework\DataObject;
use Magento\Wishlist\Model\Wishlist\Data\WishlistOutput;
use Magento\Wishlist\Model\Wishlist\BuyRequest\BuyRequestBuilder;

/**
 * Update wishlist items resolver
 */
class UpdateProductsInWishlist implements ResolverInterface
{
    /**
     * @var UpdateProductsInWishlistModel
     */
    private $updateProductsInWishlist;

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
     * @var array
     */
    private $errors = [];

    /**
     * BuyRequestBuilder
     * @var BuyRequestBuilder $buyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @param WishlistResourceModel $wishlistResource
     * @param WishlistFactory $wishlistFactory
     * @param WishlistConfig $wishlistConfig
     * @param UpdateProductsInWishlistModel $updateProductsInWishlist
     * @param WishlistDataMapper $wishlistDataMapper
     * @param BuyRequestBuilder $buyRequestBuilder
     */
    public function __construct(
        WishlistResourceModel $wishlistResource,
        WishlistFactory $wishlistFactory,
        WishlistConfig $wishlistConfig,
        UpdateProductsInWishlistModel $updateProductsInWishlist,
        WishlistDataMapper $wishlistDataMapper,
        BuyRequestBuilder $buyRequestBuilder
    ) {
        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistConfig = $wishlistConfig;
        $this->updateProductsInWishlist = $updateProductsInWishlist;
        $this->wishlistDataMapper = $wishlistDataMapper;
        $this->buyRequestBuilder = $buyRequestBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$this->wishlistConfig->isEnabled()) {
            throw new GraphQlInputException(__('The wishlist configuration is currently disabled.'));
        }

        $customerId = $context->getUserId();

        /* Guest checking */
        if (null === $customerId || $customerId === 0) {
            throw new GraphQlAuthorizationException(__('The current user cannot perform operations on wishlist'));
        }

        $wishlistId = ((int) $args['wishlistId']) ?: null;
        $wishlist = $this->getWishlist($wishlistId, $customerId);
        if (null === $wishlist->getId() || $customerId !== (int) $wishlist->getCustomerId()) {
            throw new GraphQlInputException(__('The wishlist was not found.'));
        }

        $wishlistItems  = $args['wishlistItems'];
        $wishlistItems  = $this->getWishlistItems($wishlistItems);
        foreach ($wishlistItems as $wishlistItem) {
            $options = $this->buyRequestBuilder->build($wishlistItem);
            $wishlistOutput = $this->updateItem($wishlistItem->getId(), $options, $wishlist);
        }
        if (count($wishlistOutput->getErrors()) !== count($wishlistItems)) {
            $this->wishlistResource->save($wishlist);
        }

        return [
            'wishlist' => $this->wishlistDataMapper->map($wishlistOutput->getWishlist()),'user_errors' => \array_map(
                function (Error $error) {
                    return [
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                    ];
                },
                $wishlistOutput->getErrors()
            )
        ];
    }

    /**
     * Get DTO wishlist items
     *
     * @param array $wishlistItemsData
     *
     * @return array
     */
    private function getWishlistItems(array $wishlistItemsData): array
    {
        $wishlistItems = [];
        foreach ($wishlistItemsData as $wishlistItemData) {
            $wishlistItems[] = (new WishlistItemFactory())->create($wishlistItemData);
        }
        return $wishlistItems;
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

        if (null !== $wishlistId && 0 < $wishlistId) {
            $this->wishlistResource->load($wishlist, $wishlistId);
        } elseif ($customerId !== null) {
            $wishlist->loadByCustomerId($customerId, true);
        }

        return $wishlist;
    }

    /**
     * Update wishlist Item and set data from request
     *
     * @param int $itemId
     * @param DataObject $buyRequest
     * @param Wishlist $wishlist
     *
     * @return WishlistOutput
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateItem(int $itemId, DataObject $buyRequest, Wishlist $wishlist)
    {
        $item = $wishlist->getItem((int)$itemId);

        if (!$item) {
            throw new GraphQlInputException(__('We can\'t specify a wish list item.'));
        }

        $product = $item->getProduct();
        $productId = $product->getId();

        if ($productId) {
            $buyRequest->setData('action', 'updateItem');
            $product->setWishlistStoreId($item->getStoreId());
            $cartCandidates = $product->getTypeInstance()->processConfiguration($buyRequest, clone $product);

            /**
             * If the product with options existed or not
             */
            if (is_string($cartCandidates)) {
                throw new GraphQlInputException(__('The product with options does not exist.'));
            }

            /**
             * If prepare process return one object
             */
            if (!is_array($cartCandidates)) {
                $cartCandidates = [$cartCandidates];
            }

            foreach ($cartCandidates as $candidate) {
                if ($candidate->getParentProductId()) {
                    continue;
                }
                $candidate->setWishlistStoreId($item->getStoreId());
                $qty = $buyRequest->getData('qty') ? $buyRequest->getData('qty') : 1;
                $item->setOptions($candidate->getCustomOptions());
                $item->setQty($qty);
            }
            $this->wishlistResource->save($wishlist);
        } else {
            throw new GraphQlInputException(__('The product does not exist.'));
        }
        return $this->prepareOutput($wishlist);
    }

    /**
     * Prepare output
     *
     * @param Wishlist $wishlist
     *
     * @return WishlistOutput
     */
    private function prepareOutput(Wishlist $wishlist): WishlistOutput
    {
        $output = new WishlistOutput($wishlist, $this->errors);
        $this->errors = [];

        return $output;
    }
}
