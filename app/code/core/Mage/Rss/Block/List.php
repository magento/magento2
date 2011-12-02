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
class Mage_Rss_Block_List extends Mage_Core_Block_Template
{
    const XML_PATH_RSS_METHODS = 'rss';

    protected $_rssFeeds = array();


    /**
     * Add Link elements to head
     *
     * @return Mage_Rss_Block_List
     */
    protected function _prepareLayout()
    {
        $head   = $this->getLayout()->getBlock('head');
        $feeds  = $this->getRssMiscFeeds();
        if ($head && !empty($feeds)) {
            foreach ($feeds as $feed) {
                $head->addRss($feed['label'], $feed['url']);
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve rss feeds
     *
     * @return array
     */
    public function getRssFeeds()
    {
        return empty($this->_rssFeeds) ? false : $this->_rssFeeds;
    }

    /**
     * Add new rss feed
     *
     * @param   string $url
     * @param   string $label
     * @return  Mage_Core_Helper_Abstract
     */
    public function addRssFeed($url, $label, $param = array(), $customerGroup=false)
    {
        $param = array_merge($param, array('store_id' => $this->getCurrentStoreId()));
        if ($customerGroup) {
            $param = array_merge($param, array('cid' => $this->getCurrentCustomerGroupId()));
        }

        $this->_rssFeeds[] = new Varien_Object(
            array(
                'url'   => Mage::getUrl($url, $param),
                'label' => $label
            )
        );
        return $this;
    }

    public function resetRssFeed()
    {
        $this->_rssFeeds=array();
    }

    public function getCurrentStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    public function getCurrentCustomerGroupId()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerGroupId();
    }

    /**
     * Retrieve rss catalog feeds
     *
     * array structure:
     *
     * @return  array
     */
    public function getRssCatalogFeeds()
    {
        $this->resetRssFeed();
        $this->CategoriesRssFeed();
        return $this->getRssFeeds();

/*      $section = Mage::getSingleton('Mage_Adminhtml_Model_Config')->getSections();
        $catalogFeeds = $section->rss->groups->catalog->fields[0];
        $res = array();
        foreach($catalogFeeds as $code => $feed){
            $prefix = self::XML_PATH_RSS_METHODS.'/catalog/'.$code;
            if (!Mage::getStoreConfig($prefix) || $code=='tag') {
                continue;
            }
            $res[$code] = $feed;
        }
        return $res;
*/
    }

    public function getRssMiscFeeds()
    {
        $this->resetRssFeed();
        $this->NewProductRssFeed();
        $this->SpecialProductRssFeed();
        $this->SalesRuleProductRssFeed();
        return $this->getRssFeeds();
    }

    /*
    public function getCatalogRssUrl($code)
    {
        $store_id = Mage::app()->getStore()->getId();
        $param = array('store_id' => $store_id);
        $custGroup = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerGroupId();
        if ($custGroup) {
            $param = array_merge($param, array('cid' => $custGroup));
        }

        return Mage::getUrl('rss/catalog/'.$code, $param);
    }
    */

    public function NewProductRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS.'/catalog/new';
        if((bool)Mage::getStoreConfig($path)){
            $this->addRssFeed($path, $this->__('New Products'));
        }
    }

    public function SpecialProductRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS.'/catalog/special';
        if((bool)Mage::getStoreConfig($path)){
            $this->addRssFeed($path, $this->__('Special Products'),array(),true);
        }
    }

    public function SalesRuleProductRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS.'/catalog/salesrule';
        if((bool)Mage::getStoreConfig($path)){
            $this->addRssFeed($path, $this->__('Coupons/Discounts'),array(),true);
        }
    }

    public function CategoriesRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS.'/catalog/category';
        if((bool)Mage::getStoreConfig($path)){
            $category = Mage::getModel('Mage_Catalog_Model_Category');

            /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
            $treeModel = $category->getTreeModel()->loadNode(Mage::app()->getStore()->getRootCategoryId());
            $nodes = $treeModel->loadChildren()->getChildren();

            $nodeIds = array();
            foreach ($nodes as $node) {
                $nodeIds[] = $node->getId();
            }

            $collection = $category->getCollection()
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('is_anchor')
                ->addAttributeToFilter('is_active',1)
                ->addIdFilter($nodeIds)
                ->addAttributeToSort('name')
                ->load();

            foreach ($collection as $category) {
                $this->addRssFeed('rss/catalog/category', $category->getName(),array('cid'=>$category->getId()));
            }
        }
    }
}
