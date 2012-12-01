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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 global ACL role resource collection model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Resource_Acl_Global_Role_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mage_Api2_Model_Acl_Global_Role', 'Mage_Api2_Model_Resource_Acl_Global_Role');
    }

    /**
     * Add filter by admin user id and join table with appropriate information
     *
     * @param int $id Admin user id
     * @return Mage_Api2_Model_Resource_Acl_Global_Role_Collection
     */
    public function addFilterByAdminId($id)
    {
        $this->getSelect()
            ->joinInner(
                array('acl_global_role_user' => $this->getTable('api2_acl_user')),
                'main_table.entity_id = acl_global_role_user.role_id',
                array('admin_id' => 'acl_global_role_user.admin_id'))
            ->where('acl_global_role_user.admin_id = ?', $id, Zend_Db::INT_TYPE);

        return $this;
    }
}
