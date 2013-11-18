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
 * @package     Magento_Shipping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
 * @method \Magento\Shipping\Model\Rate\Request setStoreId(int $value)
 * @method int getWebsiteId()
 * @method \Magento\Shipping\Model\Rate\Request setWebsiteId(int $value)
 * @method string getBaseCurrency()
 * @method \Magento\Shipping\Model\Rate\Request setBaseCurrency(string $value)
 *
 * @method \Magento\Shipping\Model\Rate\Request setAllItems(array $items)
 * @method array getAllItems()
 *
 * @method \Magento\Shipping\Model\Rate\Request setOrigCountryId(string $value)
 * @method string getOrigCountryId()
 * @method \Magento\Shipping\Model\Rate\Request setOrigRegionId(int $value)
 * @method int getOrigRegionId()
 * @method \Magento\Shipping\Model\Rate\Request setOrigPostcode(string $value)
 * @method string getOrigPostcode()
 * @method \Magento\Shipping\Model\Rate\Request setOrigCity(string $value)
 * @method string getOrigCity()
 *
 * @method \Magento\Shipping\Model\Rate\Request setDestCountryId(string $value)
 * @method string getDestCountryId()
 * @method \Magento\Shipping\Model\Rate\Request setDestRegionId(int $value)
 * @method int getDestRegionId()
 * @method \Magento\Shipping\Model\Rate\Request setDestRegionCode(string $value)
 * @method string getDestRegionCode()
 * @method \Magento\Shipping\Model\Rate\Request setDestPostcode(string $value)
 * @method string getDestPostcode()
 * @method \Magento\Shipping\Model\Rate\Request setDestCity(string $value)
 * @method string getDestCity()
 * @method \Magento\Shipping\Model\Rate\Request setDestStreet(string $value)
 * @method string getDestStreet()
 *
 * @method \Magento\Shipping\Model\Rate\Request setPackageValue(float $value)
 * @method float getPackageValue()
 * @method \Magento\Shipping\Model\Rate\Request setPackageValueWithDiscount(float $value)
 * @method float getPackageValueWithDiscount()
 * @method \Magento\Shipping\Model\Rate\Request setPackagePhysicalValue(float $value)
 * @method float getPackagePhysicalValue()
 * @method \Magento\Shipping\Model\Rate\Request setPackageQty(float $value)
 * @method float getPackageQty()
 * @method \Magento\Shipping\Model\Rate\Request setPackageWeight(float $value)
 * @method float getPackageWeight()
 * @method \Magento\Shipping\Model\Rate\Request setPackageHeight(int $value)
 * @method int getPackageHeight()
 * @method \Magento\Shipping\Model\Rate\Request setPackageWidth(int $value)
 * @method int getPackageWidth()
 * @method \Magento\Shipping\Model\Rate\Request setPackageDepth(int $value)
 * @method int getPackageDepth()
 * @method \Magento\Shipping\Model\Rate\Request setPackageCurrency(string $value)
 * @method string getPackageCurrency()
 *
 * @method \Magento\Shipping\Model\Rate\Request setOrderTotalQty(float $value)
 * @method float getOrderTotalQty()
 * @method \Magento\Shipping\Model\Rate\Request setOrderSubtotal(float $value)
 * @method float getOrderSubtotal()
 *
 * @method boolean getFreeShipping()
 * @method \Magento\Shipping\Model\Rate\Request setFreeShipping(boolean $flag)
 * @method float getFreeMethodWeight()
 * @method \Magento\Shipping\Model\Rate\Request setFreeMethodWeight(float $value)
 *
 * @method \Magento\Shipping\Model\Rate\Request setOptionInsurance(boolean $value)
 * @method boolean getOptionInsurance()
 * @method \Magento\Shipping\Model\Rate\Request setOptionHandling(float $flag)
 * @method float getOptionHandling()
 *
 * @method \Magento\Shipping\Model\Rate\Request setConditionName(array $value)
 * @method \Magento\Shipping\Model\Rate\Request setConditionName(string $value)
 * @method string getConditionName()
 * @method array getConditionName()
 *
 * @method \Magento\Shipping\Model\Rate\Request setLimitCarrier(string $value)
 * @method string getLimitCarrier()
 * @method \Magento\Shipping\Model\Rate\Request setLimitMethod(string $value)
 * @method string getLimitMethod()
 *
 * @category    Magento
 * @package     Magento_Shipping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Shipping\Model\Rate;

class Request extends \Magento\Object
{}
