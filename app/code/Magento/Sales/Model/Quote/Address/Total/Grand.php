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
namespace Magento\Sales\Model\Quote\Address\Total;

class Grand extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Collect grand total address amount
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        $grandTotal = $address->getGrandTotal();
        $baseGrandTotal = $address->getBaseGrandTotal();

        $totals = array_sum($address->getAllTotalAmounts());
        $baseTotals = array_sum($address->getAllBaseTotalAmounts());

        $address->setGrandTotal($grandTotal + $totals);
        $address->setBaseGrandTotal($baseGrandTotal + $baseTotals);
        return $this;
    }

    /**
     * Add grand total information to address
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  $this
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        $address->addTotal(
            array(
                'code' => $this->getCode(),
                'title' => __('Grand Total'),
                'value' => $address->getGrandTotal(),
                'area' => 'footer'
            )
        );
        return $this;
    }
}
