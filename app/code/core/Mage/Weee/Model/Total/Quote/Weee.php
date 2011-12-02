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
 * @package     Mage_Weee
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Weee_Model_Total_Quote_Weee extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * Weee module helper object
     *
     * @var Mage_Weee_Helper_Data
     */
    protected $_helper;
    protected $_store;

    /**
     * Tax configuration object
     *
     * @var Mage_Tax_Model_Config
     */
    protected $_config;

    /**
     * Flag which notify what tax amount can be affected by fixed porduct tax
     *
     * @var bool
     */
    protected $_isTaxAffected;

    /**
     * Initialize Weee totals collector
     */
    public function __construct()
    {
        $this->setCode('weee');
        $this->_helper = Mage::helper('Mage_Weee_Helper_Data');
        $this->_config = Mage::getSingleton('Mage_Tax_Model_Config');
    }

    /**
     * Collect Weee taxes amount and prepare items prices for taxation and discount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        Mage_Sales_Model_Quote_Address_Total_Abstract::collect($address);
        $this->_isTaxAffected = false;
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $address->setAppliedTaxesReset(true);
        $address->setAppliedTaxes(array());

        $this->_store = $address->getQuote()->getStore();
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $this->_resetItemData($item);
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_resetItemData($child);
                    $this->_process($address, $child);
                }
                $this->_recalculateParent($item);
            } else {
                $this->_process($address, $item);
            }
        }

        if ($this->_isTaxAffected) {
            $address->unsSubtotalInclTax();
            $address->unsBaseSubtotalInclTax();
        }

        return $this;
    }

    /**
     * Calculate item fixed tax and prepare information for discount and recular taxation
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    protected function _process(Mage_Sales_Model_Quote_Address $address, $item)
    {
        if (!$this->_helper->isEnabled($this->_store)) {
            return $this;
        }

        $attributes = $this->_helper->getProductWeeeAttributes(
            $item->getProduct(),
            $address,
            $address->getQuote()->getBillingAddress(),
            $this->_store->getWebsiteId()
        );

        $applied = array();
        $productTaxes = array();

        $totalValue         = 0;
        $baseTotalValue     = 0;
        $totalRowValue      = 0;
        $baseTotalRowValue  = 0;

        foreach ($attributes as $k=>$attribute) {
            $baseValue      = $attribute->getAmount();
            $value          = $this->_store->convertPrice($baseValue);
            $rowValue       = $value*$item->getTotalQty();
            $baseRowValue   = $baseValue*$item->getTotalQty();
            $title          = $attribute->getName();

            $totalValue         += $value;
            $baseTotalValue     += $baseValue;
            $totalRowValue      += $rowValue;
            $baseTotalRowValue  += $baseRowValue;

            $productTaxes[] = array(
                'title'         => $title,
                'base_amount'   => $baseValue,
                'amount'        => $value,
                'row_amount'    => $rowValue,
                'base_row_amount'=> $baseRowValue,
                /**
                 * Tax value can't be presented as include/exclude tax
                 */
                'base_amount_incl_tax'      => $baseValue,
                'amount_incl_tax'           => $value,
                'row_amount_incl_tax'       => $rowValue,
                'base_row_amount_incl_tax'  => $baseRowValue,
            );

            $applied[] = array(
                'id'        => $attribute->getCode(),
                'percent'   => null,
                'hidden'    => $this->_helper->includeInSubtotal($this->_store),
                'rates'     => array(array(
                    'base_real_amount'=> $baseRowValue,
                    'base_amount'   => $baseRowValue,
                    'amount'        => $rowValue,
                    'code'          => $attribute->getCode(),
                    'title'         => $title,
                    'percent'       => null,
                    'position'      => 1,
                    'priority'      => -1000+$k,
                ))
            );
        }

        $item->setWeeeTaxAppliedAmount($totalValue)
            ->setBaseWeeeTaxAppliedAmount($baseTotalValue)
            ->setWeeeTaxAppliedRowAmount($totalRowValue)
            ->setBaseWeeeTaxAppliedRowAmnt($baseTotalRowValue);

        $this->_processTaxSettings($item, $totalValue, $baseTotalValue, $totalRowValue, $baseTotalRowValue)
            ->_processTotalAmount($address, $totalRowValue, $baseTotalRowValue)
            ->_processDiscountSettings($item, $totalValue, $baseTotalValue);

        $this->_helper->setApplied($item, array_merge($this->_helper->getApplied($item), $productTaxes));
        if ($applied) {
            $this->_saveAppliedTaxes($address, $applied,
               $item->getWeeeTaxAppliedAmount(),
               $item->getBaseWeeeTaxAppliedAmount(),
               null
            );
        }

    }

    /**
     * Check if discount should be applied to weee and add weee to discounted price
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $value
     * @param   float $baseValue
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    protected function _processDiscountSettings($item, $value, $baseValue)
    {
        if ($this->_helper->isDiscounted($this->_store)) {
            Mage::helper('Mage_SalesRule_Helper_Data')->addItemDiscountPrices($item, $baseValue, $value);
        }
        return $this;
    }

    /**
     * Add extra amount which should be taxable by regular tax
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $value
     * @param   float $baseValue
     * @param   float $rowValue
     * @param   float $baseRowValue
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    protected function _processTaxSettings($item, $value, $baseValue, $rowValue, $baseRowValue)
    {
        if ($this->_helper->isTaxable($this->_store) && $rowValue) {
            if (!$this->_config->priceIncludesTax($this->_store)) {
                $item->setExtraTaxableAmount($value)
                    ->setBaseExtraTaxableAmount($baseValue)
                    ->setExtraRowTaxableAmount($rowValue)
                    ->setBaseExtraRowTaxableAmount($baseRowValue);
            }
            $item->unsRowTotalInclTax()
                ->unsBaseRowTotalInclTax()
                ->unsPriceInclTax()
                ->unsBasePriceInclTax();
            $this->_isTaxAffected = true;
        }
        return $this;
    }

    /**
     * Proces row amount based on FPT total amount configuration setting
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   float $rowValue
     * @param   float $baseRowValue
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    protected function _processTotalAmount($address, $rowValue, $baseRowValue)
    {
        if ($this->_helper->includeInSubtotal($this->_store)) {
            $address->addTotalAmount('subtotal', $rowValue);
            $address->addBaseTotalAmount('subtotal', $baseRowValue);
            $this->_isTaxAffected = true;
        } else {
            $address->setExtraTaxAmount($address->getExtraTaxAmount() + $rowValue);
            $address->setBaseExtraTaxAmount($address->getBaseExtraTaxAmount() + $baseRowValue);
        }
        return $this;
    }

    /**
     * Recalculate parent item amounts based on children results
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    protected function _recalculateParent(Mage_Sales_Model_Quote_Item_Abstract $item)
    {

    }

    /**
     * Reset information about FPT for shopping cart item
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    protected function _resetItemData($item)
    {
        $this->_helper->setApplied($item, array());

        $item->setBaseWeeeTaxDisposition(0);
        $item->setWeeeTaxDisposition(0);

        $item->setBaseWeeeTaxRowDisposition(0);
        $item->setWeeeTaxRowDisposition(0);

        $item->setBaseWeeeTaxAppliedAmount(0);
        $item->setBaseWeeeTaxAppliedRowAmnt(0);

        $item->setWeeeTaxAppliedAmount(0);
        $item->setWeeeTaxAppliedRowAmount(0);
    }

    /**
     * Fetch FPT data to address object for display in totals block
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Weee_Model_Total_Quote_Weee
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }

    /**
     * No aggregated label for fixed product tax
     *
     * TODO: fix
     */
    public function getLabel()
    {
        return '';
    }
}
