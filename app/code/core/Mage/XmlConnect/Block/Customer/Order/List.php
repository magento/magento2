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
 * Customer orders history xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Customer_Order_List extends Mage_Core_Block_Template
{
    /**
     * Orders count limit
     */
    const ORDERS_LIST_LIMIT = 10;

    /**
     * Render customer orders list xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $ordersXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<orders></orders>');

        $orders = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'customer_id', Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getId()
            )
            ->addFieldToFilter(
                'state', array('in' => Mage::getSingleton('Mage_Sales_Model_Order_Config')->getVisibleOnFrontStates())
            )
            ->setOrder('created_at', 'desc');

        $orders->getSelect()->limit(self::ORDERS_LIST_LIMIT, 0);
        $orders->load();

        if (sizeof($orders->getItems())) {
            foreach ($orders as $_order) {
                $item = $ordersXmlObj->addChild('item');
                $item->addChild('entity_id', $_order->getId());
                $item->addChild('number', $_order->getRealOrderId());
                $item->addChild('date', $this->formatDate($_order->getCreatedAtStoreDate()));
                if ($_order->getShippingAddress()) {
                    $item->addChild('ship_to', $ordersXmlObj->escapeXml($_order->getShippingAddress()->getName()));
                }
                $item->addChild('total', $_order->getOrderCurrency()->formatPrecision(
                    $_order->getGrandTotal(), 2, array(), false, false
                ));
                $item->addChild('status', $_order->getStatusLabel());
            }
        }
        return $ordersXmlObj->asNiceXml();
    }
}
