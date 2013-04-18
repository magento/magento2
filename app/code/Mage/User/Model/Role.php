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
 * @package     Mage_User
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin Role Model
 *
 * @method Mage_User_Model_Resource_Role _getResource()
 * @method Mage_User_Model_Resource_Role getResource()
 * @method int getParentId()
 * @method Mage_User_Model_Role setParentId(int $value)
 * @method int getTreeLevel()
 * @method Mage_User_Model_Role setTreeLevel(int $value)
 * @method int getSortOrder()
 * @method Mage_User_Model_Role setSortOrder(int $value)
 * @method string getRoleType()
 * @method Mage_User_Model_Role setRoleType(string $value)
 * @method int getUserId()
 * @method Mage_User_Model_Role setUserId(int $value)
 * @method string getRoleName()
 * @method Mage_User_Model_Role setRoleName(string $value)
 *
 * @category    Mage
 * @package     Mage_User
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_User_Model_Role extends Mage_Core_Model_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'admin_roles';

    protected function _construct()
    {
        $this->_init('Mage_User_Model_Resource_Role');
    }

    /**
     * Update object into database
     *
     * @return Mage_User_Model_Role
     */
    public function update()
    {
        $this->getResource()->update($this);
        return $this;
    }

    /**
     * Retrieve users collection
     *
     * @return Mage_User_Model_Resource_Role_User_Collection
     */
    public function getUsersCollection()
    {
        return Mage::getResourceModel('Mage_User_Model_Resource_Role_User_Collection');
    }

    /**
     * Return users for role
     *
     * @return array
     */
    public function getRoleUsers()
    {
        return $this->getResource()->getRoleUsers($this);
    }
}
