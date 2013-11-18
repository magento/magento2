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
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\GoogleCheckout\Model\Source\Shipping;

class Carrier implements \Magento\Core\Model\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return array(
            array('label' => __('FedEx'), 'value' => array(
                array('label' => __('Ground'), 'value' => 'FedEx/Ground'),
                array('label' => __('Home Delivery'), 'value' => 'FedEx/Home Delivery'),
                array('label' => __('Express Saver'), 'value' => 'FedEx/Express Saver'),
                array('label' => __('First Overnight'), 'value' => 'FedEx/First Overnight'),
                array('label' => __('Priority Overnight'), 'value' => 'FedEx/Priority Overnight'),
                array('label' => __('Standard Overnight'), 'value' => 'FedEx/Standard Overnight'),
                array('label' => __('2Day'), 'value' => 'FedEx/2Day'),
            )),
            array('label' => __('UPS'), 'value' => array(
                array('label' => __('Next Day Air'), 'value' => 'UPS/Next Day Air'),
                array('label' => __('Next Day Air Early AM'), 'value' => 'UPS/Next Day Air Early AM'),
                array('label' => __('Next Day Air Saver'), 'value' => 'UPS/Next Day Air Saver'),
                array('label' => __('2nd Day Air'), 'value' => 'UPS/2nd Day Air'),
                array('label' => __('2nd Day Air AM'), 'value' => 'UPS/2nd Day Air AM'),
                array('label' => __('3 Day Select'), 'value' => 'UPS/3 Day Select'),
                array('label' => __('Ground'), 'value' => 'UPS/Ground'),
            )),
            array('label' => __('USPS'), 'value' => array(
                array('label' => __('Express Mail'), 'value' => 'USPS/Express Mail'),
                array('label' => __('Priority Mail'), 'value' => 'USPS/Priority Mail'),
                array('label' => __('Parcel Post'), 'value' => 'USPS/Parcel Post'),
                array('label' => __('Media Mail'), 'value' => 'USPS/Media Mail'),
            )),
        );
    }
}
