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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Order_Status extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey('rss_order_status_'.$this->getRequest()->getParam('data'));
        $this->setCacheLifetime(600);
    }

    protected function _toHtml()
    {
        $rssObj = Mage::getModel('Mage_Rss_Model_Rss');
        $order = Mage::registry('current_order');
        $title = Mage::helper('Mage_Rss_Helper_Data')->__('Order # %s Notification(s)',$order->getIncrementId());
        $newurl = Mage::getUrl('sales/order/view',array('order_id' => $order->getId()));
        $data = array('title' => $title,
                'description' => $title,
                'link'        => $newurl,
                'charset'     => 'UTF-8',
                );
        $rssObj->_addHeader($data);
        $resourceModel = Mage::getResourceModel('Mage_Rss_Model_Resource_Order');
        $results = $resourceModel->getAllCommentCollection($order->getId());
        if($results){
            foreach($results as $result){
                $urlAppend = 'view';
                $type = $result['entity_type_code'];
                if($type && $type!='order'){
                   $urlAppend = $type;
                }
                $type  = Mage::helper('Mage_Rss_Helper_Data')->__(ucwords($type));
                $title = Mage::helper('Mage_Rss_Helper_Data')->__('Details for %s #%s', $type, $result['increment_id']);

                $description = '<p>'.
                Mage::helper('Mage_Rss_Helper_Data')->__('Notified Date: %s<br/>',$this->formatDate($result['created_at'])).
                Mage::helper('Mage_Rss_Helper_Data')->__('Comment: %s<br/>',$result['comment']).
                '</p>'
                ;
                $url = Mage::getUrl('sales/order/'.$urlAppend,array('order_id' => $order->getId()));
                $data = array(
                    'title'         => $title,
                    'link'          => $url,
                    'description'   => $description,
                );
                $rssObj->_addEntry($data);
            }
        }
        $title = Mage::helper('Mage_Rss_Helper_Data')->__('Order #%s created at %s', $order->getIncrementId(), $this->formatDate($order->getCreatedAt()));
        $url = Mage::getUrl('sales/order/view',array('order_id' => $order->getId()));
        $description = '<p>'.
            Mage::helper('Mage_Rss_Helper_Data')->__('Current Status: %s<br/>',$order->getStatusLabel()).
            Mage::helper('Mage_Rss_Helper_Data')->__('Total: %s<br/>',$order->formatPrice($order->getGrandTotal())).
            '</p>'
        ;
        $data = array(
                    'title'         => $title,
                    'link'          => $url,
                    'description'   => $description,
        );
        $rssObj->_addEntry($data);
        return $rssObj->createRssXml();
    }
}
