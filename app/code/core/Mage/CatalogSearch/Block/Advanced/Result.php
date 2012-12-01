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
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Advanced search result
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogSearch_Block_Advanced_Result extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label'=>Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Home'),
                'title'=>Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label'=>Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Catalog Advanced Search'),
                'link'=>$this->getUrl('*/*/')
            ))->addCrumb('search_result', array(
                'label'=>Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Results')
            ));
        }
        return parent::_prepareLayout();
    }

    public function setListOrders() {
        $category = Mage::getSingleton('Mage_Catalog_Model_Layer')
            ->getCurrentCategory();
        /* @var $category Mage_Catalog_Model_Category */

        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);

        $this->getChildBlock('search_result_list')
            ->setAvailableOrders($availableOrders);
    }

    public function setListModes() {
        $this->getChildBlock('search_result_list')
            ->setModes(array(
                'grid' => Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Grid'),
                'list' => Mage::helper('Mage_CatalogSearch_Helper_Data')->__('List'))
            );
    }

    public function setListCollection() {
        $this->getChildBlock('search_result_list')
           ->setCollection($this->_getProductCollection());
    }

    protected function _getProductCollection(){
        return $this->getSearchModel()->getProductCollection();
    }

    public function getSearchModel()
    {
        return Mage::getSingleton('Mage_CatalogSearch_Model_Advanced');
    }

    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->getSearchModel()->getProductCollection()->getSize();
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }

    public function getFormUrl()
    {
        return Mage::getModel('Mage_Core_Model_Url')
            ->setQueryParams($this->getRequest()->getQuery())
            ->getUrl('*/*/', array('_escape' => true));
    }

    public function getSearchCriterias()
    {
        $searchCriterias = $this->getSearchModel()->getSearchCriterias();
        $middle = ceil(count($searchCriterias) / 2);
        $left = array_slice($searchCriterias, 0, $middle);
        $right = array_slice($searchCriterias, $middle);

        return array('left'=>$left, 'right'=>$right);
    }
}
