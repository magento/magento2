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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer address book xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Customer_Address_List extends Mage_Core_Block_Template
{
    /**
     * Render customer address list xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $addressXmlObj          = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<address></address>');
        $customer               = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();

        $_billingAddssesId      = $customer->getDefaultBilling();
        $_shippingAddssesId     = $customer->getDefaultShipping();
        $billingAddress         = $customer->getAddressById($_billingAddssesId);
        $shippingAddress        = $customer->getAddressById($_shippingAddssesId);

        if ($billingAddress && $billingAddress->getId()) {
            $item = $addressXmlObj->addChild('item');
            $item->addAttribute('label', $this->__('Default Billing Address'));
            $item->addAttribute('default_billing', 1);
            $this->prepareAddressData($billingAddress, $item);
        }
        if ($shippingAddress && $shippingAddress->getId()) {
            $item = $addressXmlObj->addChild('item');
            $item->addAttribute('label', $this->__('Default Shipping Address'));
            $item->addAttribute('default_shipping', 1);
            $this->prepareAddressData($shippingAddress, $item);
        }
        $_additionalAddresses = $customer->getAdditionalAddresses();
        if ($_additionalAddresses) {
            foreach ($_additionalAddresses as $_address) {
                $item = $addressXmlObj->addChild('item');
                $item->addAttribute('label', $this->__('Additional Address'));
                $item->addAttribute('additional', 1);
                $this->prepareAddressData($_address, $item);
            }
        }

        return $addressXmlObj->asNiceXml();
    }

    /**
     * Collect address data to xml node
     * Remove objects from data array and escape data values
     *
     * @param Mage_Customer_Model_Address $address
     * @param Mage_XmlConnect_Model_Simplexml_Element $item
     * @return array
     */
    public function prepareAddressData(
        Mage_Customer_Model_Address $address, Mage_XmlConnect_Model_Simplexml_Element $item
    ) {
        if (!$address) {
            return array();
        }

        $attributes = Mage::helper('Mage_Customer_Helper_Address')->getAttributes();

        $data = array('entity_id' => $address->getId());

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Customer_Model_Attribute */
            if (!$attribute->getIsVisible()) {
                continue;
            }
            if ($attribute->getAttributeCode() == 'country_id') {
                $data['country'] = $address->getCountryModel()->getName();
                $data['country_id'] = $address->getCountryId();
            } else if ($attribute->getAttributeCode() == 'region') {
                $data['region'] = $address->getRegion();
            } else {
                $dataModel = Mage_Customer_Model_Attribute_Data::factory($attribute, $address);
                $value     = $dataModel->outputValue(Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_ONELINE);
                if ($attribute->getFrontendInput() == 'multiline') {
                    $values = $dataModel->outputValue(Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_ARRAY);
                    // explode lines
                    foreach ($values as $k => $v) {
                        $key = sprintf('%s%d', $attribute->getAttributeCode(), $k + 1);
                        $data[$key] = $v;
                    }
                }
                $data[$attribute->getAttributeCode()] = $value;
            }
        }

        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $item->addChild($key, $item->escapeXml($value));
        }
    }
}
