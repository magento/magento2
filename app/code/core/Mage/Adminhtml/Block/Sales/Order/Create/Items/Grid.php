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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create items grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    /**
     * Flag to check can items be move to customer storage
     *
     * @var bool
     */
    protected $_moveToCustomerStorage = true;

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_search_grid');
    }

    public function getItems()
    {
        $items = $this->getParentBlock()->getItems();
        $oldSuperMode = $this->getQuote()->getIsSuperMode();
        $this->getQuote()->setIsSuperMode(false);
        foreach ($items as $item) {
            // To dispatch inventory event sales_quote_item_qty_set_after, set item qty
            $item->setQty($item->getQty());
            $stockItem = $item->getProduct()->getStockItem();
            if ($stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                // This check has been performed properly in Inventory observer, so it has no sense
                /*
                $check = $stockItem->checkQuoteItemQty($item->getQty(), $item->getQty(), $item->getQty());
                $item->setMessage($check->getMessage());
                $item->setHasError($check->getHasError());
                */
                if ($item->getProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                    $item->setMessage(Mage::helper('Mage_Adminhtml_Helper_Data')->__('This product is currently disabled.'));
                    $item->setHasError(true);
                }
            }
        }
        $this->getQuote()->setIsSuperMode($oldSuperMode);
        return $items;
    }

    public function getSession()
    {
        return $this->getParentBlock()->getSession();
    }

    public function getItemEditablePrice($item)
    {
        return $item->getCalculationPrice()*1;
    }

    public function getOriginalEditablePrice($item)
    {
        if ($item->hasOriginalCustomPrice()) {
            $result = $item->getOriginalCustomPrice()*1;
        } elseif ($item->hasCustomPrice()) {
            $result = $item->getCustomPrice()*1;
        } else {
            if (Mage::helper('Mage_Tax_Helper_Data')->priceIncludesTax($this->getStore())) {
                $result = $item->getPriceInclTax()*1;
            } else {
                $result = $item->getOriginalPrice()*1;
            }
        }
        return $result;
    }

    public function getItemOrigPrice($item)
    {
//        return $this->convertPrice($item->getProduct()->getPrice());
        return $this->convertPrice($item->getPrice());
    }

    public function isGiftMessagesAvailable($item=null)
    {
        if(is_null($item)) {
            return $this->helper('Mage_GiftMessage_Helper_Message')->getIsMessagesAvailable(
                'items', $this->getQuote(), $this->getStore()
            );
        }

        return $this->helper('Mage_GiftMessage_Helper_Message')->getIsMessagesAvailable(
            'item', $item, $this->getStore()
        );
    }

    public function isAllowedForGiftMessage($item)
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Giftmessage_Save')->getIsAllowedQuoteItem($item);
    }

    /**
     * Check if we need display grid totals include tax
     *
     * @return bool
     */
    public function displayTotalsIncludeTax()
    {
        $res = Mage::getSingleton('Mage_Tax_Model_Config')->displayCartSubtotalInclTax($this->getStore())
            || Mage::getSingleton('Mage_Tax_Model_Config')->displayCartSubtotalBoth($this->getStore());
        return $res;
    }

    public function getSubtotal()
    {
        $address = $this->getQuoteAddress();
        if ($this->displayTotalsIncludeTax()) {
            if ($address->getSubtotalInclTax()) {
                return $address->getSubtotalInclTax();
            }
            return $address->getSubtotal()+$address->getTaxAmount();
        } else {
            return $address->getSubtotal();
        }
        return false;
    }

    public function getSubtotalWithDiscount()
    {
        $address = $this->getQuoteAddress();
        if ($this->displayTotalsIncludeTax()) {
            return $address->getSubtotal()+$address->getTaxAmount()+$this->getDiscountAmount();
        } else {
            return $address->getSubtotal()+$this->getDiscountAmount();
        }
    }

    public function getDiscountAmount()
    {
        return $this->getQuote()->getShippingAddress()->getDiscountAmount();
    }

    /**
     * Retrive quote address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getQuoteAddress()
    {
        if ($this->getQuote()->isVirtual()) {
            return $this->getQuote()->getBillingAddress();
        }
        else {
            return $this->getQuote()->getShippingAddress();
        }
    }

    /**
     * Define if specified item has already applied custom price
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return bool
     */
    public function usedCustomPriceForItem($item)
    {
        return $item->hasCustomPrice();
    }

    /**
     * Define if custom price can be applied for specified item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return bool
     */
    public function canApplyCustomPrice($item)
    {
        return !$item->isChildrenCalculated();
    }

    public function getQtyTitle($item)
    {
        $prices = $item->getProduct()->getTierPrice();
        if ($prices) {
            $info = array();
            foreach ($prices as $data) {
                $qty    = $data['price_qty']*1;
                $price  = $this->convertPrice($data['price']);
                $info[] = $this->helper('Mage_Sales_Helper_Data')->__('Buy %s for price %s', $qty, $price);
            }
            return implode(', ', $info);
        }
        else {
            return $this->helper('Mage_Sales_Helper_Data')->__('Item ordered qty');
        }
    }

    public function getTierHtml($item)
    {
        $html = '';
        $prices = $item->getProduct()->getTierPrice();
        if ($prices) {
            foreach ($prices as $data) {
                $qty    = $data['price_qty']*1;
                $price  = $this->convertPrice($data['price']);
                $info[] = $this->helper('Mage_Sales_Helper_Data')->__('%s for %s', $qty, $price);
            }
            $html = implode('<br/>', $info);
        }
        return $html;
    }

    /**
     * Get Custom Options of item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return array
     */
    public function getCustomOptions(Mage_Sales_Model_Quote_Item $item)
    {
        $optionStr = '';
        $this->_moveToCustomerStorage = true;
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $item->getProduct()->getOptionById($optionId)) {
                    $optionValue = $item->getOptionByCode('option_' . $option->getId())->getValue();

                    $optionStr .= $option->getTitle() . ':';

                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);

                    $optionStr .= $group->getEditableOptionValue($quoteItemOption->getValue());
                    $optionStr .= "\n";
                }
            }
        }
        return $optionStr;
    }

    /**
     * Get flag for rights to move items to customer storage
     *
     * @return bool
     */
    public function getMoveToCustomerStorage()
    {
        return $this->_moveToCustomerStorage;
    }

    public function displaySubtotalInclTax($item)
    {
        if ($item->getTaxBeforeDiscount()) {
            $tax = $item->getTaxBeforeDiscount();
        } else {
            $tax = $item->getTaxAmount() ? $item->getTaxAmount() : 0;
        }
        return $this->formatPrice($item->getRowTotal() + $tax);
    }

    public function displayOriginalPriceInclTax($item)
    {
        $tax = 0;
        if ($item->getTaxPercent()) {
            $tax = $item->getPrice() * ($item->getTaxPercent() / 100);
        }
        return $this->convertPrice($item->getPrice()+($tax/$item->getQty()));
    }

    public function displayRowTotalWithDiscountInclTax($item)
    {
        $tax = ($item->getTaxAmount() ? $item->getTaxAmount() : 0);
        return $this->formatPrice($item->getRowTotal()-$item->getDiscountAmount()+$tax);
    }

    public function getInclExclTaxMessage()
    {
        if (Mage::helper('Mage_Tax_Helper_Data')->priceIncludesTax($this->getStore())) {
            return Mage::helper('Mage_Sales_Helper_Data')->__('* - Enter custom price including tax');
        } else {
            return Mage::helper('Mage_Sales_Helper_Data')->__('* - Enter custom price excluding tax');
        }
    }

    public function getStore()
    {
        return $this->getQuote()->getStore();
    }

    /**
     * Return html button which calls configure window
     *
     * @param  $item
     * @return string
     */
    public function getConfigureButtonHtml($item)
    {
        $product = $item->getProduct();

        $options = array('label' => Mage::helper('Mage_Sales_Helper_Data')->__('Configure'));
        if ($product->canConfigure()) {
            $options['onclick'] = sprintf('order.showQuoteItemConfiguration(%s)', $item->getId());
        } else {
            $options['class'] = ' disabled';
            $options['title'] = Mage::helper('Mage_Sales_Helper_Data')->__('This product does not have any configurable options');
        }

        return $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
            ->setData($options)
            ->toHtml();
    }

    /**
     * Get order item extra info block
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return Mage_Core_Block_Abstract
     */
    public function getItemExtraInfo($item)
    {
        return $this->getLayout()
            ->getBlock('order_item_extra_info')
            ->setItem($item);
    }

    /**
     * Returns whether moving to wishlist is allowed for this item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return bool
     */
    public function isMoveToWishlistAllowed($item)
    {
        return $item->getProduct()->isVisibleInSiteVisibility();
    }
}
