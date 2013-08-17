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
 * @category    Mage
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist item model
 *
 * @method Mage_Wishlist_Model_Resource_Item _getResource()
 * @method Mage_Wishlist_Model_Resource_Item getResource()
 * @method int getWishlistId()
 * @method Mage_Wishlist_Model_Item setWishlistId(int $value)
 * @method int getProductId()
 * @method Mage_Wishlist_Model_Item setProductId(int $value)
 * @method int getStoreId()
 * @method Mage_Wishlist_Model_Item setStoreId(int $value)
 * @method string getAddedAt()
 * @method Mage_Wishlist_Model_Item setAddedAt(string $value)
 * @method string getDescription()
 * @method Mage_Wishlist_Model_Item setDescription(string $value)
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Model_Item extends Mage_Core_Model_Abstract
    implements Mage_Catalog_Model_Product_Configuration_Item_Interface
{
    const EXCEPTION_CODE_NOT_SALABLE            = 901;
    const EXCEPTION_CODE_HAS_REQUIRED_OPTIONS   = 902;

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
     * @var array
     */
    protected $_options             = array();

    /**
     * Item options by code cache
     *
     * @var array
     */
    protected $_optionsByCode       = array();

    /**
     * Not Represent options
     *
     * @var array
     */
    protected $_notRepresentOptions = array('info_buyRequest');

    /**
     * Flag stating that options were successfully saved
     *
     */
    protected $_flagOptionsSaved = null;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Wishlist_Model_Resource_Item');
    }

    /**
     * Set quantity. If quantity is less than 0 - set it to 1
     *
     * @param int $qty
     * @return Mage_Wishlist_Model_Item
     */
    public function setQty($qty)
    {
        $this->setData('qty', ($qty >= 0) ? $qty : 1 );
        return $this;
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return Mage_Wishlist_Model_Resource_Item
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
        $skipOptions = array('id', 'qty', 'return_url');
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
     * @param   Mage_Wishlist_Model_Item_Option $option
     * @return  Mage_Wishlist_Model_Item
     */
    protected function _addOptionCode($option)
    {
        if (!isset($this->_optionsByCode[$option->getCode()])) {
            $this->_optionsByCode[$option->getCode()] = $option;
        }
        else {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('An item option with code %s already exists.', $option->getCode()));
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
     * @return Mage_Wishlist_Model_Item
     */
    protected function _saveItemOptions()
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

        $this->_flagOptionsSaved = true; // Report to watchers that options were saved

        return $this;
    }

    /**
     * Save model plus its options
     * Ensures saving options in case when resource model was not changed
     */
    public function save()
    {
        $hasDataChanges = $this->hasDataChanges();
        $this->_flagOptionsSaved = false;

        parent::save();

        if ($hasDataChanges && !$this->_flagOptionsSaved) {
            $this->_saveItemOptions();
        }
    }

    /**
     * Save item options after item saved
     *
     * @return Mage_Wishlist_Model_Item
     */
    protected function _afterSave()
    {
        $this->_saveItemOptions();
        return parent::_afterSave();
    }

    /**
     * Validate wish list item data
     *
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validate()
    {
        if (!$this->getWishlistId()) {
            Mage::throwException(Mage::helper('Mage_Wishlist_Helper_Data')->__('We can\'t specify a wish list.'));
        }
        if (!$this->getProductId()) {
            Mage::throwException(Mage::helper('Mage_Wishlist_Helper_Data')->__('Cannot specify product.'));
        }

        return true;
    }

    /**
     * Check required data
     *
     * @return Mage_Wishlist_Model_Item
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        // validate required item data
        $this->validate();

        // set current store id if it is not defined
        if (is_null($this->getStoreId())) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }

        // set current date if added at data is not defined
        if (is_null($this->getAddedAt())) {
            $this->setAddedAt(Mage::getSingleton('Mage_Core_Model_Date')->gmtDate());
        }

        return $this;
    }


    /**
     * Load item by product, wishlist and shared stores
     *
     * @param int $wishlistId
     * @param int $productId
     * @param array $sharedStores
     * @return Mage_Wishlist_Model_Item
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
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (is_null($product)) {
            if (!$this->getProductId()) {
                Mage::throwException(Mage::helper('Mage_Wishlist_Helper_Data')->__('Cannot specify product.'));
            }

            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId($this->getStoreId())
                ->load($this->getProductId());

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
     * @throws Mage_Core_Exception
     * @param Mage_Checkout_Model_Cart $cart
     * @param bool $delete  delete the item after successful add to cart
     * @return bool
     */
    public function addToCart(Mage_Checkout_Model_Cart $cart, $delete = false)
    {
        $product = $this->getProduct();

        $storeId = $this->getStoreId();

        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
            $urlData = Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Url')
                ->getRewriteByProductStore(array($product->getId() => $storeId));
            if (!isset($urlData[$product->getId()])) {
                return false;
            }
            $product->setUrlDataObject(new Varien_Object($urlData));
            $visibility = $product->getUrlDataObject()->getVisibility();
            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new Mage_Core_Exception(null, self::EXCEPTION_CODE_NOT_SALABLE);
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
        $query   = array();

        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            $query['options'] = 'cart';
        }

        return $product->getUrlModel()->getUrl($product, array('_query' => $query));
    }

    /**
     * Returns formatted buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @return Varien_Object
     */
    public function getBuyRequest()
    {
        $option = $this->getOptionByCode('info_buyRequest');
        $initialData = $option ? unserialize($option->getValue()) : null;

        // There can be wrong data due to bug in Grouped products - it formed 'info_buyRequest' as Varien_Object
        if ($initialData instanceof Varien_Object) {
            $initialData = $initialData->getData();
        }

        $buyRequest = new Varien_Object($initialData);
        $buyRequest->setOriginalQty($buyRequest->getQty())
            ->setQty($this->getQty() * 1);
        return $buyRequest;
    }

    /**
     * Merge data to item info_buyRequest option
     *
     * @param array|Varien_Object $buyRequest
     * @return Mage_Wishlist_Model_Item
     */
    public function mergeBuyRequest($buyRequest) {
        if ($buyRequest instanceof Varien_Object) {
            $buyRequest = $buyRequest->getData();
        }

        if (empty($buyRequest) || !is_array($buyRequest)) {
            return $this;
        }

        $oldBuyRequest = $this->getBuyRequest()
            ->getData();
        $sBuyRequest = serialize($buyRequest + $oldBuyRequest);

        $option = $this->getOptionByCode('info_buyRequest');
        if ($option) {
            $option->setValue($sBuyRequest);
        } else {
            $this->addOption(array(
                'code'  => 'info_buyRequest',
                'value' => $sBuyRequest
            ));
        }

        return $this;
    }

    /**
     * Set buy request - object, holding request received from
     * product view page with keys and options for configured product
     * @param Varien_Object $buyRequest
     * @return Mage_Wishlist_Model_Item
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
     * @param   Mage_Catalog_Model_Product $product
     * @param   Varien_Object $buyRequest
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
            if (!$product->isComposite()){
                return true;
            } else {
                return false;
            }
        }

        $requestArray = $buyRequest->getData();

        if(!$this->_compareOptions($requestArray, $selfOptions)){
            return false;
        }
        if(!$this->_compareOptions($selfOptions, $requestArray)){
            return false;
        }
        return true;
    }

    /**
     * Check product representation in item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if ($itemProduct->getId() != $product->getId()) {
            return false;
        }

        $itemOptions    = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if(!$this->compareOptions($itemOptions, $productOptions)){
            return false;
        }
        if(!$this->compareOptions($productOptions, $itemOptions)){
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
            if (in_array($code, $this->_notRepresentOptions )) {
                continue;
            }
            if ( !isset($options2[$code])
                || ($options2[$code]->getValue() === null)
                || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Initialize item options
     *
     * @param   array $options
     * @return  Mage_Wishlist_Model_Item
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
     * @return array
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
     * @param   Mage_Wishlist_Model_Item_Option $option
     * @return  Mage_Wishlist_Model_Item
     */
    public function addOption($option)
    {
        if (is_array($option)) {
            $option = Mage::getModel('Mage_Wishlist_Model_Item_Option')->setData($option)
                ->setItem($this);
        } else if ($option instanceof Mage_Wishlist_Model_Item_Option) {
            $option->setItem($this);
        } else if ($option instanceof Varien_Object) {
            $option = Mage::getModel('Mage_Wishlist_Model_Item_Option')->setData($option->getData())
               ->setProduct($option->getProduct())
               ->setItem($this);
        } else {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('Invalid item option format.'));
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
     *Remove option from item options
     *
     * @param string $code
     * @return Mage_Wishlist_Model_Item
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
     * @return  Mage_Wishlist_Model_Item_Option || null
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
        return $product->getTypeId() != Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE;
    }

    /**
     * Get current custom option download url
     */
    public function getCustomDownloadUrl()
    {
        return $this->_customOptionDownloadUrl;
    }

    /**
     * Sets custom option download url
     */
    public function setCustomDownloadUrl($url)
    {
        $this->_customOptionDownloadUrl = $url;
    }

    /**
     * Returns special download params (if needed) for custom option with type = 'file'.
     * Needed to implement Mage_Catalog_Model_Product_Configuration_Item_Interface.
     *
     * We have to customize only controller url, so return it.
     *
     * @return null|Varien_Object
     */
    public function getFileDownloadParams()
    {
        $params = new Varien_Object();
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
     * @return Mage_Wishlist_Model_Item
     */
    public function loadWithOptions($id, $optionsFilter = null)
    {
        $this->load($id);
        if (!$this->getId()) {
            return $this;
        }

        $options = Mage::getResourceModel('Mage_Wishlist_Model_Resource_Item_Option_Collection')
            ->addItemFilter($this);
        if ($optionsFilter) {
            $options->addFieldToFilter('code', $optionsFilter);
        }

        $this->setOptions($options->getOptionsByItem($this));
        return $this;
    }
}
