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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store API
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Core_Model_Store_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve stores list
     *
     * @return array
     */
    public function items()
    {
        // Retrieve stores
        $stores = Mage::app()->getStores();

        // Make result array
        $result = array();
        foreach ($stores as $store) {
            $result[] = array(
                'store_id'    => $store->getId(),
                'code'        => $store->getCode(),
                'website_id'  => $store->getWebsiteId(),
                'group_id'    => $store->getGroupId(),
                'name'        => $store->getName(),
                'sort_order'  => $store->getSortOrder(),
                'is_active'   => $store->getIsActive()
            );
        }

        return $result;
    }

    /**
     * Retrieve store data
     *
     * @param string|int $storeId
     * @return array
     */
    public function info($storeId)
    {
        // Retrieve store info
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        if (!$store->getId()) {
            $this->_fault('store_not_exists');
        }

        // Basic store data
        $result = array();
        $result['store_id'] = $store->getId();
        $result['code'] = $store->getCode();
        $result['website_id'] = $store->getWebsiteId();
        $result['group_id'] = $store->getGroupId();
        $result['name'] = $store->getName();
        $result['sort_order'] = $store->getSortOrder();
        $result['is_active'] = $store->getIsActive();

        return $result;
    }

}
