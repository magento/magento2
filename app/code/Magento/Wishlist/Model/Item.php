<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Wishlist\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory;
use Magento\Catalog\Model\Product\Exception as ProductException;

/**
 * Wishlist item model
 *
 * @method \Magento\Wishlist\Model\ResourceModel\Item getResource()
 * @method int getWishlistId()
 * @method \Magento\Wishlist\Model\Item setWishlistId(int $value)
 * @method int getProductId()
 * @method \Magento\Wishlist\Model\Item setProductId(int $value)
 * @method int getStoreId()
 * @method \Magento\Wishlist\Model\Item setStoreId(int $value)
 * @method string getAddedAt()
 * @method \Magento\Wishlist\Model\Item setAddedAt(string $value)
 * @method string getDescription()
 * @method \Magento\Wishlist\Model\Item setDescription(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Item extends AbstractModel implements ItemInterface
{
    /**
     * Custom path to download attached file
     * @var string
     */
    protected $_customOptionDownloadUrl = 'wishlist/index/downloadCustomOption';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wishlist_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Item options array
     *
     * @var Option[]
     */
    protected $_options = [];

    /**
     * Item options by code cache
     *
     * @var array
     */
    protected $_optionsByCode = [];

    /**
     * Not Represent options
     *
     * @var string[]
     */
    protected $_notRepresentOptions = ['info_buyRequest'];

    /**
     * Flag stating that options were successfully saved
     *
     * @var bool|null
     */
    protected $_flagOptionsSaved = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $_catalogUrl;

    /**
     * @var OptionFactory
     */
    protected $_wishlistOptFactory;

    /**
     * @var CollectionFactory
     */
    protected $_wishlOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param OptionFactory $wishlistOptFactory
     * @param CollectionFactory $wishlOptionCollectionFactory
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        OptionFactory $wishlistOptFactory,
        CollectionFactory $wishlOptionCollectionFactory,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_catalogUrl = $catalogUrl;
        $this->_wishlistOptFactory = $wishlistOptFactory;
        $this->_wishlOptionCollectionFactory = $wishlOptionCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Wishlist\Model\ResourceModel\Item');
    }

    /**
     * Set quantity. If quantity is less than 0 - set it to 1
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->setData('qty', $qty >= 0 ? $qty : 1);
        return $this;
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Check if two options array are identical
     *
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    protected function _compareOptions($options1, $options2)
    {
        $skipOptions = ['id', 'qty', 'return_url'];
        foreach ($options1 as $code => $value) {
            if (in_array($code, $skipOptions)) {
                continue;
            }
            if (!isset($options2[$code]) || $options2[$code] != $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Register option code
     *
     * @param   Option $option
     * @return  $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addOptionCode($option)
    {
        if (!isset($this->_optionsByCode[$option->getCode()])) {
            $this->_optionsByCode[$option->getCode()] = $option;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('An item option with code %1 already exists.', $option->getCode())
            );
        }
        return $this;
    }

    /**
     * Checks that item model has data changes.
     * Call save item options if model isn't need to save in DB
     *
     * @return boolean
     */
    protected function _hasModelChanged()
    {
        if (!$this->hasDataChanges()) {
            return false;
        }

        return $this->_getResource()->hasDataChanged($this);
    }

    /**
     * Save item options
     *
     * @return $this
     */
    public function saveItemOptions()
    {
        foreach ($this->_options as $index => $option) {
            if ($option->isDeleted()) {
                $option->delete();
                unset($this->_options[$index]);
                unset($this->_optionsByCode[$option->getCode()]);
            } else {
                $option->save();
            }
        }

        $this->_flagOptionsSaved = true;
        // Report to watchers that options were saved

        return $this;
    }

    /**
     * Mark option save requirement
     *
     * @param bool $flag
     * @return void
     */
    public function setIsOptionsSaved($flag)
    {
        $this->_flagOptionsSaved = $flag;
    }

    /**
     * Were options saved?
     *
     * @return bool
     */
    public function isOptionsSaved()
    {
        return $this->_flagOptionsSaved;
    }

    /**
     * Save item options after item saved
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->saveItemOptions();
        return parent::afterSave();
    }

    /**
     * Validate wish list item data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        if (!$this->getWishlistId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t specify a wish list.'));
        }
        if (!$this->getProductId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot specify product.'));
        }

        return true;
    }

    /**
     * Check required data
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();

        // validate required item data
        $this->validate();

        // set current store id if it is not defined
        if (is_null($this->getStoreId())) {
            $this->setStoreId($this->_storeManager->getStore()->getId());
        }

        // set current date if added at data is not defined
        if (is_null($this->getAddedAt())) {
            $this->setAddedAt($this->_date->gmtDate());
        }

        return $this;
    }

    /**
     * Load item by product, wishlist and shared stores
     *
     * @param int $wishlistId
     * @param int $productId
     * @param array $sharedStores
     * @return $this
     */
    public function loadByProductWishlist($wishlistId, $productId, $sharedStores)
    {
        $this->_getResource()->loadByProductWishlist($this, $wishlistId, $productId, $sharedStores);
        $this->_afterLoad();
        $this->setOrigData();

        return $this;
    }

    /**
     * Retrieve item product instance
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (is_null($product)) {
            if (!$this->getProductId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot specify product.'));
            }
            try {
                $product = $this->productRepository->getById($this->getProductId(), false, $this->getStoreId(), true);
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot specify product.'), $e);
            }
            $this->setData('product', $product);
        }

        /**
         * Reset product final price because it related to custom options
         */
        $product->setFinalPrice(null);
        $product->setCustomOptions($this->_optionsByCode);
        return $product;
    }

    /**
     * Add or Move item product to shopping cart
     *
     * Return true if product was successful added or exception with code
     * Return false for disabled or unvisible products
     *
     * @param \Magento\Checkout\Model\Cart $cart
     * @param bool $delete  delete the item after successful add to cart
     * @return bool
     * @throws \Magento\Catalog\Model\Product\Exception
     */
    public function addToCart(\Magento\Checkout\Model\Cart $cart, $delete = false)
    {
        $product = $this->getProduct();

        $storeId = $this->getStoreId();

        if ($product->getStatus() != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
            $urlData = $this->_catalogUrl->getRewriteByProductStore([$product->getId() => $storeId]);
            if (!isset($urlData[$product->getId()])) {
                return false;
            }
            $product->setUrlDataObject(new \Magento\Framework\DataObject($urlData));
            $visibility = $product->getUrlDataObject()->getVisibility();
            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new ProductException(__('Product is not salable.'));
        }

        $buyRequest = $this->getBuyRequest();

        $cart->addProduct($product, $buyRequest);
        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }

        if ($delete) {
            $this->delete();
        }

        return true;
    }

    /**
     * Retrieve Product View Page URL
     *
     * If product has required options add special key to URL
     *
     * @return string
     */
    public function getProductUrl()
    {
        $product = $this->getProduct();
        $query = [];

        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            $query['options'] = 'cart';
        }

        return $product->getUrlModel()->getUrl($product, ['_query' => $query]);
    }

    /**
     * Returns formatted buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @return \Magento\Framework\DataObject
     */
    public function getBuyRequest()
    {
        $option = $this->getOptionByCode('info_buyRequest');
        $initialData = $option ? unserialize($option->getValue()) : null;

        if ($initialData instanceof \Magento\Framework\DataObject) {
            $initialData = $initialData->getData();
        }

        $buyRequest = new \Magento\Framework\DataObject($initialData);
        $buyRequest->setOriginalQty($buyRequest->getQty())->setQty($this->getQty() * 1);
        return $buyRequest;
    }

    /**
     * Merge data to item info_buyRequest option
     *
     * @param array|\Magento\Framework\DataObject $buyRequest
     * @return $this
     */
    public function mergeBuyRequest($buyRequest)
    {
        if ($buyRequest instanceof \Magento\Framework\DataObject) {
            $buyRequest = $buyRequest->getData();
        }

        if (empty($buyRequest) || !is_array($buyRequest)) {
            return $this;
        }

        $oldBuyRequest = $this->getBuyRequest()->getData();
        $sBuyRequest = serialize($buyRequest + $oldBuyRequest);

        $option = $this->getOptionByCode('info_buyRequest');
        if ($option) {
            $option->setValue($sBuyRequest);
        } else {
            $this->addOption(['code' => 'info_buyRequest', 'value' => $sBuyRequest]);
        }

        return $this;
    }

    /**
     * Set buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @return $this
     */
    public function setBuyRequest($buyRequest)
    {
        $buyRequest->setId($this->getId());

        $_buyRequest = serialize($buyRequest->getData());
        $this->setData('buy_request', $_buyRequest);
        return $this;
    }

    /**
     * Check product representation in item
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   \Magento\Framework\DataObject $buyRequest
     * @return  bool
     */
    public function isRepresent($product, $buyRequest)
    {
        if ($this->getProductId() != $product->getId()) {
            return false;
        }

        $selfOptions = $this->getBuyRequest()->getData();

        if (empty($buyRequest) && !empty($selfOptions)) {
            return false;
        }
        if (empty($selfOptions) && !empty($buyRequest)) {
            if (!$product->isComposite()) {
                return true;
            } else {
                return false;
            }
        }

        $requestArray = $buyRequest->getData();

        if (!$this->_compareOptions($requestArray, $selfOptions)) {
            return false;
        }
        if (!$this->_compareOptions($selfOptions, $requestArray)) {
            return false;
        }
        return true;
    }

    /**
     * Check product representation in item
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if ($itemProduct->getId() != $product->getId()) {
            return false;
        }

        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        return true;
    }

    /**
     * Check if two options array are identical
     * First options array is prerogative
     * Second options array checked against first one
     *
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    public function compareOptions($options1, $options2)
    {
        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (in_array($code, $this->_notRepresentOptions)) {
                continue;
            }
            if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Initialize item options
     *
     * @param   array $options
     * @return  $this
     */
    public function setOptions($options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
        return $this;
    }

    /**
     * Get all item options
     *
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get all item options as array with codes in array key
     *
     * @return array
     */
    public function getOptionsByCode()
    {
        return $this->_optionsByCode;
    }

    /**
     * Add option to item
     *
     * @param   Option|\Magento\Framework\DataObject|array $option
     * @return  $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addOption($option)
    {
        if (is_array($option)) {
            $option = $this->_wishlistOptFactory->create()->setData($option)->setItem($this);
        } elseif ($option instanceof Option) {
            $option->setItem($this);
        } elseif ($option instanceof \Magento\Framework\DataObject) {
            $option = $this->_wishlistOptFactory->create()->setData($option->getData())
               ->setProduct($option->getProduct())
               ->setItem($this);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid item option format.'));
        }

        $exOption = $this->getOptionByCode($option->getCode());
        if ($exOption) {
            $exOption->addData($option->getData());
        } else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }
        return $this;
    }

    /**
     * Remove option from item options
     *
     * @param string $code
     * @return $this
     */
    public function removeOption($code)
    {
        $option = $this->getOptionByCode($code);
        if ($option) {
            $option->isDeleted(true);
        }
        return $this;
    }

    /**
     * Get item option by code
     *
     * @param   string $code
     * @return  Option|null
     */
    public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code]) && !$this->_optionsByCode[$code]->isDeleted()) {
            return $this->_optionsByCode[$code];
        }
        return null;
    }

    /**
     * Returns whether Qty field is valid for this item
     *
     * @return bool
     */
    public function canHaveQty()
    {
        $product = $this->getProduct();
        return !$this->productTypeConfig->isProductSet($product->getTypeId());
    }

    /**
     * Get current custom option download url
     *
     * @return string
     */
    public function getCustomDownloadUrl()
    {
        return $this->_customOptionDownloadUrl;
    }

    /**
     * Sets custom option download url
     *
     * @param string $url
     * @return void
     */
    public function setCustomDownloadUrl($url)
    {
        $this->_customOptionDownloadUrl = $url;
    }

    /**
     * Returns special download params (if needed) for custom option with type = 'file'.
     * Needed to implement \Magento\Catalog\Model\Product\Configuration\Item\Interface.
     *
     * We have to customize only controller url, so return it.
     *
     * @return null|\Magento\Framework\DataObject
     */
    public function getFileDownloadParams()
    {
        $params = new \Magento\Framework\DataObject();
        $params->setUrl($this->_customOptionDownloadUrl);
        return $params;
    }

    /**
     * Loads item together with its options (default load() method doesn't load options).
     * If we need to load only some of options, then option code or array of option codes
     * can be provided in $optionsFilter.
     *
     * @param int $id
     * @param null|string|array $optionsFilter
     *
     * @return $this
     */
    public function loadWithOptions($id, $optionsFilter = null)
    {
        $this->load($id);
        if (!$this->getId()) {
            return $this;
        }

        $options = $this->_wishlOptionCollectionFactory->create()->addItemFilter($this);
        if ($optionsFilter) {
            $options->addFieldToFilter('code', $optionsFilter);
        }

        $this->setOptions($options->getOptionsByItem($this));
        return $this;
    }
}
