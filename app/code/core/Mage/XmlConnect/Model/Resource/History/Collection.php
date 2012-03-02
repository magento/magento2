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
 * History resource collection
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Resource_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('Mage_XmlConnect_Model_History', 'Mage_XmlConnect_Model_Resource_History');
    }

    /**
     * Filter collection by store
     *
     * @param int $storeId
     * @return Mage_XmlConnect_Model_Resource_History_Collection
     */
    public function addStoreFilter($storeId)
    {
        $this->addFieldToFilter('store_id', $storeId);
        return $this;
    }

    /**
     * Filter collection by application_id
     *
     * @param int $applicationId
     * @return Mage_XmlConnect_Model_Resource_History_Collection
     */
    public function addApplicationFilter($applicationId)
    {
        $this->addFieldToFilter('application_id', $applicationId);
        return $this;
    }
}
