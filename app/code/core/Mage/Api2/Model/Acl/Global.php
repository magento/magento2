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
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API Global ACL model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl_Global
{
    /**
     * Check if the operation is allowed on resources of given type type for given user type/role
     *
     * @param Mage_Api2_Model_Auth_User_Abstract $apiUser
     * @param string $resourceType
     * @param string $operation
     * @return boolean
     * @throws Mage_Api2_Exception
     */
    public function isAllowed(Mage_Api2_Model_Auth_User_Abstract $apiUser, $resourceType, $operation)
    {
        // skip user without role, e.g. Customer
        if (null === $apiUser->getRole()) {
            return true;
        }
        /** @var $aclInstance Mage_Api2_Model_Acl */
        $aclInstance = Mage::getSingleton(
            'Mage_Api2_Model_Acl',
            array('resource_type' => $resourceType, 'operation' => $operation)
        );

        if (!$aclInstance->hasRole($apiUser->getRole())) {
            throw new Mage_Api2_Exception('Role not found', Mage_Api2_Model_Server::HTTP_UNAUTHORIZED);
        }
        if (!$aclInstance->has($resourceType)) {
            throw new Mage_Api2_Exception('Resource not found', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        }
        return $aclInstance->isAllowed($apiUser->getRole(), $resourceType, $operation);
    }
}
