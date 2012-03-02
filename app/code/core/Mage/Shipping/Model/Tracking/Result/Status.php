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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Fields:
 * - carrier: fedex
 * - carrierTitle: Federal Express
 * - tracking: 749011111111
 * - status: delivered
 * - service: home delivery
 * - delivery date: 2007-11-23
 * - delivery time: 16:01:00
 * - delivery location: Frontdoor
 * - signedby: lindy
 *
 * Fields:
 * -carrier: ups cgi
 * -popup: 1
 * -url: http://wwwapps.ups.com/WebTracking/processInputRequest?HTMLVersion=5.0&error_carried=true&tracknums_displayed=5&TypeOfInquiryNumber=T&loc=en_US&InquiryNumber1=$tracking
 *
 * Fields:
 * -carrier: usps
 * -tracksummary: Your item was delivered at 6:50 am on February 6 in Los Angeles CA 90064
 */
class Mage_Shipping_Model_Tracking_Result_Status extends Mage_Shipping_Model_Tracking_Result_Abstract
{
    public function getAllData(){
        return $this->_data;
    }
}
