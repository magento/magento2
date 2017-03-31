<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * Fields:
 * - orig:
 *   - country_id: UK
 *   - region_id: 1
 *   - postcode: 90034
 * - dest:
 *   - country_id: UK
 *   - region_id: 2
 *   - postcode: 01005
 * - package:
 *   - value: $100
 *   - weight: 1.5 lb
 *   - height: 10"
 *   - width: 10"
 *   - depth: 10"
 * - order:
 *   - total_qty: 10
 *   - subtotal: $100
 * - option
 *   - insurance: true
 *   - handling: $1
 * - table (shiptable)
 *   - condition_name: package_weight
 * - limit
 *   - carrier: carrier code
 *   - method: carrier method
 * - shipping carrier
 *   - specific carrier fields
 *
 * @method int getStoreId()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setStoreId(int $value)
 * @method int getWebsiteId()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setWebsiteId(int $value)
 * @method string getBaseCurrency()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setBaseCurrency(string $value)
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setAllItems(array $items)
 * @method array getAllItems()
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOrigCountryId(string $value)
 * @method string getOrigCountryId()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOrigRegionId(int $value)
 * @method int getOrigRegionId()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOrigPostcode(string $value)
 * @method string getOrigPostcode()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOrigCity(string $value)
 * @method string getOrigCity()
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setDestCountryId(string $value)
 * @method string getDestCountryId()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setDestRegionId(int $value)
 * @method int getDestRegionId()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setDestRegionCode(string $value)
 * @method string getDestRegionCode()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setDestPostcode(string $value)
 * @method string getDestPostcode()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setDestCity(string $value)
 * @method string getDestCity()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setDestStreet(string $value)
 * @method string getDestStreet()
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageValue(float $value)
 * @method float getPackageValue()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageValueWithDiscount(float $value)
 * @method float getPackageValueWithDiscount()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackagePhysicalValue(float $value)
 * @method float getPackagePhysicalValue()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageQty(float $value)
 * @method float getPackageQty()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageWeight(float $value)
 * @method float getPackageWeight()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageHeight(int $value)
 * @method int getPackageHeight()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageWidth(int $value)
 * @method int getPackageWidth()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageDepth(int $value)
 * @method int getPackageDepth()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setPackageCurrency(string $value)
 * @method string getPackageCurrency()
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOrderTotalQty(float $value)
 * @method float getOrderTotalQty()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOrderSubtotal(float $value)
 * @method float getOrderSubtotal()
 *
 * @method boolean getFreeShipping()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setFreeShipping(boolean $flag)
 * @method float getFreeMethodWeight()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setFreeMethodWeight(float $value)
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOptionInsurance(boolean $value)
 * @method boolean getOptionInsurance()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setOptionHandling(float $flag)
 * @method float getOptionHandling()
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setConditionName(array|string $value)
 * @method array|string getConditionName()
 *
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setLimitCarrier(string $value)
 * @method string getLimitCarrier()
 * @method \Magento\Quote\Model\Quote\Address\RateRequest setLimitMethod(string $value)
 * @method string getLimitMethod()
 *
 * @api
 */
class RateRequest extends \Magento\Framework\DataObject
{
}
