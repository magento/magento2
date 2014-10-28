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

/**
 * Calculate items and address amounts including/excluding tax
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Sales\Model\Quote\Address;
use Magento\Sales\Model\Quote\Item\AbstractItem;
use Magento\Tax\Model\Calculation;

class Subtotal extends CommonTaxCollector
{
    /**
     * Calculate tax on product items. The result will be used to determine shipping
     * and discount later.
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

        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());

        //Setup taxable items
        $itemDataObjects = $this->mapItems($address, $priceIncludesTax, false);
        $quoteDetails = $this->prepareQuoteDetails($address, $itemDataObjects);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        $itemDataObjects = $this->mapItems($address, $priceIncludesTax, true);
        $baseQuoteDetails = $this->prepareQuoteDetails($address, $itemDataObjects);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $address->getQuote()->getStore()->getStoreId());

        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);

        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($address, $itemsByType[self::ITEM_TYPE_PRODUCT]);
        }

        return $this;
    }
}
