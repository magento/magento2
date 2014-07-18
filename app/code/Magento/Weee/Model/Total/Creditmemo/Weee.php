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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Weee\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;

class Weee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData = null;

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assigns it as object
     * attributes This behavior may change in child classes
     *
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param array $data
     */
    public function __construct(\Magento\Weee\Helper\Data $weeeData, array $data = array())
    {
        $this->_weeeData = $weeeData;
        parent::__construct($data);
    }

    /**
     * Collect Weee amounts for the credit memo
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $store = $creditmemo->getStore();

        $totalTax = 0;
        $baseTotalTax = 0;

        $weeeTaxAmount = 0;
        $baseWeeeTaxAmount = 0;
        
        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }

            $weeeAmountExclTax = ($this->_weeeData->getWeeeTaxInclTax($item) -
                $this->_weeeData->getTotalTaxAppliedForWeeeTax($item)) * $item->getQty();
            $totalTax += $weeeAmountExclTax;

            $baseWeeeAmountExclTax = ($this->_weeeData->getBaseWeeeTaxInclTax($item) -
                $this->_weeeData->getBaseTotalTaxAppliedForWeeeTax($item)) * $item->getQty();
            $baseTotalTax += $baseWeeeAmountExclTax;

            $item->setWeeeTaxAppliedRowAmount($weeeAmountExclTax);
            $item->setBaseWeeeTaxAppliedRowAmount($baseWeeeAmountExclTax);
            
            $weeeTaxAmount += $this->_weeeData->getWeeeTaxInclTax($item)* $item->getQty();
            $baseWeeeTaxAmount += $this->_weeeData->getBaseWeeeTaxInclTax($item)* $item->getQty();

            $newApplied = array();
            $applied = $this->_weeeData->getApplied($item);
            foreach ($applied as $one) {
                $one['base_row_amount'] = $one['base_amount'] * $item->getQty();
                $one['row_amount'] = $one['amount'] * $item->getQty();
                $one['base_row_amount_incl_tax'] = $one['base_amount_incl_tax'] * $item->getQty();
                $one['row_amount_incl_tax'] = $one['amount_incl_tax'] * $item->getQty();

                $newApplied[] = $one;
            }
            $this->_weeeData->setApplied($item, $newApplied);

            $item->setWeeeTaxRowDisposition($item->getWeeeTaxDisposition() * $item->getQty());
            $item->setBaseWeeeTaxRowDisposition($item->getBaseWeeeTaxDisposition() * $item->getQty());
        }

        if ($this->_weeeData->includeInSubtotal($store)) {
            $creditmemo->setSubtotal($creditmemo->getSubtotal() + $totalTax);
            $creditmemo->setBaseSubtotal($creditmemo->getBaseSubtotal() + $baseTotalTax);
        }

        $creditmemo->setSubtotalInclTax($creditmemo->getSubtotalInclTax() + $weeeTaxAmount);
        $creditmemo->setBaseSubtotalInclTax($creditmemo->getBaseSubtotalInclTax() + $baseWeeeTaxAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalTax);

        return $this;
    }
}
