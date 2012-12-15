<?php
/**
 * User role in role grid items updater.
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
class Mage_Webapi_Model_Acl_User_RoleUpdater implements Mage_Core_Model_Layout_Argument_UpdaterInterface
{
    /**
     * @var int
     */
    protected $_userId;

    /**
     * @var Mage_Webapi_Model_Acl_User_Factory
     */
    protected $_userFactory;

    /**
     * Constructor.
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Webapi_Model_Acl_User_Factory $userFactory
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Webapi_Model_Acl_User_Factory $userFactory
    ) {
        $this->_userId = (int)$request->getParam('user_id');
        $this->_userFactory = $userFactory;
    }

    /**
     * Initialize value with role assigned to user.
     *
     * @param int|null $value
     * @return int|null
     */
    public function update($value)
    {
        if ($this->_userId) {
            $value = $this->_userFactory->create()->load($this->_userId)->getRoleId();
        }
        return $value;
    }
}
