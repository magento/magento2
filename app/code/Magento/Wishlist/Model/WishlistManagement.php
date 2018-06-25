<?php

namespace Magento\Wishlist\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Wishlist\Api\WishlistManagementInterface;

class WishlistManagement implements WishlistManagementInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * WishlistRepository constructor.
     * @param ProductRepository $productRepository
     * @param WishlistFactory $wishlistFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        WishlistFactory $wishlistFactory
    ) {
        $this->productRepository = $productRepository;
        $this->wishlistFactory = $wishlistFactory;
    }


    /**
     * @inheritdoc
     */
    public function getWishlistForCustomer($customerId)
    {
        /** @var \Magento\Wishlist\Model\ResourceModel\Wishlist $resourceModel */
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, false);
        if (!$wishlist->getId()) {
            throw new NoSuchEntityException(__('No wishlist for customer.'));
        }

        return $wishlist;
    }

    /**
     * @inheritdoc
     */
    public function addWishlistForCustomer($customerId, $productId)
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);

        $product = $this->productRepository->getById($productId);

        try {
            $item = $wishlist->addNewItem($product);
            return $item->getId();

        } catch (LocalizedException $exception) {
            throw new StateException(__('Product with id: ' . $productId . ' already attached to wishlist'));
        }

    }
}
