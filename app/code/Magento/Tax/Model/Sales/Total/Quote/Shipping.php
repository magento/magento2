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
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Sales\Model\Quote\Address;
use Magento\Tax\Model\Calculation;

class Shipping extends CommonTaxCollector
{
    /**
     * Collect tax totals for shipping. The result can be used to calculate discount on shipping
     *
     * @param   Address $address
     * @return  $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $items = $this->_getAddressItems($address);
        if (!$items) {
            return $this;
        }

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($address, false);
        $baseShippingDataObject = $this->getShippingDataObject($address, true);
        if ($shippingDataObject == null || $baseShippingDataObject == null) {
            return $this;
        }

        $quoteDetails = $this->prepareQuoteDetails($address, [$shippingDataObject]);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        $baseQuoteDetails = $this->prepareQuoteDetails($address, [$baseShippingDataObject]);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $address->getQuote()->getStore()->getStoreId());

        $this->processShippingTaxInfo(
            $address,
            $taxDetails->getItems()[self::ITEM_CODE_SHIPPING],
            $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING]
        );

        return $this;
    }
}
