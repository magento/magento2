<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Wishlist
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist model
 *
 * @method \Magento\Wishlist\Model\Resource\Wishlist _getResource()
 * @method \Magento\Wishlist\Model\Resource\Wishlist getResource()
 * @method int getShared()
 * @method \Magento\Wishlist\Model\Wishlist setShared(int $value)
 * @method string getSharingCode()
 * @method \Magento\Wishlist\Model\Wishlist setSharingCode(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Wishlist\Model\Wishlist setUpdatedAt(string $value)
 *
 * @category    Magento
 * @package     Magento_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model;

class Wishlist extends \Magento\Core\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wishlist';
    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\Resource\Item\Collection
     */
    protected $_itemCollection;

    /**
     * Store filter for wishlist
     *
     * @var \Magento\Core\Model\Store
     */
    protected $_store;

    /**
     * Shared store ids (website stores)
     *
     * @var array
     */
    protected $_storeIds;

    /**
     * Wishlist data
     *
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistData;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Date
     */
    protected $_date;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $_wishlistItemFactory;

    /**
     * @var \Magento\Wishlist\Model\Resource\Item\CollectionFactory
     */
    protected $_wishlistCollFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Wishlist\Helper\Data $wishlistData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Wishlist\Model\Resource\Wishlist $resource
     * @param \Magento\Wishlist\Model\Resource\Wishlist\Collection $resourceCollection
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Date $date
     * @param \Magento\Wishlist\Model\ItemFactory $wishlistItemFactory
     * @param \Magento\Wishlist\Model\Resource\Item\CollectionFactory $wishlistCollFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Wishlist\Helper\Data $wishlistData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Wishlist\Model\Resource\Wishlist $resource,
        \Magento\Wishlist\Model\Resource\Wishlist\Collection $resourceCollection,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Date $date,
        \Magento\Wishlist\Model\ItemFactory $wishlistItemFactory,
        \Magento\Wishlist\Model\Resource\Item\CollectionFactory $wishlistCollFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_catalogProduct = $catalogProduct;
        $this->_coreData = $coreData;
        $this->_wishlistData = $wishlistData;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_wishlistItemFactory = $wishlistItemFactory;
        $this->_wishlistCollFactory = $wishlistCollFactory;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Load wishlist by customer
     *
     * @param mixed $customer
     * @param bool $create Create wishlist if don't exists
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function loadByCustomer($customer, $create = false)
    {
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $customer = $customer->getId();
        }

        $customer = (int) $customer;
        $customerIdFieldName = $this->_getResource()->getCustomerIdFieldName();
        $this->_getResource()->load($this, $customer, $customerIdFieldName);
        if (!$this->getId() && $create) {
            $this->setCustomerId($customer);
            $this->setSharingCode($this->_getSharingRandomCode());
            $this->save();
        }

        return $this;
    }

    /**
     * Retrieve wishlist name
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->_getData('name');
        if (!strlen($name)) {
            return $this->_wishlistData->getDefaultWishlistName();
        }
        return $name;
    }

    /**
     * Set random sharing code
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function generateSharingCode()
    {
        $this->setSharingCode($this->_getSharingRandomCode());
        return $this;
    }

    /**
     * Load by sharing code
     *
     * @param string $code
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function loadByCode($code)
    {
        $this->_getResource()->load($this, $code, 'sharing_code');
        if (!$this->getShared()) {
            $this->setId(null);
        }

        return $this;
    }

    /**
     * Retrieve sharing code (random string)
     *
     * @return string
     */
    protected function _getSharingRandomCode()
    {
        return $this->_coreData->uniqHash();
    }

    /**
     * Set date of last update for wishlist
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->setUpdatedAt($this->_date->gmtDate());
        return $this;
    }

    /**
     * Save related items
     *
     * @return \Magento\Sales\Model\Quote
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if (null !== $this->_itemCollection) {
            $this->getItemCollection()->save();
        }
        return $this;
    }

    /**
     * Add catalog product object data to wishlist
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   int $qty
     * @param   bool $forciblySetQty
     *
     * @return  \Magento\Wishlist\Model\Item
     */
    protected function _addCatalogProduct(\Magento\Catalog\Model\Product $product, $qty = 1, $forciblySetQty = false)
    {
        $item = null;
        foreach ($this->getItemCollection() as $_item) {
            if ($_item->representProduct($product)) {
                $item = $_item;
                break;
            }
        }

        if ($item === null) {
            $storeId = $product->hasWishlistStoreId() ? $product->getWishlistStoreId() : $this->getStore()->getId();
            $item = $this->_wishlistItemFactory->create();
            $item->setProductId($product->getId())
                ->setWishlistId($this->getId())
                ->setAddedAt(now())
                ->setStoreId($storeId)
                ->setOptions($product->getCustomOptions())
                ->setProduct($product)
                ->setQty($qty)
                ->save();
            if ($item->getId()) {
                $this->getItemCollection()->addItem($item);
            }
        } else {
            $qty = $forciblySetQty ? $qty : $item->getQty() + $qty;
            $item->setQty($qty)
                ->save();
        }

        $this->addItem($item);

        return $item;
    }

    /**
     * Retrieve wishlist item collection
     *
     * @return \Magento\Wishlist\Model\Resource\Item\Collection
     */
    public function getItemCollection()
    {
        if (is_null($this->_itemCollection)) {
            /** @var $currentWebsiteOnly boolean */
            $currentWebsiteOnly = !$this->_storeManager->getStore()->isAdmin();
            $this->_itemCollection = $this->_wishlistCollFactory->create()
                ->addWishlistFilter($this)
                ->addStoreFilter($this->getSharedStoreIds($currentWebsiteOnly))
                ->setVisibilityFilter();
        }

        return $this->_itemCollection;
    }

    /**
     * Retrieve wishlist item collection
     *
     * @param int $itemId
     * @return \Magento\Wishlist\Model\Item
     */
    public function getItem($itemId)
    {
        if (!$itemId) {
            return false;
        }
        return $this->getItemCollection()->getItemById($itemId);
    }

    /**
     * Adding item to wishlist
     *
     * @param   \Magento\Wishlist\Model\Item $item
     * @return  \Magento\Wishlist\Model\Wishlist
     */
    public function addItem(\Magento\Wishlist\Model\Item $item)
    {
        $item->setWishlist($this);
        if (!$item->getId()) {
            $this->getItemCollection()->addItem($item);
            $this->_eventManager->dispatch('wishlist_add_item', array('item' => $item));
        }
        return $this;
    }

    /**
     * Adds new product to wishlist.
     * Returns new item or string on error.
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @param mixed $buyRequest
     * @param bool $forciblySetQty
     * @return \Magento\Wishlist\Model\Item|string
     */
    public function addNewItem($product, $buyRequest = null, $forciblySetQty = false)
    {
        /*
         * Always load product, to ensure:
         * a) we have new instance and do not interfere with other products in wishlist
         * b) product has full set of attributes
         */
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $productId = $product->getId();
            // Maybe force some store by wishlist internal properties
            $storeId = $product->hasWishlistStoreId() ? $product->getWishlistStoreId() : $product->getStoreId();
        } else {
            $productId = (int) $product;
            if ($buyRequest->getStoreId()) {
                $storeId = $buyRequest->getStoreId();
            } else {
                $storeId = $this->_storeManager->getStore()->getId();
            }
        }

        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this->_productFactory->create()
            ->setStoreId($storeId)
            ->load($productId);

        if ($buyRequest instanceof \Magento\Object) {
            $_buyRequest = $buyRequest;
        } elseif (is_string($buyRequest)) {
            $_buyRequest = new \Magento\Object(unserialize($buyRequest));
        } elseif (is_array($buyRequest)) {
            $_buyRequest = new \Magento\Object($buyRequest);
        } else {
            $_buyRequest = new \Magento\Object();
        }

        $cartCandidates = $product->getTypeInstance()
            ->processConfiguration($_buyRequest, $product);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $errors = array();
        $items = array();

        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }
            $candidate->setWishlistStoreId($storeId);

            $qty = $candidate->getQty() ? $candidate->getQty() : 1; // No null values as qty. Convert zero to 1.
            $item = $this->_addCatalogProduct($candidate, $qty, $forciblySetQty);
            $items[] = $item;

            // Collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }

        $this->_eventManager->dispatch('wishlist_product_add_after', array('items' => $items));

        return $item;
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function setCustomerId($customerId)
    {
        return $this->setData($this->_getResource()->getCustomerIdFieldName(), $customerId);
    }

    /**
     * Retrieve customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData($this->_getResource()->getCustomerIdFieldName());
    }

    /**
     * Retrieve data for save
     *
     * @return array
     */
    public function getDataForSave()
    {
        $data = array();
        $data[$this->_getResource()->getCustomerIdFieldName()] = $this->getCustomerId();
        $data['shared']      = (int) $this->getShared();
        $data['sharing_code']= $this->getSharingCode();
        return $data;
    }

    /**
     * Retrieve shared store ids for current website or all stores if $current is false
     *
     * @param bool $current Use current website or not
     * @return array
     */
    public function getSharedStoreIds($current = true)
    {
        if (is_null($this->_storeIds) || !is_array($this->_storeIds)) {
            if ($current) {
                $this->_storeIds = $this->getStore()->getWebsite()->getStoreIds();
            } else {
                $_storeIds = array();
                $stores = $this->_storeManager->getStores();
                foreach ($stores as $store) {
                    $_storeIds[] = $store->getId();
                }
                $this->_storeIds = $_storeIds;
            }
        }
        return $this->_storeIds;
    }

    /**
     * Set shared store ids
     *
     * @param array $storeIds
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function setSharedStoreIds($storeIds)
    {
        $this->_storeIds = (array) $storeIds;
        return $this;
    }

    /**
     * Retrieve wishlist store object
     *
     * @return \Magento\Core\Model\Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->setStore($this->_storeManager->getStore());
        }
        return $this->_store;
    }

    /**
     * Set wishlist store
     *
     * @param \Magento\Core\Model\Store $store
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve wishlist items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getItemCollection()->getSize();
    }

    /**
     * Retrieve wishlist has salable item(s)
     *
     * @return bool
     */
    public function isSalable()
    {
        foreach ($this->getItemCollection() as $item) {
            if ($item->getProduct()->getIsSalable()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check customer is owner this wishlist
     *
     * @param int $customerId
     * @return bool
     */
    public function isOwner($customerId)
    {
        return $customerId == $this->getCustomerId();
    }


    /**
     * Update wishlist Item and set data from request
     *
     * $params sets how current item configuration must be taken into account and additional options.
     * It's passed to \Magento\Catalog\Helper\Product->addParamsToBuyRequest() to compose resulting buyRequest.
     *
     * Basically it can hold
     * - 'current_config', \Magento\Object or array - current buyRequest that configures product in this item,
     *   used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file options (file inputs),
     * so they won't intersect with other submitted options
     *
     * For more options see \Magento\Catalog\Helper\Product->addParamsToBuyRequest()
     *
     * @param int|\Magento\Wishlist\Model\Item $itemId
     * @param \Magento\Object $buyRequest
     * @param null|array|\Magento\Object $params
     * @return \Magento\Wishlist\Model\Wishlist
     *
     * @see \Magento\Catalog\Helper\Product::addParamsToBuyRequest()
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = null;
        if ($itemId instanceof \Magento\Wishlist\Model\Item) {
            $item = $itemId;
        } else {
            $item = $this->getItem((int)$itemId);
        }
        if (!$item) {
            throw new \Magento\Core\Exception(__('We can\'t specify a wish list item.'));
        }

        $product = $item->getProduct();
        $productId = $product->getId();
        if ($productId) {
            if (!$params) {
                $params = new \Magento\Object();
            } else if (is_array($params)) {
                $params = new \Magento\Object($params);
            }
            $params->setCurrentConfig($item->getBuyRequest());
            $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);

            $product->setWishlistStoreId($item->getStoreId());
            $items = $this->getItemCollection();
            $isForceSetQuantity = true;
            foreach ($items as $_item) {
                /* @var $_item \Magento\Wishlist\Model\Item */
                if ($_item->getProductId() == $product->getId()
                    && $_item->representProduct($product)
                    && $_item->getId() != $item->getId()) {
                    // We do not add new wishlist item, but updating the existing one
                    $isForceSetQuantity = false;
                }
            }
            $resultItem = $this->addNewItem($product, $buyRequest, $isForceSetQuantity);
            /**
             * Error message
             */
            if (is_string($resultItem)) {
                throw new \Magento\Core\Exception(__($resultItem));
            }

            if ($resultItem->getId() != $itemId) {
                if ($resultItem->getDescription() != $item->getDescription()) {
                    $resultItem->setDescription($item->getDescription())->save();
                }
                $item->isDeleted(true);
                $this->setDataChanges(true);
            } else {
                $resultItem->setQty($buyRequest->getQty() * 1);
                $resultItem->setOrigData('qty', 0);
            }
        } else {
            throw new \Magento\Core\Exception(__('The product does not exist.'));
        }
        return $this;
    }

    /**
     * Save wishlist.
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function save()
    {
        $this->_hasDataChanges = true;
        return parent::save();
    }
}
