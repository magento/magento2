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

class Mage_User_Model_Acl_Loader_Rule implements Magento_Acl_Loader
{
    /**
     * @var Mage_Core_Model_Resource
     */
    protected $_resource;

    public function __construct(array $data = array())
    {
        $this->_resource = isset($data['resource'])
            ? $data['resource']
            : Mage::getSingleton('Mage_Core_Model_Resource');
    }

    /**
     * Populate ACL with rules from external storage
     *
     * @param Magento_Acl $acl
     */
    public function populateAcl(Magento_Acl $acl)
    {
        $ruleTable = $this->_resource->getTableName("admin_rule");

        $adapter = $this->_resource->getConnection('read');

        $select = $adapter->select()
            ->from(array('r' => $ruleTable));

        $rulesArr = $adapter->fetchAll($select);

        foreach ($rulesArr as $rule) {
            $role = $rule['role_type'] . $rule['role_id'];
            $resource = $rule['resource_id'];
            $privileges = !empty($rule['privileges']) ? explode(',', $rule['privileges']) : null;

            if ( $rule['permission'] == 'allow') {
                if ($resource === Mage_Backend_Model_Acl_Config::ACL_RESOURCE_ALL) {
                    $acl->allow($role, null, $privileges);
                }
                $acl->allow($role, $resource, $privileges);
            } else if ( $rule['permission'] == 'deny' ) {
                $acl->deny($role, $resource, $privileges);
            }
        }
    }
}
