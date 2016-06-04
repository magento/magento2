<?php
/**
 * Contributor company: iPragmatech solution Pvt Ltd.
 * Contributor Author : Manish Kumar
 * Date: 23/5/16
 * Time: 11:55 AM
 */

namespace Magento\Wishlist\Model;

use Magento\Wishlist\Api\WishlistManagementInterface;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Defines the implementaiton class of the WishlistManagementInterface
 */
class WishlistManagement implements WishlistManagementInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_itemCollection;

    /**
     * @var WishlistRepository
     */
    protected $_wishlistRepository;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;
    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;
    /**
     * @var Item
     */
    protected $_itemFactory;


    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_wishlistRepository = $wishlistRepository;
        $this->_productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_itemFactory = $itemFactory;
    }

    /**
     * Get wishlist collection
     * @deprecated
     * @param $customerId
     * @return WishlistData
     */
    public function getWishlistForCustomer($customerId)
    {
        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new InputException(__('Id required'));
        } else {
            $collection =
                $this->_wishlistCollectionFactory->create()
                    ->addCustomerIdFilter($customerId);

            $wishlistData = [];
            foreach ($collection as $item) {
                $productInfo = $item->getProduct()->toArray();
                $data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id"      => $item->getWishlistId(),
                    "product_id"       => $item->getProductId(),
                    "store_id"         => $item->getStoreId(),
                    "added_at"         => $item->getAddedAt(),
                    "description"      => $item->getDescription(),
                    "qty"              => round($item->getQty()),
                    "product"          => $productInfo
                ];
                $wishlistData[] = $data;
            }
            return $wishlistData;
        }
    }

    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addWishlistForCustomer($customerId, $productId)
    {
        if ($productId == null) {
            throw new LocalizedException(__
            ('Invalid product, Please select a valid product'));
        }
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        try {
            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId
            ($customerId, true);
            $wishlist->addNewItem($product);
            $returnData = $wishlist->save();
        } catch (NoSuchEntityException $e) {

        }
        return true;
    }

    /**
     * Delete wishlist item for customer
     * @param int $customerId
     * @param int $productIdId
     * @return bool|\Magento\Wishlist\Api\status
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteWishlistForCustomer($customerId, $wishlistItemId)
    {

        if ($wishlistItemId == null) {
            throw new LocalizedException(__
            ('Invalid wishlist item, Please select a valid item'));
        }
        $item = $this->_itemFactory->create()->load($wishlistItemId);
        if (!$item->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List Item doesn\'t exist.')
            );
        }
        $wishlistId = $item->getWishlistId();
        $wishlist = $this->_wishlistFactory->create();

        if ($wishlistId) {
            $wishlist->load($wishlistId);
        } elseif ($customerId) {
            $wishlist->loadByCustomerId($customerId, true);
        }
        if (!$wishlist) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List doesn\'t exist.')
            );
        }
        if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List doesn\'t exist.')
            );
        }
        try {
            $item->delete();
            $wishlist->save();
        } catch (\Exception $e) {

        }
        return true;
    }
}