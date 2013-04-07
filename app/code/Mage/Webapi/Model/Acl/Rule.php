<?php
/**
 * Web API ACL Rules.
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
 *
 * @method int getRoleId() getRoleId()
 * @method Mage_Webapi_Model_Acl_Rule setRoleId() setRoleId(int $value)
 * @method string getResourceId() getResourceId()
 * @method Mage_Webapi_Model_Resource_Acl_Rule getResource() getResource()
 * @method Mage_Webapi_Model_Resource_Acl_Rule_Collection getCollection() getCollection()
 * @method Mage_Webapi_Model_Acl_Rule setResourceId() setResourceId(string $value)
 * @method Mage_Webapi_Model_Acl_Rule setResources() setResources(array $resources)
 * @method array getResources() getResources()
 */
class Mage_Webapi_Model_Acl_Rule extends Mage_Core_Model_Abstract
{
    /**
     * Web API ACL config's resources root ID.
     */
    const API_ACL_RESOURCES_ROOT_ID = 'Mage_Webapi';

    /**
     * Web API ACL resource separator.
     */
    const RESOURCE_SEPARATOR = '/';

    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_init('Mage_Webapi_Model_Resource_Acl_Rule');
    }

    /**
     * Save role resources.
     *
     * @return Mage_Webapi_Model_Acl_Rule
     */
    public function saveResources()
    {
        $this->getResource()->saveResources($this);
        return $this;
    }
}
