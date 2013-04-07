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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Subscriber_Webapi
{
    const NAME_DELIM = ' - ';

    /**
     * @var Mage_Webhook_Model_Subscriber
     */
    protected $_subscriber;

    public function __construct(Mage_Webhook_Model_Subscriber $subscriber)
    {
        $this->_subscriber = $subscriber;
    }

    public function createUserAndRole($email, $key, $secret, $company = '')
    {
        if (!$this->_subscriber->getApiUser()) {
            throw new LogicException('The API user for current subscriber has already been created.');
        }

        // create new role
        $role = $this->_createRole($email, $company);

        // add rules to the new role
        // add webhook rules to the new role
        $resources = array(
            'webhook/create',
            'webhook/get',
            'webhook/update',
            'webhook/delete',
        );

        $acl = $this->_subscriber->getAuthenticationOption('acl');
        if (is_array($acl)) {
            foreach ($acl as $resourceId => $privileges) {
                foreach ($privileges as $privilege => $data) {
                    $resources[] = $resourceId . '/' . $privilege;
                }
            }
        }

        try {
            Mage::getModel('Mage_Webapi_Model_Acl_Rule')
                    ->setRoleId($role->getId())
                    ->setResources($resources)
                    ->saveResources();

            // create new user for new role
            $user = Mage::getModel('Mage_Webapi_Model_Acl_User')
                    ->setRoleId($role->getId())
                    ->setApiKey($key)
                    ->setSecret($secret)
                    ->setCompanyName($company)
                    ->setContactEmail($email)
                    ->save();

            $this->_subscriber->setApiUserId($user->getId());
        } catch (Exception $e) {
            $role->delete();
            if ($user->getId()) {
                $user->delete();
            }
            throw $e;
        }

        return $user;
    }

    protected function _createRole($email, $company)
    {
        $roleName = $this->_createRoleName($email, $company);
        $role     = Mage::getModel('Mage_Webapi_Model_Acl_Role')->load($roleName, 'role_name');

        if ($role->getId()) {
            $uniqString = Mage::helper('Mage_Core_Helper_Data')->uniqHash();
            $roleName   = $this->_createRoleName($email, $company, $uniqString);
        }

        $role = Mage::getModel('Mage_Webapi_Model_Acl_Role')
                ->setRoleName($roleName)
                ->save();

        return $role;
    }

    protected function _createRoleName($email, $prefix = null, $suffix = null)
    {
        $result = '';
        if ($prefix) {
            $result = $prefix . self::NAME_DELIM;
        }

        $result .= $email;

        if ($suffix) {
            $result .= self::NAME_DELIM . $suffix;
        }
        return $result;
    }

    protected function _deactive()
    {
        $this->_subscriber->setStatus(Mage_Webhook_Model_Subscriber::STATUS_INACTIVE);
        $this->_subscriber->save();
    }

    protected function _getAclRuleCollection()
    {
        return Mage::getResourceModel('Mage_Webapi_Model_Resource_Acl_Rule_Collection');
    }

    public function validate()
    {
        /** @var $requiredPermissions array */
        $requiredPermissions = $this->_subscriber->getRequiredPermissions();

        /** @var $user Mage_Webapi_Model_Acl_User */
        $user         = $this->_subscriber->getApiUser();
        $roleId       = $user->getRoleId();
        $resourceData = $this->_getAclRuleCollection()->addFieldToFilter('role_id', $roleId)->load()
                ->toArray(array('resource_id'));
        $resourceData = isset($resourceData['items']) ? $resourceData['items'] : $resourceData;

        // Need to flatten this array for faster checking
        $resourceDataFlat = array();
        foreach ($resourceData as $resourceDataArray) {
            foreach ($resourceDataArray as $individualResource) {
                $resourceDataFlat[] = $individualResource;
            }
        }

        foreach ($requiredPermissions as $requiredPermission) {
            if (!empty($requiredPermission) && !in_array($requiredPermission, $resourceDataFlat)) {
                $this->_deactive();
                return false;
            }
        }

        return true;
    }
}
