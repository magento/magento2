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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Catalog_NotifyStock extends Mage_Rss_Block_Abstract
{

    /**
     * Cache tag constant for feed notify stock
     *
     * @var string
     */
    const CACHE_TAG = 'block_html_rss_catalog_notifystock';

    /**
     * Constructor
     *
     * @return null
     */
    protected function _construct()
    {
        $this->setCacheTags(array(self::CACHE_TAG));
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey('rss_catalog_notifystock');
        $this->setCacheLifetime(600);
    }

    /**
     * Render RSS
     *
     * @return string
     */
    protected function _toHtml()
    {
        $newUrl = Mage::getUrl('rss/catalog/notifystock');
        $title = Mage::helper('Mage_Rss_Helper_Data')->__('Low Stock Products');

        $rssObj = Mage::getModel('Mage_Rss_Model_Rss');
        $data = array(
            'title'       => $title,
            'description' => $title,
            'link'        => $newUrl,
            'charset'     => 'UTF-8',
        );
        $rssObj->_addHeader($data);

        $configManageStock = (int) Mage::getStoreConfigFlag(
            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $globalNotifyStockQty = (float) Mage::getStoreConfig(
            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY);
        Mage::helper('Mage_Rss_Helper_Data')->disableFlat();
        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = $product->getCollection();
        $stockItemTable = $collection->getTable('cataloginventory_stock_item');

        $adapter = $collection->getConnection();
        $stockItemWhere = '({{table}}.low_stock_date is not null) '
            . " AND ( ({{table}}.use_config_manage_stock=1 AND {$configManageStock}=1)"
            . " AND {{table}}.qty < "
            . $adapter->getCheckSql("{$stockItemTable}.use_config_notify_stock_qty = 1", $globalNotifyStockQty,
                '{{table}}.notify_stock_qty')
            . ' OR ({{table}}.use_config_manage_stock=0 AND {{table}}.manage_stock=1) )';

        $collection
            ->addAttributeToSelect('name', true)
            ->joinTable('cataloginventory_stock_item', 'product_id=entity_id',
                array(
                     'qty'=>'qty',
                     'notify_stock_qty'=>'notify_stock_qty',
                     'use_config' => 'use_config_notify_stock_qty',
                     'low_stock_date' => 'low_stock_date'),
                $stockItemWhere, 'inner')
            ->setOrder('low_stock_date');

        $collection->addAttributeToFilter('status',
            array('in' => Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getVisibleStatusIds()));
        Mage::dispatchEvent('rss_catalog_notify_stock_collection_select', array('collection' => $collection));

        /*
        using resource iterator to load the data one by one
        instead of loading all at the same time. loading all data at the same time can cause the big memory allocation.
        */
        Mage::getSingleton('Mage_Core_Model_Resource_Iterator')->walk(
            $collection->getSelect(),
            array(array($this, 'addNotifyItemXmlCallback')),
            array('rssObj'=> $rssObj, 'product'=>$product, 'globalQty' => $globalNotifyStockQty)
        );

        return $rssObj->createRssXml();
    }

    /**
     * Adds single product to feed
     *
     * @param array $args
     * @return void
     */
    public function addNotifyItemXmlCallback($args)
    {
        $product = $args['product'];
        $product->setData($args['row']);
        $url = Mage::helper('Mage_Adminhtml_Helper_Data')->getUrl('adminhtml/catalog_product/edit/',
            array('id' => $product->getId(), '_secure' => true, '_nosecret' => true));
        $qty = 1 * $product->getQty();
        $description = Mage::helper('Mage_Rss_Helper_Data')->__('%s has reached a quantity of %s.', $product->getName(), $qty);
        $rssObj = $args['rssObj'];
        $data = array(
            'title'         => $product->getName(),
            'link'          => $url,
            'description'   => $description,
        );
        $rssObj->_addEntry($data);
    }
}
