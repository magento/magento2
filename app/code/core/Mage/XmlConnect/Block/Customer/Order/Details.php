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
 * Customer order details xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Customer_Order_Details extends Mage_Payment_Block_Info
{
    /**
     * Pre-defined array of methods that we are going to render
     *
     * Core renderer list:
     * - 'ccsave' => Mage_XmlConnect_Block_Checkout_Payment_Method_Info_Ccsave
     * - 'checkmo' => Mage_XmlConnect_Block_Checkout_Payment_Method_Info_Checkmo
     * - 'purchaseorder' => Mage_XmlConnect_Block_Checkout_Payment_Method_Info_Purchaseorder
     * - 'authorizenet' => Mage_XmlConnect_Block_Checkout_Payment_Method_Info_Authorizenet
     * - 'pbridge_authorizenet' => Mage_Paygate_Block_Authorizenet_Info_Cc
     * - 'pbridge_verisign' => Mage_Payment_Block_Info_Cc
     * - 'paypal_express' => Mage_Paypal_Block_Payment_Info
     * - 'paypal_mecl' => Mage_Paypal_Block_Payment_Info
     * - 'pbridge_paypal_direct' => Mage_Paypal_Block_Payment_Info
     * - 'pbridge_paypaluk_direct' => Mage_Paypal_Block_Payment_Info
     * - 'free' => Mage_Payment_Block_Info
     */
    protected $_methodArray = array(
        'ccsave', 'checkmo', 'purchaseorder', 'authorizenet', 'pbridge_authorizenet', 'pbridge_verisign',
        'paypal_express', 'paypal_mecl', 'pbridge_paypal_direct', 'pbridge_paypaluk_direct', 'free'
    );

    /**
     * Render customer orders list xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $orderXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $orderXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<order_details></order_details>');

        $order = $this->_getOrder();

        $orderDate = $this->formatDate($order->getCreatedAtStoreDate(), 'long');
        $orderXmlObj->addCustomChild('order', null, array(
             'label' => $this->__('Order #%s - %s', $order->getRealOrderId(), $order->getStatusLabel()),
             'order_date' => $this->__('Order Date: %s', $orderDate)
        ));
        if (!$order->getIsVirtual()) {
            $shipping = Mage::helper('Mage_XmlConnect_Helper_Data')->trimLineBreaks($order->getShippingAddress()->format('text'));
            $billing  = Mage::helper('Mage_XmlConnect_Helper_Data')->trimLineBreaks($order->getBillingAddress()->format('text'));

            $orderXmlObj->addCustomChild('shipping_address', $shipping);
            $orderXmlObj->addCustomChild('billing_address', $billing);

            if ($order->getShippingDescription()) {
                $shippingMethodDescription = $order->getShippingDescription();
            } else {
                $shippingMethodDescription = Mage::helper('Mage_Sales_Helper_Data')->__('No shipping information available');
            }
            $orderXmlObj->addCustomChild('shipping_method', $shippingMethodDescription);
        }

        $this->_addPaymentMethodInfoToXmlObj($orderXmlObj);

        $itemsBlock = $this->getLayout()->getBlock('xmlconnect.customer.order.items');
        if ($itemsBlock) {
            /** @var $itemsBlock Mage_XmlConnect_Block_Customer_Order_Items */
            $itemsBlock->setItems($order->getItemsCollection());
            $itemsBlock->addItemsToXmlObject($orderXmlObj);
            $totalsBlock = $this->getLayout()->getBlock('xmlconnect.customer.order.totals');
            if ($totalsBlock) {
                $totalsBlock->setOrder($order);
                $totalsBlock->addTotalsToXmlObject($orderXmlObj);
            }
        } else {
            $orderXmlObj->addChild('ordered_items');
        }

        return $orderXmlObj->asNiceXml();
    }

    /**
     * Add payment method info to order xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $orderXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    protected function _addPaymentMethodInfoToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $orderXmlObj)
    {
        $order = $this->_getOrder();

        // TODO: it's need to create an info blocks for other payment methods (including Enterprise)
        $method = $this->helper('Mage_Payment_Helper_Data')->getInfoBlock($order->getPayment())->getMethod();
        $methodCode = $method->getCode();

        $paymentNode = $orderXmlObj->addChild('payment_method');

        if (in_array($methodCode, $this->_methodArray, true)) {
            $blockClassSuffix = str_replace(' ', '_', ucwords(str_replace('_', ' ', $methodCode)));
            $currentBlockRenderer = 'Mage_XmlConnect_Block_Checkout_Payment_Method_Info_' . $blockClassSuffix;
            $currentBlockName = 'xmlconnect.checkout.payment.method.info.' . $methodCode;
            $this->getLayout()->addBlock($currentBlockRenderer, $currentBlockName);
            $this->setChild($methodCode, $currentBlockName);
            $renderer = $this->getChild($methodCode)->setInfo($order->getPayment());
            $renderer->addPaymentInfoToXmlObj($paymentNode);
        } else {
            $paymentNode->addAttribute('type', $methodCode);
            $paymentNode->addAttribute('title', $orderXmlObj->xmlAttribute($method->getTitle()));

            $this->setInfo($order->getPayment());

            $specificInfo = array_merge(
                (array)$order->getPayment()->getAdditionalInformation(),
                (array)$this->getSpecificInformation()
            );
            if (!empty($specificInfo)) {
                foreach ($specificInfo as $label => $value) {
                    if ($value) {
                        $paymentNode->addCustomChild('item', implode($this->getValueAsArray($value, true), '\n'),
                            array('label' => $label)
                        );
                    }
                }
            }
        }

        return $orderXmlObj;
    }

    /**
     * Get order model
     *
     * @through Mage_Core_Exception
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        $order = Mage::registry('current_order');
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->__('Order is not available.'));
        }
        return $order;
    }
}
