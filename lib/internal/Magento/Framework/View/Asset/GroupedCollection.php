<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset;

/**
 * List of page assets that combines into groups ones having the same properties
 *
 * @api
 * @since 100.0.2
 */
class GroupedCollection extends Collection
{
    /**#@+
     * Special properties, enforced to be grouped by
     */
    public const PROPERTY_CONTENT_TYPE = 'content_type';

    public const PROPERTY_CAN_MERGE = 'can_merge';

    /**#@-*/

    /**
     * Attributes that differ per asset and must not split assets into separate groups
     */
    private const ASSET_ATTRIBUTES = ['integrity', 'crossorigin'];

    /**
     * Factory for PropertyGroup
     *
     * @var PropertyGroupFactory
     */
    protected $propertyFactory;

    /**
     * Property Groups
     *
     * @var PropertyGroup[]
     */
    protected $groups = [];

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
    public function add($identifier, AssetInterface $asset, array $properties = [])
    {
        parent::add($identifier, $asset);
        $this->getGroupForAsset($identifier, $this->getFilteredProperties($asset, $properties))
            ->add($identifier, $asset);
    }

    /**
     * Insert asset by after key
     *
     * @param string $identifier
     * @param AssetInterface $asset
     * @param string $key
     * @return void
     */
    public function insert($identifier, AssetInterface $asset, $key)
    {
        parent::insert($identifier, $asset, $key);
        $group = $this->getGroupForAsset($identifier, $this->getFilteredProperties($asset));
        $groupAssets = $group->getAll();

        if (!$groupAssets) {
            //add current asset to group
            $group->add($identifier, $asset);
        }
        $group->insert($identifier, $asset, $key);
    }

    /**
     * Get Asset Properties
     *
     * @param AssetInterface $asset
     * @param array $properties
     * @return array
     */
    public function getFilteredProperties(AssetInterface $asset, $properties = [])
    {
        $properties = array_filter($properties);
        $properties[self::PROPERTY_CONTENT_TYPE] = $asset->getContentType();
        $properties[self::PROPERTY_CAN_MERGE] = $asset instanceof MergeableInterface;

        return $properties;
    }

    /**
     * Retrieve the group for an asset, keeping its per-asset attributes out of the group key
     *
     * @param string $identifier
     * @param array $properties
     * @return PropertyGroup
     */
    private function getGroupForAsset($identifier, array $properties)
    {
        $assetAttributes = [];
        if (isset($properties['attributes']) && is_array($properties['attributes'])) {
            $assetAttributes = array_intersect_key($properties['attributes'], array_flip(self::ASSET_ATTRIBUTES));
            $properties['attributes'] = array_diff_key($properties['attributes'], $assetAttributes);
            if (!$properties['attributes']) {
                unset($properties['attributes']);
            }
        }
        $group = $this->getGroupFor($properties);
        $group->setAssetAttributes($identifier, $assetAttributes);

        return $group;
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
        $newGroup = $this->propertyFactory->create(['properties' => $properties]);
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

    /**
     * Get asset group by content type
     *
     * @param string $contentType
     * @return bool|PropertyGroup
     */
    public function getGroupByContentType($contentType)
    {
        foreach ($this->groups as $group) {
            if ($group->getProperty(self::PROPERTY_CONTENT_TYPE) == $contentType) {
                return $group;
            }
        }

        return false;
    }
}
