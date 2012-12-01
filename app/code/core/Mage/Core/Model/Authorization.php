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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core Authorization model
 */
class Mage_Core_Model_Authorization
{
    /**
     * ACL policy
     *
     * @var Magento_Authorization_Policy
     */
    protected $_aclPolicy;

    /**
     * ACL role locator
     *
     * @var Magento_Authorization_RoleLocator
     */
    protected $_aclRoleLocator;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_aclPolicy = isset($data['policy']) ? $data['policy'] : $this->_getAclPolicy();
        $this->_aclRoleLocator = isset($data['roleLocator']) ? $data['roleLocator'] : $this->_getAclRoleLocator();
    }

    /**
     * Get ACL policy object
     *
     * @return Magento_Authorization_Policy
     * @throws InvalidArgumentException
     */
    protected function _getAclPolicy()
    {
        $areaConfig = Mage::getConfig()->getAreaConfig();
        $policyClassName = isset($areaConfig['acl']['policy']) ?
            $areaConfig['acl']['policy'] :
            'Magento_Authorization_Policy_Default';

        /** @var $aclBuilder Mage_Core_Model_Acl_Builder */
        $aclBuilder = Mage::getSingleton('Mage_Core_Model_Acl_Builder', array(
            'data' => array('areaConfig' => Mage::getConfig()->getAreaConfig(), 'objectFactory' => Mage::getConfig())
        ));

        /** @var $policyObject Magento_Authorization_Policy **/
        $policyObject = new $policyClassName($aclBuilder->getAcl());
        if (false == ($policyObject instanceof Magento_Authorization_Policy)) {
            throw new InvalidArgumentException($policyClassName . ' is not instance of Magento_Authorization_Policy');
        }

        return $policyObject;
    }

    /**
     * Get ACL role locator
     *
     * @return Magento_Authorization_RoleLocator
     * @throws InvalidArgumentException
     */
    protected function _getAclRoleLocator()
    {
        $areaConfig = Mage::getConfig()->getAreaConfig();
        $roleLocatorClassName = isset($areaConfig['acl']['roleLocator']) ?
            $areaConfig['acl']['roleLocator'] :
            'Magento_Authorization_RoleLocator_Default';

        /** @var $roleLocatorObject Magento_Authorization_RoleLocator **/
        $roleLocatorObject = Mage::getSingleton($roleLocatorClassName);

        if (false == ($roleLocatorObject instanceof Magento_Authorization_RoleLocator)) {
            throw new InvalidArgumentException(
                $roleLocatorClassName . ' is not instance of Magento_Authorization_RoleLocator'
            );
        }
        return $roleLocatorObject;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        return $this->_aclPolicy->isAllowed($this->_aclRoleLocator->getAclRoleId(), $resource, $privilege);
    }

    /**
     * Delete nodes that have "acl" attribute but value is "not allowed"
     *
     * In any case, the "acl" attribute will be unset
     *
     * @param Varien_Simplexml_Element $xml
     */
    public function filterAclNodes(Varien_Simplexml_Element $xml)
    {
        $limitations = $xml->xpath('//*[@acl]') ?: array();
        foreach ($limitations as $node) {
            if (!$this->isAllowed($node['acl'])) {
                $node->unsetSelf();
            } else {
                unset($node['acl']);
            }
        }
    }
}
