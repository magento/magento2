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
 * One page checkout billing addresses xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Address_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    /**
     * Render billing addresses xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $billingXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<billing></billing>');

        $addressId = $this->getAddress()->getId();
        $address = $this->getCustomer()->getPrimaryBillingAddress();
        if ($address) {
            $addressId = $address->getId();
        }

        foreach ($this->getCustomer()->getAddresses() as $address) {
            $item = $billingXmlObj->addChild('item');
            if ($addressId == $address->getId()) {
                $item->addAttribute('selected', 1);
            }
            $this->getChild('address_list')->prepareAddressData($address, $item);
            $item->addChild(
                'address_line', $billingXmlObj->escapeXml($address->format('oneline'))
            );
        }

        return $billingXmlObj->asNiceXml();
    }
}
