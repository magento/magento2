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
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Order_New extends Mage_Core_Block_Template
{

    /**
     * Cache tag constant for feed new orders
     *
     * @var string
     */
    const CACHE_TAG = 'block_html_rss_order_new';

    protected function _construct()
    {
        $this->setCacheTags(array(self::CACHE_TAG));
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey('rss_order_new');
        $this->setCacheLifetime(600);
    }

    protected function _toHtml()
    {
        $order = Mage::getModel('Mage_Sales_Model_Order');
        $passDate = $order->getResource()->formatDate(mktime(0,0,0,date('m'),date('d')-7));

        $newurl = Mage::helper('Mage_Adminhtml_Helper_Data')->getUrl(
            'adminhtml/sales_order',
            array(
                '_secure' => true,
                '_nosecret' => true
            )
        );
        $title = Mage::helper('Mage_Rss_Helper_Data')->__('New Orders');

        $rssObj = Mage::getModel('Mage_Rss_Model_Rss');
        $data = array('title' => $title,
                'description' => $title,
                'link'        => $newurl,
                'charset'     => 'UTF-8',
                );
        $rssObj->_addHeader($data);

        $collection = $order->getCollection()
            ->addAttributeToFilter('created_at', array('date'=>true, 'from'=> $passDate))
            ->addAttributeToSort('created_at','desc')
        ;

        $detailBlock = Mage::getBlockSingleton('Mage_Rss_Block_Order_Details');

        Mage::dispatchEvent('rss_order_new_collection_select', array('collection' => $collection));

        Mage::getSingleton('Mage_Core_Model_Resource_Iterator')
            ->walk($collection->getSelect(), array(array($this, 'addNewOrderXmlCallback')), array('rssObj'=> $rssObj, 'order'=>$order , 'detailBlock' => $detailBlock));

        return $rssObj->createRssXml();
    }

    public function addNewOrderXmlCallback($args)
    {
        $rssObj = $args['rssObj'];
        $order = $args['order'];
        $detailBlock = $args['detailBlock'];
        $order->reset()->load($args['row']['entity_id']);
        if ($order && $order->getId()) {
            $title = Mage::helper('Mage_Rss_Helper_Data')->__('Order #%s created at %s', $order->getIncrementId(), $this->formatDate($order->getCreatedAt()));
            $url = Mage::helper('Mage_Adminhtml_Helper_Data')->getUrl(
                'adminhtml/sales_order/view',
                array(
                    '_secure' => true,
                    'order_id' => $order->getId(),
                    '_nosecret' => true
                )
            );
            $detailBlock->setOrder($order);
            $data = array(
                    'title'         => $title,
                    'link'          => $url,
                    'description'   => $detailBlock->toHtml()
                    );
            $rssObj->_addEntry($data);
        }
    }
}
