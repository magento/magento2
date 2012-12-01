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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Weee_Model_Total_Creditmemo_Weee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $store = $creditmemo->getStore();

        $totalTax              = 0;
        $baseTotalTax          = 0;

        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $orderItemQty = $item->getOrderItem()->getQtyOrdered();

            $totalTax += $item->getWeeeTaxAppliedAmount()*$item->getQty();
            $baseTotalTax += $item->getBaseWeeeTaxAppliedAmount()*$item->getQty();

            $newApplied = array();
            $applied = Mage::helper('Mage_Weee_Helper_Data')->getApplied($item);
            foreach ($applied as $one) {
                $one['base_row_amount'] = $one['base_amount']*$item->getQty();
                $one['row_amount'] = $one['amount']*$item->getQty();
                $one['base_row_amount_incl_tax'] = $one['base_amount_incl_tax']*$item->getQty();
                $one['row_amount_incl_tax'] = $one['amount_incl_tax']*$item->getQty();

                $newApplied[] = $one;
            }
            Mage::helper('Mage_Weee_Helper_Data')->setApplied($item, $newApplied);

            $item->setWeeeTaxRowDisposition($item->getWeeeTaxDisposition()*$item->getQty());
            $item->setBaseWeeeTaxRowDisposition($item->getBaseWeeeTaxDisposition()*$item->getQty());
        }

        if (Mage::helper('Mage_Weee_Helper_Data')->includeInSubtotal($store)) {
            $creditmemo->setSubtotal($creditmemo->getSubtotal() + $totalTax);
            $creditmemo->setBaseSubtotal($creditmemo->getBaseSubtotal() + $baseTotalTax);
        } else {
            $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $totalTax);
            $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTotalTax);
        }

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalTax);

        return $this;
    }
}
