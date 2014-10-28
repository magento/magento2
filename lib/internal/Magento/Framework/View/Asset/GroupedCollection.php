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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Asset;

/**
 * List of page assets that combines into groups ones having the same properties
 */
class GroupedCollection extends Collection
{
    /**#@+
     * Special properties, enforced to be grouped by
     */
    const PROPERTY_CONTENT_TYPE = 'content_type';

    const PROPERTY_CAN_MERGE = 'can_merge';

    /**#@-*/

    /**
     * Property Factory
     *
     * @var \Magento\Framework\View\Asset\PropertyGroupFactory
     */
    protected $propertyFactory;

    /**
     * Property Groups
     *
     * @var PropertyGroup[]
     */
    protected $groups = array();

    /**
     * Constructor
     *
     * @param PropertyGroupFactory $propertyFactory
     */
    public function __construct(PropertyGroupFactory $propertyFactory)
    {
        $this->propertyFactory = $propertyFactory;
    }

    /**
     * Add an instance, identified by a unique identifier, to the list and to the corresponding group
     *
     * @param string $identifier
     * @param AssetInterface $asset
     * @param array $properties
     * @return void
     */
    public function add($identifier, AssetInterface $asset, array $properties = array())
    {
        parent::add($identifier, $asset);
        $properties = array_filter($properties);
        $properties[self::PROPERTY_CONTENT_TYPE] = $asset->getContentType();
        $properties[self::PROPERTY_CAN_MERGE] = $asset instanceof MergeableInterface;
        $this->getGroupFor($properties)->add($identifier, $asset);
    }

    /**
     * Retrieve existing or new group matching the properties
     *
     * @param array $properties
     * @return PropertyGroup
     */
    private function getGroupFor(array $properties)
    {
        /** @var $existingGroup PropertyGroup */
        foreach ($this->groups as $existingGroup) {
            if ($existingGroup->getProperties() == $properties) {
                return $existingGroup;
            }
        }
        /** @var $newGroup PropertyGroup */
        $newGroup = $this->propertyFactory->create(array('properties' => $properties));
        $this->groups[] = $newGroup;
        return $newGroup;
    }

    /**
     * Remove an instance from the list and from the corresponding group
     *
     * @param string $identifier
     * @return void
     */
    public function remove($identifier)
    {
        parent::remove($identifier);
        /** @var PropertyGroup $group  */
        foreach ($this->groups as $group) {
            if ($group->has($identifier)) {
                $group->remove($identifier);
                return;
            }
        }
    }

    /**
     * Retrieve groups, containing assets that have the same properties
     *
     * @return PropertyGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
