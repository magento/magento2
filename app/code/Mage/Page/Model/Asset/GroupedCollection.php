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
 * @package     Mage_Page
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * List of page assets that combines into groups ones having the same properties
 */
class Mage_Page_Model_Asset_GroupedCollection extends Mage_Core_Model_Page_Asset_Collection
{
    /**#@+
     * Special properties, enforced to be grouped by
     */
    const PROPERTY_CONTENT_TYPE = 'content_type';
    const PROPERTY_CAN_MERGE    = 'can_merge';
    /**#@-*/

    /**
     * @var Magento_ObjectManager
     */
    private $_objectManager;

    /**
     * @var Mage_Page_Model_Asset_PropertyGroup[]
     */
    private $_groups = array();

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Add an instance, identified by a unique identifier, to the list and to the corresponding group
     *
     * @param string $identifier
     * @param Mage_Core_Model_Page_Asset_AssetInterface $asset
     * @param array $properties
     */
    public function add($identifier, Mage_Core_Model_Page_Asset_AssetInterface $asset, array $properties = array())
    {
        parent::add($identifier, $asset);
        $properties[self::PROPERTY_CONTENT_TYPE] = $asset->getContentType();
        $properties[self::PROPERTY_CAN_MERGE] = $asset instanceof Mage_Core_Model_Page_Asset_MergeableInterface;
        $this->_getGroupFor($properties)->add($identifier, $asset);
    }

    /**
     * Retrieve existing or new group matching the properties
     *
     * @param array $properties
     * @return Mage_Page_Model_Asset_PropertyGroup
     */
    private function _getGroupFor(array $properties)
    {
        /** @var $existingGroup Mage_Page_Model_Asset_PropertyGroup */
        foreach ($this->_groups as $existingGroup) {
            if ($existingGroup->getProperties() == $properties) {
                return $existingGroup;
            }
        }
        /** @var $newGroup Mage_Page_Model_Asset_PropertyGroup */
        $newGroup = $this->_objectManager->create(
            'Mage_Page_Model_Asset_PropertyGroup', array('properties' => $properties)
        );
        $this->_groups[] = $newGroup;
        return $newGroup;
    }

    /**
     * Remove an instance from the list and from the corresponding group
     *
     * @param string $identifier
     */
    public function remove($identifier)
    {
        parent::remove($identifier);
        /** @var $group Mage_Page_Model_Asset_PropertyGroup */
        foreach ($this->_groups as $group) {
            if ($group->has($identifier)) {
                $group->remove($identifier);
                return;
            }
        }
    }

    /**
     * Retrieve groups, containing assets that have the same properties
     *
     * @return Mage_Page_Model_Asset_PropertyGroup[]
     */
    public function getGroups()
    {
        return $this->_groups;
    }
}
