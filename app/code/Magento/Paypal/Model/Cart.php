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
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal-specific model for shopping cart items and totals
 * The main idea is to accommodate all possible totals into PayPal-compatible 4 totals and line items
 */
namespace Magento\Paypal\Model;

class Cart extends \Magento\Payment\Model\Cart
{
    /**
     * @var bool
     */
    protected $_areAmountsValid = false;

    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @return array
     */
    public function getAmounts()
    {
        $this->_collectItemsAndAmounts();

        if (!$this->_areAmountsValid) {
            $subtotal = $this->getSubtotal() + $this->getTax();

            if (empty($this->_transferFlags[self::AMOUNT_SHIPPING])) {
                $subtotal += $this->getShipping();
            }

            if (empty($this->_transferFlags[self::AMOUNT_DISCOUNT])) {
                $subtotal -= $this->getDiscount();
            }

            return array(
                self::AMOUNT_SUBTOTAL => $subtotal
            );
        }

        return $this->_amounts;
    }

    /**
     * Calculate subtotal from custom items
     */
    protected function _calculateCustomItemsSubtotal()
    {
        parent::_calculateCustomItemsSubtotal();
        $this->_applyHiddenTaxWorkaround($this->_salesModel);

        $this->_validate();
    }

    /**
     * Check the line items and totals according to PayPal business logic limitations
     */
    protected function _validate()
    {
        $areItemsValid = false;
        $this->_areAmountsValid = false;

        $referenceAmount = $this->_salesModel->getDataUsingMethod('base_grand_total');

        $itemsSubtotal = 0;
        foreach ($this->getAllItems() as $i) {
            $itemsSubtotal = $itemsSubtotal + $i->getQty() * $i->getAmount();
        }

        $sum = $itemsSubtotal + $this->getTax();

        if (empty($this->_transferFlags[self::AMOUNT_SHIPPING])) {
            $sum += $this->getShipping();
        }

        if (empty($this->_transferFlags[self::AMOUNT_DISCOUNT])) {
            $sum -= $this->getDiscount();
            // PayPal requires to have discount less than items subtotal
            $this->_areAmountsValid = round($this->getDiscount(), 4) < round($itemsSubtotal, 4);
        } else {
            $this->_areAmountsValid = $itemsSubtotal > 0.00001;
        }

        /**
         * numbers are intentionally converted to strings because of possible comparison error
         * see http://php.net/float
         */
        // match sum of all the items and totals to the reference amount
        if (sprintf('%.4F', $sum) == sprintf('%.4F', $referenceAmount)) {
            $areItemsValid = true;
        }

        $areItemsValid = $areItemsValid && $this->_areAmountsValid;

        if (!$areItemsValid) {
            $this->_salesModelItems = array();
            $this->_customItems = array();
        }
    }

    /**
     * Import items from sales model with workarounds for PayPal
     */
    protected function _importItemsFromSalesModel()
    {
        $this->_salesModelItems = array();

        foreach ($this->_salesModel->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $amount = $item->getPrice();
            $qty = $item->getQty();

            $subAggregatedLabel = '';

            // workaround in case if item subtotal precision is not compatible with PayPal (.2)
            if ($amount - round($amount, 2)) {
                $amount = $amount * $qty;
                $subAggregatedLabel = ' x' . $qty;
                $qty = 1;
            }

            // aggregate item price if item qty * price does not match row total
            $itemBaseRowTotal = $item->getOriginalItem()->getBaseRowTotal();
            if (($amount * $qty) != $itemBaseRowTotal) {
                $amount = (float)$itemBaseRowTotal;
                $subAggregatedLabel = ' x' . $qty;
                $qty = 1;
            }

            $this->_salesModelItems[] = $this->_createItemFromData($item->getName() . $subAggregatedLabel, $qty,
                $amount);
        }

        $this->addSubtotal($this->_salesModel->getBaseSubtotal());
        $this->addTax($this->_salesModel->getBaseTaxAmount());
        $this->addShipping($this->_salesModel->getBaseShippingAmount());
        $this->addDiscount(abs($this->_salesModel->getBaseDiscountAmount()));
    }

    /**
     * Add "hidden" discount and shipping tax
     *
     * Go ahead, try to understand ]:->
     *
     * Tax settings for getting "discount tax":
     * - Catalog Prices = Including Tax
     * - Apply Customer Tax = After Discount
     * - Apply Discount on Prices = Including Tax
     *
     * Test case for getting "hidden shipping tax":
     * - Make sure shipping is taxable (set shipping tax class)
     * - Catalog Prices = Including Tax
     * - Shipping Prices = Including Tax
     * - Apply Customer Tax = After Discount
     * - Create a shopping cart price rule with % discount applied to the Shipping Amount
     * - run shopping cart and estimate shipping
     * - go to PayPal
     *
     * @param \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface $salesEntity
     */
    protected function _applyHiddenTaxWorkaround(\Magento\Payment\Model\Cart\SalesModel\SalesModelInterface $salesEntity)
    {
        $dataContainer = $salesEntity->getTaxContainer();
        $this->addTax((float)$dataContainer->getBaseHiddenTaxAmount());
        $this->addTax((float)$dataContainer->getBaseShippingHiddenTaxAmnt());
    }
}
