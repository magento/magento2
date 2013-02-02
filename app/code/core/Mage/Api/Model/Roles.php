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
 * @package     Mage_Api
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 *
 * @method Mage_Api_Model_Resource_Roles _getResource()
 * @method Mage_Api_Model_Resource_Roles getResource()
 * @method int getParentId()
 * @method Mage_Api_Model_Roles setParentId(int $value)
 * @method int getTreeLevel()
 * @method Mage_Api_Model_Roles setTreeLevel(int $value)
 * @method int getSortOrder()
 * @method Mage_Api_Model_Roles setSortOrder(int $value)
 * @method string getRoleType()
 * @method Mage_Api_Model_Roles setRoleType(string $value)
 * @method int getUserId()
 * @method Mage_Api_Model_Roles setUserId(int $value)
 * @method string getRoleName()
 * @method Mage_Api_Model_Roles setRoleName(string $value)
 * @method string getName()
 * @method Mage_Api_Model_Role setName() setName(string $name)
 *
 * @category    Mage
 * @package     Mage_Api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Roles extends Mage_Core_Model_Abstract
{
    /**
     * Filters
     *
     * @var array
     */
    protected $_filters;


    protected function _construct()
    {
        $this->_init('Mage_Api_Model_Resource_Roles');
    }

    public function update()
    {
        $this->getResource()->update($this);
        return $this;
    }

    public function getUsersCollection()
    {
        return Mage::getResourceModel('Mage_Api_Model_Resource_Roles_User_Collection');
    }

    public function getResourcesTree()
    {
        return $this->_buildResourcesArray(null, null, null, null, true);
    }

    public function getResourcesList()
    {
        return $this->_buildResourcesArray();
    }

    public function getResourcesList2D()
    {
        return $this->_buildResourcesArray(null, null, null, true);
    }

    public function getRoleUsers()
    {
        return $this->getResource()->getRoleUsers($this);
    }

    protected function _buildResourcesArray(
        Varien_Simplexml_Element $resource = null, $parentName = null, $level = 0, $represent2Darray = null,
        $rawNodes = false, $module = 'Mage_Adminhtml'
    ) {
        static $result;

        if (is_null($resource)) {
            $resource = Mage::getSingleton('Mage_Api_Model_Config')->getNode('acl/resources');
            $resourceName = null;
            $level = -1;
        } else {
            $resourceName = $parentName;
            if ($resource->getName()!='title' && $resource->getName()!='sort_order'
                && $resource->getName() != 'children'
            ) {
                $resourceName = (is_null($parentName) ? '' : $parentName.'/').$resource->getName();

                //assigning module for its' children nodes
                if ($resource->getAttribute('module')) {
                    $module = (string)$resource->getAttribute('module');
                }

                if ($rawNodes) {
                    $resource->addAttribute("aclpath", $resourceName);
                }

                $resource->title = Mage::helper($module)->__((string)$resource->title);

                if ( is_null($represent2Darray) ) {
                    $result[$resourceName]['name']  = (string)$resource->title;
                    $result[$resourceName]['level'] = $level;
                } else {
                    $result[] = $resourceName;
                }
            }
        }

        $children = $resource->children();
        if (empty($children)) {
            if ($rawNodes) {
                return $resource;
            } else {
                return $result;
            }
        }
        foreach ($children as $child) {
            $this->_buildResourcesArray($child, $resourceName, $level+1, $represent2Darray, $rawNodes, $module);
        }
        if ($rawNodes) {
            return $resource;
        } else {
            return $result;
        }
    }

    /**
     * Filter data before save
     *
     * @return Mage_Api_Model_Roles
     */
    protected function _beforeSave()
    {
        $this->filter();
        parent::_beforeSave();
        return $this;
    }

    /**
     * Filter set data
     *
     * @return Mage_Api_Model_Roles
     */
    public function filter()
    {
        $data = $this->getData();
        if (!$this->_filters || !$data) {
            return $this;
        }
        /** @var $filter Mage_Core_Model_Input_Filter */
        $filter = Mage::getModel('Mage_Core_Model_Input_Filter');
        $filter->setFilters($this->_filters);
        $this->setData($filter->filter($data));
        return $this;
    }
}
