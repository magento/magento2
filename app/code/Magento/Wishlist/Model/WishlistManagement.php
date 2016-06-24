<?php
/**
 * Contributor company: iPragmatech solution Pvt Ltd.
 * Contributor Author : Manish Kumar
 * Date: 23/5/16
 * Time: 11:55 AM
 */

namespace Magento\Wishlist\Model;

use Exception;
use Magento\Wishlist\Api\WishlistManagementInterface;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Defines the implementaiton class of the \Magento\Wishlist\Api\WishlistManagementInterface
 */
class WishlistManagement implements
    \Magento\Wishlist\Api\WishlistManagementInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
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
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_itemFactory = $itemFactory;
    }

    /**
     * Get wishlist collection
     * @param int $customerId
     * @return array WishlistData
     */
    public function getWishlistForCustomer($customerId)
    {
        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            $message = __('Id required');
            $status = false;
            $response[] = [
                "message" => $message,
                "status"  => $status
            ];
            return $response;
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
     *
     */
    public function addWishlistForCustomer($customerId, $productId)
    {
        if ($productId == null) {
            $message = __('Invalid product, Please select a valid product');
            $status = false;
            $response[] = [
                "message" => $message,
                "status"  => $status
            ];
            return $response;
        }
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (Exception $e) {
            return false;
        }
        try {
            $wishlist = $this->_wishlistFactory->create()
                ->loadByCustomerId($customerId, true);
            $wishlist->addNewItem($product);
            $wishlist->save();
        } catch (Exception $e) {
            return false;
        }
        $message = __('Item added to wishlist.');
        $status = true;
        $response[] = [
            "message" => $message,
            "status"  => $status
        ];
        return $response;
    }

    /**
     * Delete wishlist item for customer
     * @param int $customerId
     * @param int $productIdId
     * @return array
     *
     */
    public function deleteWishlistForCustomer($customerId, $wishlistItemId)
    {

        $message = null;
        $status = null;
        if ($wishlistItemId == null) {
            $message = __('Invalid wishlist item, Please select a valid item');
            $status = false;
            $response[] = [
                "message" => $message,
                "status"  => $status
            ];
            return $response;
        }
        $item = $this->_itemFactory->create()->load($wishlistItemId);
        if (!$item->getId()) {
            $message = __('The requested Wish List Item doesn\'t exist .');
            $status = false;

            $response[] = [
                "message" => $message,
                "status"  => $status
            ];
            return $response;
        }
        $wishlistId = $item->getWishlistId();
        $wishlist = $this->_wishlistFactory->create();

        if ($wishlistId) {
            $wishlist->load($wishlistId);
        } elseif ($customerId) {
            $wishlist->loadByCustomerId($customerId, true);
        }
        if (!$wishlist) {
            $message = __('The requested Wish List Item doesn\'t exist .');
            $status = false;
            $response[] = [
                "message" => $message,
                "status"  => $status
            ];
            return $response;
        }
        if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
            $message = __('The requested Wish List Item doesn\'t exist .');
            $status = false;
            $response[] = [
                "message" => $message,
                "status"  => $status
            ];
            return $response;
        }
        try {
            $item->delete();
            $wishlist->save();
        } catch (Exception $e) {
            return false;
        }

        $message = __(' Item has been removed from wishlist .');
        $status = true;
        $response[] = [
            "message" => $message,
            "status"  => $status
        ];
        return $response;
    }
}
