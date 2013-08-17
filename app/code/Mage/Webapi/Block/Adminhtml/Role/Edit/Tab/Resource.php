<?php
/**
 * Web API role resource tab.
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
 * @method Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource setApiRole() setApiRole(Mage_Webapi_Model_Acl_Role $role)
 * @method Mage_Webapi_Model_Acl_Role getApiRole() getApiRole()
 * @method Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource setSelectedResources() setSelectedResources(array $srIds)
 * @method array getSelectedResources() getSelectedResources()
 */
class Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource extends Mage_Backend_Block_Widget_Form
{
    /**
     * @var Magento_Acl_Loader_Resource_ConfigReaderInterface
     */
    protected $_reader;

    /**
     * @var Mage_Webapi_Model_Resource_Acl_Rule
     */
    protected $_ruleResource;

    /**
     * @var array
     */
    protected $_aclResourcesTree;

    /**
     * @var array
     */
    protected $_selResourcesIds;

    /**
     * Root ACL Resource
     *
     * @var Mage_Core_Model_Acl_RootResource
     */
    protected $_rootResource;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Magento_Acl_Loader_Resource_ConfigReaderInterface $configReader
     * @param Mage_Webapi_Model_Resource_Acl_Rule $ruleResource
     * @param Mage_Core_Model_Acl_RootResource $rootResource
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Magento_Acl_Loader_Resource_ConfigReaderInterface $configReader,
        Mage_Webapi_Model_Resource_Acl_Rule $ruleResource,
        Mage_Core_Model_Acl_RootResource $rootResource,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_reader = $configReader;
        $this->_ruleResource = $ruleResource;
        $this->_rootResource = $rootResource;
    }

    /**
     * Prepare Form.
     *
     * @return Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource
     */
    protected function _prepareForm()
    {
        /** @var $translator Mage_Webapi_Helper_Data */
        $translator = $this->helper('Mage_Webapi_Helper_Data');
        $resources = $this->_reader->getAclResources();
        $this->_aclResourcesTree = $this->_mapResources(
            isset($resources[1]['children']) ? $resources[1]['children'] : array(),
            $translator
        );
        return parent::_prepareForm();
    }

    /**
     * Map resources
     *
     * @param array $resources
     * @param Mage_Webapi_Helper_Data $translator
     * @return array
     */
    protected function _mapResources(array $resources, Mage_Webapi_Helper_Data $translator)
    {
        $output = array();
        foreach ($resources as $resource) {
            $item = array();
            $item['id'] = $resource['id'];
            $item['text'] = $translator->__($resource['title']);
            if (in_array($item['id'], $this->_getSelectedResourcesIds())) {
                $item['checked'] = true;
            }
            $item['children'] = array();
            if (isset($resource['children'])) {
                $item['children'] = $this->_mapResources($resource['children'], $translator);
            }
            $output[] = $item;
        }
        return $output;
    }

    /**
     * Check whether resource access is set to "All".
     *
     * @return bool
     */
    public function isEverythingAllowed()
    {
        return in_array($this->_rootResource->getId(), $this->_getSelectedResourcesIds());
    }

    /**
     * Get ACL resources tree.
     *
     * @return string
     */
    public function getResourcesTree()
    {
        return $this->_aclResourcesTree;
    }

    /**
     * Get selected ACL resources of given API role.
     *
     * @return array
     */
    protected function _getSelectedResourcesIds()
    {
        $apiRole = $this->getApiRole();
        if (null === $this->_selResourcesIds && $apiRole && $apiRole->getId()) {
            $this->_selResourcesIds = $this->_ruleResource->getResourceIdsByRole($apiRole->getRoleId());
        }
        return (array)$this->_selResourcesIds;
    }
}
