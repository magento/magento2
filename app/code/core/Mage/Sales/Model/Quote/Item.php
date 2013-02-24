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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Quote Item Model
 *
 * @method Mage_Sales_Model_Resource_Quote_Item _getResource()
 * @method Mage_Sales_Model_Resource_Quote_Item getResource()
 * @method int getQuoteId()
 * @method Mage_Sales_Model_Quote_Item setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Quote_Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Sales_Model_Quote_Item setUpdatedAt(string $value)
 * @method int getProductId()
 * @method Mage_Sales_Model_Quote_Item setProductId(int $value)
 * @method int getStoreId()
 * @method Mage_Sales_Model_Quote_Item setStoreId(int $value)
 * @method int getParentItemId()
 * @method Mage_Sales_Model_Quote_Item setParentItemId(int $value)
 * @method int getIsVirtual()
 * @method Mage_Sales_Model_Quote_Item setIsVirtual(int $value)
 * @method string getSku()
 * @method Mage_Sales_Model_Quote_Item setSku(string $value)
 * @method string getName()
 * @method Mage_Sales_Model_Quote_Item setName(string $value)
 * @method string getDescription()
 * @method Mage_Sales_Model_Quote_Item setDescription(string $value)
 * @method string getAppliedRuleIds()
 * @method Mage_Sales_Model_Quote_Item setAppliedRuleIds(string $value)
 * @method string getAdditionalData()
 * @method Mage_Sales_Model_Quote_Item setAdditionalData(string $value)
 * @method int getFreeShipping()
 * @method Mage_Sales_Model_Quote_Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method Mage_Sales_Model_Quote_Item setIsQtyDecimal(int $value)
 * @method int getNoDiscount()
 * @method Mage_Sales_Model_Quote_Item setNoDiscount(int $value)
 * @method float getWeight()
 * @method Mage_Sales_Model_Quote_Item setWeight(float $value)
 * @method float getBasePrice()
 * @method Mage_Sales_Model_Quote_Item setBasePrice(float $value)
 * @method float getCustomPrice()
 * @method float getDiscountPercent()
 * @method Mage_Sales_Model_Quote_Item setDiscountPercent(float $value)
 * @method float getDiscountAmount()
 * @method Mage_Sales_Model_Quote_Item setDiscountAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method Mage_Sales_Model_Quote_Item setBaseDiscountAmount(float $value)
 * @method float getTaxPercent()
 * @method Mage_Sales_Model_Quote_Item setTaxPercent(float $value)
 * @method Mage_Sales_Model_Quote_Item setTaxAmount(float $value)
 * @method Mage_Sales_Model_Quote_Item setBaseTaxAmount(float $value)
 * @method float getRowTotal()
 * @method Mage_Sales_Model_Quote_Item setRowTotal(float $value)
 * @method float getBaseRowTotal()
 * @method Mage_Sales_Model_Quote_Item setBaseRowTotal(float $value)
 * @method float getRowTotalWithDiscount()
 * @method Mage_Sales_Model_Quote_Item setRowTotalWithDiscount(float $value)
 * @method float getRowWeight()
 * @method Mage_Sales_Model_Quote_Item setRowWeight(float $value)
 * @method Mage_Sales_Model_Quote_Item setProductType(string $value)
 * @method float getBaseTaxBeforeDiscount()
 * @method Mage_Sales_Model_Quote_Item setBaseTaxBeforeDiscount(float $value)
 * @method float getTaxBeforeDiscount()
 * @method Mage_Sales_Model_Quote_Item setTaxBeforeDiscount(float $value)
 * @method float getOriginalCustomPrice()
 * @method Mage_Sales_Model_Quote_Item setOriginalCustomPrice(float $value)
 * @method string getRedirectUrl()
 * @method Mage_Sales_Model_Quote_Item setRedirectUrl(string $value)
 * @method float getBaseCost()
 * @method Mage_Sales_Model_Quote_Item setBaseCost(float $value)
 * @method float getPriceInclTax()
 * @method Mage_Sales_Model_Quote_Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method Mage_Sales_Model_Quote_Item setBasePriceInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method Mage_Sales_Model_Quote_Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method Mage_Sales_Model_Quote_Item setBaseRowTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method Mage_Sales_Model_Quote_Item setGiftMessageId(int $value)
 * @method string getWeeeTaxApplied()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxApplied(string $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmnt()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method float getWeeeTaxDisposition()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxDisposition(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method Mage_Sales_Model_Quote_Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxDisposition(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method Mage_Sales_Model_Quote_Item setBaseWeeeTaxRowDisposition(float $value)
 * @method float getHiddenTaxAmount()
 * @method Mage_Sales_Model_Quote_Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method Mage_Sales_Model_Quote_Item setBaseHiddenTaxAmount(float $value)
 * @method null|bool getHasConfigurationUnavailableError()
 * @method Mage_Sales_Model_Quote_Item setHasConfigurationUnavailableError(bool $value)
 * @method Mage_Sales_Model_Quote_Item unsHasConfigurationUnavailableError()
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Quote_Item extends Mage_Sales_Model_Quote_Item_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_quote_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Quote model object
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

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
     * Array of errors associated with this quote item
     *
     * @var Mage_Sales_Model_Status_List
     */
    protected $_errorInfos;

    /**
     * @param Mage_Core_Model_Event_Manager $eventDispatcher
     * @param Mage_Core_Model_Cache $cacheManager
     * @param Mage_Sales_Model_Status_ListFactory $statusListFactory
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_Core_Model_Cache $cacheManager,
        Mage_Sales_Model_Status_ListFactory $statusListFactory,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_errorInfos = $statusListFactory->create();
        parent::__construct($eventDispatcher, $cacheManager, $resource, $resourceCollection, $data);
    }


    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Quote_Item');
    }

    /**
     * Quote Item Before Save prepare data process
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->setIsVirtual($this->getProduct()->getIsVirtual());
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        return $this;
    }

    /**
     * Retrieve address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if ($this->getQuote()->getItemsQty() == $this->getQuote()->getVirtualItemsQty()) {
            $address = $this->getQuote()->getBillingAddress();
        } else {
            $address = $this->getQuote()->getShippingAddress();
        }

        return $address;
    }

    /**
     * Declare quote model object
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Prepare quantity
     *
     * @param float|int $qty
     * @return int|float
     */
    protected function _prepareQty($qty)
    {
        $qty = Mage::app()->getLocale()->getNumber($qty);
        $qty = ($qty > 0) ? $qty : 1;
        return $qty;
    }

    /**
     * Adding quantity to quote item
     *
     * @param float $qty
     * @return Mage_Sales_Model_Quote_Item
     */
    public function addQty($qty)
    {
        $oldQty = $this->getQty();
        $qty = $this->_prepareQty($qty);

        /**
         * We can't modify quontity of existing items which have parent
         * This qty declared just once duering add process and is not editable
         */
        if (!$this->getParentItem() || !$this->getId()) {
            $this->setQtyToAdd($qty);
            $this->setQty($oldQty+$qty);
        }
        return $this;
    }

    /**
     * Declare quote item quantity
     *
     * @param float $qty
     * @return Mage_Sales_Model_Quote_Item
     */
    public function setQty($qty)
    {
        $qty    = $this->_prepareQty($qty);
        $oldQty = $this->_getData('qty');
        $this->setData('qty', $qty);

        Mage::dispatchEvent('sales_quote_item_qty_set_after', array('item'=>$this));

        if ($this->getQuote() && $this->getQuote()->getIgnoreOldQty()) {
            return $this;
        }
        if ($this->getUseOldQty()) {
            $this->setData('qty', $oldQty);
        }

        return $this;
    }

    /**
     * Retrieve option product with Qty
     *
     * Return array
     * 'qty'        => the qty
     * 'product'    => the product model
     *
     * @return array
     */
    public function getQtyOptions()
    {
        $qtyOptions = $this->getData('qty_options');
        if (is_null($qtyOptions)) {
            $productIds = array();
            $qtyOptions = array();
            foreach ($this->getOptions() as $option) {
                /** @var $option Mage_Sales_Model_Quote_Item_Option */
                if (is_object($option->getProduct())
                    && $option->getProduct()->getId() != $this->getProduct()->getId()
                ) {
                    $productIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
                }
            }

            foreach ($productIds as $productId) {
                $option = $this->getOptionByCode('product_qty_' . $productId);
                if ($option) {
                    $qtyOptions[$productId] = $option;
                }
            }

            $this->setData('qty_options', $qtyOptions);
        }

        return $qtyOptions;
    }

    /**
     * Set option product with Qty
     *
     * @param  $qtyOptions
     * @return Mage_Sales_Model_Quote_Item
     */
    public function setQtyOptions($qtyOptions)
    {
        return $this->setData('qty_options', $qtyOptions);
    }

    /**
     * Setup product for quote item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function setProduct($product)
    {
        if ($this->getQuote()) {
            $product->setStoreId($this->getQuote()->getStoreId());
            $product->setCustomerGroupId($this->getQuote()->getCustomerGroupId());
        }
        $this->setData('product', $product)
            ->setProductId($product->getId())
            ->setProductType($product->getTypeId())
            ->setSku($this->getProduct()->getSku())
            ->setName($product->getName())
            ->setWeight($this->getProduct()->getWeight())
            ->setTaxClassId($product->getTaxClassId())
            ->setBaseCost($product->getCost())
            ->setIsRecurring($product->getIsRecurring())
        ;

        if ($product->getStockItem()) {
            $this->setIsQtyDecimal($product->getStockItem()->getIsQtyDecimal());
        }

        Mage::dispatchEvent('sales_quote_item_set_product', array(
            'product' => $product,
            'quote_item'=>$this
        ));


//        if ($options = $product->getCustomOptions()) {
//            foreach ($options as $option) {
//                $this->addOption($option);
//            }
//        }
        return $this;
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
        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }

        /**
         * Check maybe product is planned to be a child of some quote item - in this case we limit search
         * only within same parent item
         */
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }

        // Check options
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
     * Compare item
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  bool
     */
    public function compare($item)
    {
        if ($this->getProductId() != $item->getProductId()) {
            return false;
        }
        foreach ($this->getOptions() as $option) {
            if (in_array($option->getCode(), $this->_notRepresentOptions)) {
                continue;
            }
            if ($itemOption = $item->getOptionByCode($option->getCode())) {
                $itemOptionValue = $itemOption->getValue();
                $optionValue     = $option->getValue();

                // dispose of some options params, that can cramp comparing of arrays
                if (is_string($itemOptionValue) && is_string($optionValue)) {
                    $_itemOptionValue = @unserialize($itemOptionValue);
                    $_optionValue     = @unserialize($optionValue);
                    if (is_array($_itemOptionValue) && is_array($_optionValue)) {
                        $itemOptionValue = $_itemOptionValue;
                        $optionValue     = $_optionValue;
                        // looks like it does not break bundle selection qty
                        unset($itemOptionValue['qty'], $itemOptionValue['uenc']);
                        unset($optionValue['qty'], $optionValue['uenc']);
                    }
                }

                if ($itemOptionValue != $optionValue) {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        return true;
    }

    /**
     * Get item product type
     *
     * @return string
     */
    public function getProductType()
    {
        if ($option = $this->getOptionByCode('product_type')) {
            return $option->getValue();
        }
        if ($product = $this->getProduct()) {
            return $product->getTypeId();
        }
        return $this->_getData('product_type');
    }

    /**
     * Return real product type of item
     *
     * @return unknown
     */
    public function getRealProductType()
    {
        return $this->_getData('product_type');
    }

    /**
     * Convert Quote Item to array
     *
     * @param array $arrAttributes
     * @return array
     */
    public function toArray(array $arrAttributes=array())
    {
        $data = parent::toArray($arrAttributes);

        if ($product = $this->getProduct()) {
            $data['product'] = $product->toArray();
        }
        return $data;
    }

    /**
     * Initialize quote item options
     *
     * @param   array $options
     * @return  Mage_Sales_Model_Quote_Item
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
     * @param   Mage_Sales_Model_Quote_Item_Option|Varien_Object $option
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function addOption($option)
    {
        if (is_array($option)) {
            $option = Mage::getModel('Mage_Sales_Model_Quote_Item_Option')->setData($option)
                ->setItem($this);
        }
        elseif (($option instanceof Varien_Object) && !($option instanceof Mage_Sales_Model_Quote_Item_Option)) {
            $option = Mage::getModel('Mage_Sales_Model_Quote_Item_Option')->setData($option->getData())
               ->setProduct($option->getProduct())
               ->setItem($this);
        }
        elseif($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $option->setItem($this);
        }
        else {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('Invalid item option format.'));
        }

        if ($exOption = $this->getOptionByCode($option->getCode())) {
            $exOption->addData($option->getData());
        }
        else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }
        return $this;
    }

    /**
     * Can specify specific actions for ability to change given quote options values
     * Exemple: cataloginventory decimal qty validation may change qty to int,
     * so need to change quote item qty option value.
     *
     * @param Varien_Object $option
     * @param int|float|null $value
     * @return Mage_Sales_Model_Quote_Item
     */
    public function updateQtyOption(Varien_Object $option, $value)
    {
        $optionProduct  = $option->getProduct();
        $options        = $this->getQtyOptions();

        if (isset($options[$optionProduct->getId()])) {
            $options[$optionProduct->getId()]->setValue($value);
        }

        $this->getProduct()->getTypeInstance()
            ->updateQtyOption($this->getOptions(), $option, $value, $this->getProduct());

        return $this;
    }

    /**
     *Remove option from item options
     *
     * @param string $code
     * @return Mage_Sales_Model_Quote_Item
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
     * Register option code
     *
     * @param   Mage_Sales_Model_Quote_Item_Option $option
     * @return  Mage_Sales_Model_Quote_Item
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
     * Get item option by code
     *
     * @param   string $code
     * @return  Mage_Sales_Model_Quote_Item_Option || null
     */
    public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code]) && !$this->_optionsByCode[$code]->isDeleted()) {
            return $this->_optionsByCode[$code];
        }
        return null;
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
     * @return Mage_Sales_Model_Quote_Item
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
     * @return Mage_Sales_Model_Quote_Item
     */
    protected function _afterSave()
    {
        $this->_saveItemOptions();
        return parent::_afterSave();
    }

    /**
     * Clone quote item
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function __clone()
    {
        parent::__clone();
        $options = $this->getOptions();
        $this->_quote           = null;
        $this->_options         = array();
        $this->_optionsByCode   = array();
        foreach ($options as $option) {
            $this->addOption(clone $option);
        }
        return $this;
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
        $buyRequest = new Varien_Object($option ? unserialize($option->getValue()) : null);

        // Overwrite standard buy request qty, because item qty could have changed since adding to quote
        $buyRequest->setOriginalQty($buyRequest->getQty())
            ->setQty($this->getQty() * 1);

        return $buyRequest;
    }

    /**
     * Sets flag, whether this quote item has some error associated with it.
     *
     * @param bool $flag
     * @return Mage_Sales_Model_Quote_Item
     */
    protected function _setHasError($flag)
    {
        return $this->setData('has_error', $flag);
    }

    /**
     * Sets flag, whether this quote item has some error associated with it.
     * When TRUE - also adds 'unknown' error information to list of quote item errors.
     * When FALSE - clears whole list of quote item errors.
     * It's recommended to use addErrorInfo() instead - to be able to remove error statuses later.
     *
     * @param bool $flag
     * @return Mage_Sales_Model_Quote_Item
     * @see addErrorInfo()
     */
    public function setHasError($flag)
    {
        if ($flag) {
            $this->addErrorInfo();
        } else {
            $this->_clearErrorInfo();
        }
        return $this;
    }

    /**
     * Clears list of errors, associated with this quote item.
     * Also automatically removes error-flag from oneself.
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    protected function _clearErrorInfo()
    {
        $this->_errorInfos->clear();
        $this->_setHasError(false);
        return $this;
    }

    /**
     * Adds error information to the quote item.
     * Automatically sets error flag.
     *
     * @param string|null $origin Usually a name of module, that embeds error
     * @param int|null $code Error code, unique for origin, that sets it
     * @param string|null $message Error message
     * @param Varien_Object|null $additionalData Any additional data, that caller would like to store
     * @return Mage_Sales_Model_Quote_Item
     */
    public function addErrorInfo($origin = null, $code = null, $message = null, $additionalData = null)
    {
        $this->_errorInfos->addItem($origin, $code, $message, $additionalData);
        if ($message !== null) {
            $this->setMessage($message);
        }
        $this->_setHasError(true);

        return $this;
    }

    /**
     * Retrieves all error infos, associated with this item
     *
     * @return array
     */
    public function getErrorInfos()
    {
        return $this->_errorInfos->getItems();
    }

    /**
     * Removes error infos, that have parameters equal to passed in $params.
     * $params can have following keys (if not set - then any item is good for this key):
     *   'origin', 'code', 'message'
     *
     * @param array $params
     * @return Mage_Sales_Model_Quote_Item
     */
    public function removeErrorInfosByParams($params)
    {
        $removedItems = $this->_errorInfos->removeItemsByParams($params);
        foreach ($removedItems as $item) {
            if ($item['message'] !== null) {
                $this->removeMessageByText($item['message']);
            }
        }

        if (!$this->_errorInfos->getItems()) {
            $this->_setHasError(false);
        }

        return $this;
    }
}
