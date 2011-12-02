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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Authorizenet Payment info xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Payment_Method_Info_Authorizenet extends Mage_Paygate_Block_Authorizenet_Info_Cc
{
    /**
     * Add Authorizenet info to order XML object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $orderItemXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function addPaymentInfoToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $orderItemXmlObj)
    {
        $orderItemXmlObj->addAttribute('type', $this->getMethod()->getCode());
        if (!$this->getHideTitle()) {
            $orderItemXmlObj->addAttribute('title', $orderItemXmlObj->xmlAttribute($this->getMethod()->getTitle()));
        }

        $cards = $this->getCards();
        $showCount = count($cards) > 1;

        foreach ($cards as $key => $card) {
            $creditCard = $orderItemXmlObj->addCustomChild('item', null, array(
                'label' => $showCount ? $this->__('Credit Card %s', $key + 1) : $this->__('Credit Card')
            ));
            foreach ($card as $label => $value) {
                $creditCard->addCustomChild('item', implode($this->getValueAsArray($value, true), '\n'), array(
                    'label' => $label
                ));
            }
        }
    }
}
