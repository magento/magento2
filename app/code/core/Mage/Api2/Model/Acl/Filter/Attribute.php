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
 * API2 filter ACL attribute model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection getCollection()
 * @method Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection getResourceCollection()
 * @method Mage_Api2_Model_Resource_Acl_Filter_Attribute getResource()
 * @method Mage_Api2_Model_Resource_Acl_Filter_Attribute _getResource()
 * @method string getUserType()
 * @method Mage_Api2_Model_Acl_Filter_Attribute setUserType() setUserType(string $type)
 * @method string getResourceId()
 * @method Mage_Api2_Model_Acl_Filter_Attribute setResourceId() setResourceId(string $resource)
 * @method string getOperation()
 * @method Mage_Api2_Model_Acl_Filter_Attribute setOperation() setOperation(string $operation)
 * @method string getAllowedAttributes()
 * @method Mage_Api2_Model_Acl_Filter_Attribute setAllowedAttributes() setAllowedAttributes(string $attributes)
 */
class Mage_Api2_Model_Acl_Filter_Attribute extends Mage_Core_Model_Abstract
{
    /**
     * Permissions model
     *
     * @var Mage_Api2_Model_Acl_Filter_Attribute_ResourcePermission
     */
    protected $_permissionModel;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mage_Api2_Model_Resource_Acl_Filter_Attribute');
    }

    /**
     * Get pairs resources-permissions for current attribute
     *
     * @return Mage_Api2_Model_Acl_Filter_Attribute_ResourcePermission
     */
    public function getPermissionModel()
    {
        if (null == $this->_permissionModel) {
            $this->_permissionModel = Mage::getModel('Mage_Api2_Model_Acl_Filter_Attribute_ResourcePermission');
        }
        return $this->_permissionModel;
    }
}
