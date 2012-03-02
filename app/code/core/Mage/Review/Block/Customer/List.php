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
 * @package     Mage_Review
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer Reviews list block
 *
 * @category   Mage
 * @package    Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Review_Block_Customer_List extends Mage_Customer_Block_Account_Dashboard
{
    /**
     * Product reviews collection
     *
     * @var Mage_Review_Model_Resource_Review_Product_Collection
     */
    protected $_collection;

    /**
     * Initializes collection
     */
    protected function _construct()
    {
        $this->_collection = Mage::getModel('Mage_Review_Model_Review')->getProductCollection();
        $this->_collection
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addCustomerFilter(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId())
            ->setDateOrder();
    }

    /**
     * Gets collection items count
     *
     * @return int
     */
    public function count()
    {
        return $this->_collection->getSize();
    }

    /**
     * Get html code for toolbar
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Initializes toolbar
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $toolbar = $this->getLayout()->createBlock('Mage_Page_Block_Html_Pager', 'customer_review_list.toolbar')
            ->setCollection($this->getCollection());

        $this->setChild('toolbar', $toolbar);
        return parent::_prepareLayout();
    }

    /**
     * Get collection
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    protected function _getCollection()
    {
        return $this->_collection;
    }

    /**
     * Get collection
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    public function getCollection()
    {
        return $this->_getCollection();
    }

    /**
     * Get review link
     *
     * @return string
     */
    public function getReviewLink()
    {
        return Mage::getUrl('review/customer/view/');
    }

    /**
     * Get product link
     *
     * @return string
     */
    public function getProductLink()
    {
        return Mage::getUrl('catalog/product/view/');
    }

    /**
     * Format date in short format
     *
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->_getCollection()
            ->load()
            ->addReviewSummary();
        return parent::_beforeToHtml();
    }
}
