<?php
/**
 * Critical messages collection
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_AdminNotification_Model_Resource_Inbox_Collection_Critical
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Resource collection initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_AdminNotification_Model_Inbox', 'Mage_AdminNotification_Model_Resource_Inbox');
    }

    /**
     * @return $this|Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addOrder('notification_id', self::SORT_ORDER_DESC)
            ->addFieldToFilter('is_read', array('neq' => 1))
            ->addFieldToFilter('is_remove', array('neq' => 1))
            ->addFieldToFilter('severity', Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL)
            ->setPageSize(1);
        return $this;
    }
}
