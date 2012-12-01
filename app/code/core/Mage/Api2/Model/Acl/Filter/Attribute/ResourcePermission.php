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
 * API2 filter ACL attribute resources permissions model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl_Filter_Attribute_ResourcePermission
    implements Mage_Api2_Model_Acl_PermissionInterface
{
    /**
     * Resources permissions
     *
     * @var array
     */
    protected $_resourcesPermissions;

    /**
     * Filter item value
     *
     * @var string
     */
    protected $_userType;

    /**
     * Flag if resource has entity only attributes
     *
     * @var bool
     */
    protected $_hasEntityOnlyAttributes = false;

    /**
     * Get resources permissions for selected role
     *
     * @return array
     */
    public function getResourcesPermissions()
    {
        if (null === $this->_resourcesPermissions) {
            $rulesPairs = array();

            if ($this->_userType) {
                $allowedAttributes = array();

                /** @var $rules Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection */
                $rules = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection');
                $rules->addFilterByUserType($this->_userType);

                foreach ($rules as $rule) {
                    if (Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL === $rule->getResourceId()) {
                        $rulesPairs[$rule->getResourceId()] = Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_ALLOW;
                    }

                    /** @var $rule Mage_Api2_Model_Acl_Filter_Attribute */
                    if (null !== $rule->getAllowedAttributes()) {
                        $allowedAttributes[$rule->getResourceId()][$rule->getOperation()] = explode(
                            ',', $rule->getAllowedAttributes()
                        );
                    }
                }

                /** @var $config Mage_Api2_Model_Config */
                $config = Mage::getModel('Mage_Api2_Model_Config');

                /** @var $operationSource Mage_Api2_Model_Acl_Filter_Attribute_Operation */
                $operationSource = Mage::getModel('Mage_Api2_Model_Acl_Filter_Attribute_Operation');

                foreach ($config->getResourcesTypes() as $resource) {
                    $resourceUserPrivileges = $config->getResourceUserPrivileges($resource, $this->_userType);

                    if (!$resourceUserPrivileges) { // skip user without any privileges for resource
                        continue;
                    }
                    $operations = $operationSource->toArray();

                    if (empty($resourceUserPrivileges[Mage_Api2_Model_Resource::OPERATION_CREATE])
                        && empty($resourceUserPrivileges[Mage_Api2_Model_Resource::OPERATION_UPDATE])
                    ) {
                        unset($operations[Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_WRITE]);
                    }
                    if (empty($resourceUserPrivileges[Mage_Api2_Model_Resource::OPERATION_RETRIEVE])) {
                        unset($operations[Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ]);
                    }
                    if (!$operations) { // skip resource without any operations allowed
                        continue;
                    }
                    try {
                        /** @var $resourceModel Mage_Api2_Model_Resource */
                        $resourceModel = Mage::getModel($config->getResourceModel($resource));
                        if ($resourceModel) {
                            $resourceModel->setResourceType($resource)
                                ->setUserType($this->_userType);

                            foreach ($operations as $operation => $operationLabel) {
                                if (!$this->_hasEntityOnlyAttributes
                                    && $config->getResourceEntityOnlyAttributes($resource, $this->_userType, $operation)
                                ) {
                                    $this->_hasEntityOnlyAttributes = true;
                                }
                                $availableAttributes = $resourceModel->getAvailableAttributes(
                                    $this->_userType,
                                    $operation
                                );
                                asort($availableAttributes);
                                foreach ($availableAttributes as $attribute => $attributeLabel) {
                                    $status = isset($allowedAttributes[$resource][$operation])
                                        && in_array($attribute, $allowedAttributes[$resource][$operation])
                                            ? Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_ALLOW
                                            : Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_DENY;

                                    $rulesPairs[$resource]['operations'][$operation]['attributes'][$attribute] = array(
                                        'status'    => $status,
                                        'title'     => $attributeLabel
                                    );
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // getModel() throws exception when application is in development mode
                        Mage::logException($e);
                    }
                }
            }
            $this->_resourcesPermissions = $rulesPairs;
        }
        return $this->_resourcesPermissions;
    }

    /**
     * Set filter value
     *
     * Set user type
     *
     * @param string $userType
     * @return Mage_Api2_Model_Acl_Filter_Attribute_ResourcePermission
     */
    public function setFilterValue($userType)
    {
        if (!array_key_exists($userType, Mage_Api2_Model_Auth_User::getUserTypes())) {
            throw new Exception('Unknown user type.');
        }
        $this->_userType = $userType;
        return $this;
    }

    /**
     * Get flag value
     *
     * @return bool
     */
    public function getHasEntityOnlyAttributes()
    {
        return $this->_hasEntityOnlyAttributes;
    }
}
