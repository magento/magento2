<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model;

use Exception;
use InvalidArgumentException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as ResourceWishlist;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection;

/**
 * Wishlist model
 *
 * @method int getShared()
 * @method Wishlist setShared(int $value)
 * @method string getSharingCode()
 * @method Wishlist setSharingCode(string $value)
 * @method string getUpdatedAt()
 * @method Wishlist setUpdatedAt(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @api
 * @since 100.0.2
 */
class Wishlist extends AbstractModel implements IdentityInterface
{
    /**
     * Wishlist cache tag name
     */
    const CACHE_TAG = 'wishlist';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wishlist';

    /**
     * Wishlist item collection
     *
     * @var ResourceModel\Item\Collection
     */
    protected $_itemCollection;

    /**
     * Store filter for wishlist
     *
     * @var Store
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
     * @var Data
     */
    protected $_wishlistData;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime\DateTime
     */
    protected $_date;

    /**
     * @var ItemFactory
     */
    protected $_wishlistItemFactory;

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var bool
     */
    protected $_useCurrentWebsite;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StockRegistryInterface|null
     */
    private $stockRegistry;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param Data $wishlistData
     * @param ResourceWishlist $resource
     * @param Collection $resourceCollection
     * @param StoreManagerInterface $storeManager
     * @param DateTime\DateTime $date
     * @param ItemFactory $wishlistItemFactory
     * @param CollectionFactory $wishlistCollectionFactory
     * @param ProductFactory $productFactory
     * @param Random $mathRandom
     * @param DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     * @param bool $useCurrentWebsite
     * @param array $data
     * @param Json|null $serializer
     * @param StockRegistryInterface|null $stockRegistry
     * @param ScopeConfigInterface|null $scopeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Catalog\Helper\Product $catalogProduct,
        Data $wishlistData,
        ResourceWishlist $resource,
        Collection $resourceCollection,
        StoreManagerInterface $storeManager,
        DateTime\DateTime $date,
        ItemFactory $wishlistItemFactory,
        CollectionFactory $wishlistCollectionFactory,
        ProductFactory $productFactory,
        Random $mathRandom,
        DateTime $dateTime,
        ProductRepositoryInterface $productRepository,
        $useCurrentWebsite = true,
        array $data = [],
        Json $serializer = null,
        StockRegistryInterface $stockRegistry = null,
        ScopeConfigInterface $scopeConfig = null
    ) {
        $this->_useCurrentWebsite = $useCurrentWebsite;
        $this->_catalogProduct = $catalogProduct;
        $this->_wishlistData = $wishlistData;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_wishlistItemFactory = $wishlistItemFactory;
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->mathRandom = $mathRandom;
        $this->dateTime = $dateTime;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->stockRegistry = $stockRegistry ?: ObjectManager::getInstance()->get(StockRegistryInterface::class);
    }

    /**
     * Load wishlist by customer id
     *
     * @param int $customerId
     * @param bool $create Create wishlist if don't exists
     * @return $this
     */
    public function loadByCustomerId($customerId, $create = false)
    {
        if ($customerId === null) {
            return $this;
        }
        $customerId = (int)$customerId;
        $customerIdFieldName = $this->_getResource()->getCustomerIdFieldName();
        $this->_getResource()->load($this, $customerId, $customerIdFieldName);
        if (!$this->getId() && $create) {
            $this->setCustomerId($customerId);
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
        if ($name === null || !strlen($name)) {
            return $this->_wishlistData->getDefaultWishlistName();
        }
        return $name;
    }

    /**
     * Set random sharing code
     *
     * @return $this
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
     * @return $this
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
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * Set date of last update for wishlist
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->setUpdatedAt($this->_date->gmtDate());
        return $this;
    }

    /**
     * Save related items
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();

        if (null !== $this->_itemCollection) {
            $this->getItemCollection()->save();
        }
        return $this;
    }

    /**
     * Add catalog product object data to wishlist
     *
     * @param   Product $product
     * @param   int $qty
     * @param   bool $forciblySetQty
     *
     * @return  Item
     */
    protected function _addCatalogProduct(Product $product, $qty = 1, $forciblySetQty = false)
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
            $item->setProductId($product->getId());
            $item->setWishlistId($this->getId());
            $item->setAddedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
            $item->setStoreId($storeId);
            $item->setOptions($product->getCustomOptions());
            $item->setProduct($product);
            $item->setQty($qty);
            $item->save();
            if ($item->getId()) {
                $this->getItemCollection()->addItem($item);
            }
        } else {
            $qty = $forciblySetQty ? $qty : $item->getQty() + $qty;
            $item->setQty($qty)->save();
        }

        $this->addItem($item);

        return $item;
    }

    /**
     * Retrieve wishlist item collection
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     * @throws NoSuchEntityException
     */
    public function getItemCollection()
    {
        if ($this->_itemCollection === null) {
            $this->_itemCollection = $this->_wishlistCollectionFactory->create()->addWishlistFilter(
                $this
            )->addStoreFilter(
                $this->getSharedStoreIds()
            )->setVisibilityFilter($this->_useCurrentWebsite);
        }

        return $this->_itemCollection;
    }

    /**
     * Retrieve wishlist item collection
     *
     * @param int $itemId
     * @return false|Item
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
     * @param Item $item
     * @return $this
     * @throws Exception
     */
    public function addItem(Item $item)
    {
        $item->setWishlist($this);
        if (!$item->getId()) {
            $this->getItemCollection()->addItem($item);
            $this->_eventManager->dispatch('wishlist_add_item', ['item' => $item]);
        }
        return $this;
    }

    /**
     * Adds new product to wishlist.
     *
     * Returns new item or string on error.
     *
     * @param int|Product $product
     * @param DataObject|array|string|null $buyRequest
     * @param bool $forciblySetQty
     * @return Item|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws LocalizedException
     * @throws InvalidArgumentException
     */
    public function addNewItem($product, $buyRequest = null, $forciblySetQty = false)
    {
        /*
         * Always load product, to ensure:
         * a) we have new instance and do not interfere with other products in wishlist
         * b) product has full set of attributes
         */
        if ($product instanceof Product) {
            $productId = $product->getId();
            // Maybe force some store by wishlist internal properties
            $storeId = $product->hasWishlistStoreId() ? $product->getWishlistStoreId() : $product->getStoreId();
        } else {
            $productId = (int)$product;
            if (isset($buyRequest) && $buyRequest->getStoreId()) {
                $storeId = $buyRequest->getStoreId();
            } else {
                $storeId = $this->_storeManager->getStore()->getId();
            }
        }

        try {
            /** @var Product $product */
            $product = $this->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Cannot specify product.'));
        }

        if ($this->isInStock($productId)) {
            throw new LocalizedException(__('Cannot add product without stock to wishlist.'));
        }

        if ($buyRequest instanceof DataObject) {
            $_buyRequest = $buyRequest;
        } elseif (is_string($buyRequest)) {
            $isInvalidItemConfiguration = false;
            try {
                $buyRequestData = $this->serializer->unserialize($buyRequest);
                if (!is_array($buyRequestData)) {
                    $isInvalidItemConfiguration = true;
                }
            } catch (Exception $exception) {
                $isInvalidItemConfiguration = true;
            }
            if ($isInvalidItemConfiguration) {
                throw new InvalidArgumentException('Invalid wishlist item configuration.');
            }
            $_buyRequest = new DataObject($buyRequestData);
        } elseif (is_array($buyRequest)) {
            $_buyRequest = new DataObject($buyRequest);
        } else {
            $_buyRequest = new DataObject();
        }

        /* @var $product Product */
        $cartCandidates = $product->getTypeInstance()->processConfiguration($_buyRequest, clone $product);

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
            $cartCandidates = [$cartCandidates];
        }

        $errors = [];
        $items = [];

        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }
            $candidate->setWishlistStoreId($storeId);

            $qty = $candidate->getQty() ? $candidate->getQty() : 1;
            // No null values as qty. Convert zero to 1.
            $item = $this->_addCatalogProduct($candidate, $qty, $forciblySetQty);
            $items[] = $item;

            // Collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }

        $this->_eventManager->dispatch('wishlist_product_add_after', ['items' => $items]);

        return $item;
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     * @throws LocalizedException
     */
    public function setCustomerId($customerId)
    {
        return $this->setData($this->_getResource()->getCustomerIdFieldName(), $customerId);
    }

    /**
     * Retrieve customer id
     *
     * @return int
     * @throws LocalizedException
     */
    public function getCustomerId()
    {
        return $this->getData($this->_getResource()->getCustomerIdFieldName());
    }

    /**
     * Retrieve data for save
     *
     * @return array
     * @throws LocalizedException
     */
    public function getDataForSave()
    {
        $data = [];
        $data[$this->_getResource()->getCustomerIdFieldName()] = $this->getCustomerId();
        $data['shared'] = (int)$this->getShared();
        $data['sharing_code'] = $this->getSharingCode();
        return $data;
    }

    /**
     * Retrieve shared store ids for current website or all stores if $current is false
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSharedStoreIds()
    {
        if ($this->_storeIds === null || !is_array($this->_storeIds)) {
            if ($this->_useCurrentWebsite) {
                $this->_storeIds = $this->getStore()->getWebsite()->getStoreIds();
            } else {
                $_storeIds = [];
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
     * @return $this
     */
    public function setSharedStoreIds($storeIds)
    {
        $this->_storeIds = (array)$storeIds;
        return $this;
    }

    /**
     * Retrieve wishlist store object
     *
     * @return \Magento\Store\Model\Store
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        if ($this->_store === null) {
            $this->setStore($this->_storeManager->getStore());
        }
        return $this->_store;
    }

    /**
     * Set wishlist store
     *
     * @param Store $store
     * @return $this
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
        return $this->getItemCollection()->count();
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
     * Retrieve if product has stock or config is set for showing out of stock products
     *
     * @param int $productId
     * @return bool
     */
    private function isInStock($productId)
    {
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($productId);
        $showOutOfStock = $this->scopeConfig->isSetFlag(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE
        );
        $isInStock = $stockItem ? $stockItem->getIsInStock() : false;
        return !$isInStock && !$showOutOfStock;
    }

    /**
     * Check customer is owner this wishlist
     *
     * @param int $customerId
     * @return bool
     * @throws LocalizedException
     */
    public function isOwner($customerId)
    {
        return $customerId == $this->getCustomerId();
    }

    /**
     * Update wishlist Item and set data from request
     *
     * The $params sets how current item configuration must be taken into account and additional options.
     * It's passed to \Magento\Catalog\Helper\Product->addParamsToBuyRequest() to compose resulting buyRequest.
     *
     * Basically it can hold
     * - 'current_config', \Magento\Framework\DataObject or array - current buyRequest
     *   that configures product in this item, used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file options (file inputs),
     * so they won't intersect with other submitted options
     *
     * For more options see \Magento\Catalog\Helper\Product->addParamsToBuyRequest()
     *
     * @param int|Item $itemId
     * @param DataObject $buyRequest
     * @param null|array|DataObject $params
     * @return $this
     * @throws LocalizedException
     *
     * @see \Magento\Catalog\Helper\Product::addParamsToBuyRequest()
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = null;
        if ($itemId instanceof Item) {
            $item = $itemId;
            $itemId = $item->getId();
        } else {
            $item = $this->getItem((int)$itemId);
        }
        if (!$item) {
            throw new LocalizedException(__('We can\'t specify a wish list item.'));
        }

        $product = $item->getProduct();
        $productId = $product->getId();
        if ($productId) {
            if (!$params) {
                $params = new DataObject();
            } elseif (is_array($params)) {
                $params = new DataObject($params);
            }
            $params->setCurrentConfig($item->getBuyRequest());
            $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);

            $product->setWishlistStoreId($item->getStoreId());
            $items = $this->getItemCollection();
            $isForceSetQuantity = true;
            foreach ($items as $_item) {
                /* @var $_item Item */
                if ($_item->getProductId() == $product->getId() && $_item->representProduct(
                    $product
                ) && $_item->getId() != $item->getId()
                ) {
                    // We do not add new wishlist item, but updating the existing one
                    $isForceSetQuantity = false;
                }
            }
            $resultItem = $this->addNewItem($product, $buyRequest, $isForceSetQuantity);
            /**
             * Error message
             */
            if (is_string($resultItem)) {
                throw new LocalizedException(__($resultItem));
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
            throw new LocalizedException(__('The product does not exist.'));
        }
        return $this;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getId()) {
            $identities = [self::CACHE_TAG . '_' . $this->getId()];
        }
        return $identities;
    }
}
