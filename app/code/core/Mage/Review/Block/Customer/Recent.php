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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recent Customer Reviews Block
 *
 * @category   Mage
 * @package    Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Review_Block_Customer_Recent extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customer/list.phtml');

        $this->_collection = Mage::getModel('Mage_Review_Model_Review')->getProductCollection();

        $this->_collection
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addCustomerFilter(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId())
            ->setDateOrder()
            ->setPageSize(5)
            ->load()
            ->addReviewSummary();
    }

    public function count()
    {
        return $this->_collection->getSize();
    }

    protected function _getCollection()
    {
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_getCollection();
    }

    public function getReviewLink()
    {
        return Mage::getUrl('review/customer/view/');
    }

    public function getProductLink()
    {
        return Mage::getUrl('catalog/product/view/');
    }

    public function dateFormat($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    public function getAllReviewsUrl()
    {
        return Mage::getUrl('review/customer');
    }

    public function getReviewUrl($id)
    {
        return Mage::getUrl('review/customer/view', array('id' => $id));
    }
}
