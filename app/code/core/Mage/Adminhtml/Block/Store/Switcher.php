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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store switcher block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Store_Switcher extends Mage_Adminhtml_Block_Template
{
    /**
     * @var array
     */
    protected $_storeIds;

    protected $_storeVarName = 'store';

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('Mage_Adminhtml::store/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }

    /**
     * @return Mage_Core_Model_Resource_Website_Collection
     */
    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('Mage_Core_Model_Website')->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if (!is_null($websiteIds)) {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        $websites = Mage::app()->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            foreach ($websites as $websiteId => $website) {
                if (!in_array($websiteId, $websiteIds)) {
                    unset($websites[$websiteId]);
                }
            }
        }
        return $websites;
    }

    /**
     * @return Mage_Core_Model_Resource_Store_Group_Collection
     */
    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::getModel('Mage_Core_Model_Website')->load($website);
        }
        return $website->getGroupCollection();
    }

    /**
     * Get store groups for specified website
     *
     * @param Mage_Core_Model_Website $website
     * @return array
     */
    public function getStoreGroups($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::app()->getWebsite($website);
        }
        return $website->getGroups();
    }

    /**
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function getStoreCollection($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = Mage::getModel('Mage_Core_Model_Store_Group')->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    /**
     * Get store views for specified store group
     *
     * @param Mage_Core_Model_Store_Group $group
     * @return array
     */
    public function getStores($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = Mage::app()->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach ($stores as $storeId => $store) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, $this->_storeVarName => null));
    }

    public function setStoreVarName($varName)
    {
        $this->_storeVarName = $varName;
        return $this;
    }

    public function getStoreId()
    {
        return $this->getRequest()->getParam($this->_storeVarName);
    }

    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    public function isShow()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    protected function _toHtml()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }
}
