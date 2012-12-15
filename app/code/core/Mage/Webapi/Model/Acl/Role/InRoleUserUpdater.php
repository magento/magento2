<?php
/**
 * Users in role grid "In Role User" column with checkbox updater.
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Acl_Role_InRoleUserUpdater implements Mage_Core_Model_Layout_Argument_UpdaterInterface
{
    /**
     * @var int
     */
    protected $_roleId;

    /**
     * @var Mage_Webapi_Model_Resource_Acl_User
     */
    protected $_userResource;

    /**
     * Constructor.
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Webapi_Model_Resource_Acl_User $userResource
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Webapi_Model_Resource_Acl_User $userResource
    ) {
        $this->_roleId = (int)$request->getParam('role_id');
        $this->_userResource = $userResource;
    }

    /**
     * Init values with users assigned to role.
     *
     * @param array|null $values
     * @return array|null
     */
    public function update($values)
    {
        if ($this->_roleId) {
            $values = $this->_userResource->getRoleUsers($this->_roleId);
        }
        return $values;
    }
}
