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
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

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
 *   - carrier: ups
 *   - method: 3dp
 * - ups
 *   - pickup: CC
 *   - container: CP
 *   - address: RES
 *
 * @method int getStoreId()
 * @method Mage_Shipping_Model_Rate_Request setStoreId(int $value)
 * @method int getWebsiteId()
 * @method Mage_Shipping_Model_Rate_Request setWebsiteId(int $value)
 * @method string getBaseCurrency()
 * @method Mage_Shipping_Model_Rate_Request setBaseCurrency(string $value)
 *
 * @method Mage_Shipping_Model_Rate_Request setAllItems(array $items)
 * @method array getAllItems()
 *
 * @method Mage_Shipping_Model_Rate_Request setOrigCountryId(string $value)
 * @method string getOrigCountryId()
 * @method Mage_Shipping_Model_Rate_Request setOrigRegionId(int $value)
 * @method int getOrigRegionId()
 * @method Mage_Shipping_Model_Rate_Request setOrigPostcode(string $value)
 * @method string getOrigPostcode()
 * @method Mage_Shipping_Model_Rate_Request setOrigCity(string $value)
 * @method string getOrigCity()
 *
 * @method Mage_Shipping_Model_Rate_Request setDestCountryId(string $value)
 * @method string getDestCountryId()
 * @method Mage_Shipping_Model_Rate_Request setDestRegionId(int $value)
 * @method int getDestRegionId()
 * @method Mage_Shipping_Model_Rate_Request setDestRegionCode(string $value)
 * @method string getDestRegionCode()
 * @method Mage_Shipping_Model_Rate_Request setDestPostcode(string $value)
 * @method string getDestPostcode()
 * @method Mage_Shipping_Model_Rate_Request setDestCity(string $value)
 * @method string getDestCity()
 * @method Mage_Shipping_Model_Rate_Request setDestStreet(string $value)
 * @method string getDestStreet()
 *
 * @method Mage_Shipping_Model_Rate_Request setPackageValue(float $value)
 * @method float getPackageValue()
 * @method Mage_Shipping_Model_Rate_Request setPackageValueWithDiscount(float $value)
 * @method float getPackageValueWithDiscount()
 * @method Mage_Shipping_Model_Rate_Request setPackagePhysicalValue(float $value)
 * @method float getPackagePhysicalValue()
 * @method Mage_Shipping_Model_Rate_Request setPackageQty(float $value)
 * @method float getPackageQty()
 * @method Mage_Shipping_Model_Rate_Request setPackageWeight(float $value)
 * @method float getPackageWeight()
 * @method Mage_Shipping_Model_Rate_Request setPackageHeight(int $value)
 * @method int getPackageHeight()
 * @method Mage_Shipping_Model_Rate_Request setPackageWidth(int $value)
 * @method int getPackageWidth()
 * @method Mage_Shipping_Model_Rate_Request setPackageDepth(int $value)
 * @method int getPackageDepth()
 * @method Mage_Shipping_Model_Rate_Request setPackageCurrency(string $value)
 * @method string getPackageCurrency()
 *
 * @method Mage_Shipping_Model_Rate_Request setOrderTotalQty(float $value)
 * @method float getOrderTotalQty()
 * @method Mage_Shipping_Model_Rate_Request setOrderSubtotal(float $value)
 * @method float getOrderSubtotal()
 *
 * @method boolean getFreeShipping()
 * @method Mage_Shipping_Model_Rate_Request setFreeShipping(boolean $flag)
 * @method float getFreeMethodWeight()
 * @method Mage_Shipping_Model_Rate_Request setFreeMethodWeight(float $value)
 *
 * @method Mage_Shipping_Model_Rate_Request setOptionInsurance(boolean $value)
 * @method boolean getOptionInsurance()
 * @method Mage_Shipping_Model_Rate_Request setOptionHandling(float $flag)
 * @method float getOptionHandling()
 *
 * @method Mage_Shipping_Model_Rate_Request setConditionName(array $value)
 * @method Mage_Shipping_Model_Rate_Request setConditionName(string $value)
 * @method string getConditionName()
 * @method array getConditionName()
 *
 * @method Mage_Shipping_Model_Rate_Request setLimitCarrier(string $value)
 * @method string getLimitCarrier()
 * @method Mage_Shipping_Model_Rate_Request setLimitMethod(string $value)
 * @method string getLimitMethod()
 *
 * @category    Mage
 * @package     Mage_Shipping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shipping_Model_Rate_Request extends Varien_Object
{}
