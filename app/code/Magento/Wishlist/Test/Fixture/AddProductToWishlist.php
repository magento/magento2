<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\ResourceModel\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class AddProductToWishlist implements DataFixtureInterface
{
    /**
     * @var WishlistFactory
     */
    private WishlistFactory $wishlistFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Wishlist
     */
    private Wishlist $wishlistResource;

    /**
     * @param WishlistFactory $wishlistFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Wishlist $wishlistResource
     */
    public function __construct(
        WishlistFactory $wishlistFactory,
        ProductRepositoryInterface $productRepository,
        Wishlist $wishlistResource,
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->productRepository = $productRepository;
        $this->wishlistResource = $wishlistResource;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'customer_id' => (int) Customer ID. Required.
     *      'product_ids'  => (array) Product IDs. Optional. Default: [].
     *      'name'        => (string) name. Optional. Default: 'Wish List'.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $name = $data['name'] ?? 'Wish List';
        $wishlist = $this->wishlistFactory->create();
        $wishlist->setCustomerId($data['customer_id'])
            ->setName($name)
            ->setVisibility(1);
        $this->wishlistResource->save($wishlist);
        if (isset($data['product_ids'])) {
            foreach ($data['product_ids'] as $productId) {
                $product = $this->productRepository->getById($productId);
                $wishlist->addNewItem($product);
            }
        }
        if (is_string($wishlist)) {
            throw new LocalizedException(__($wishlist));
        }
        return $wishlist;
    }
}
