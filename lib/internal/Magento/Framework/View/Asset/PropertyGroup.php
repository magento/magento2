<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset;

/**
 * Association of arbitrary properties with a list of page assets
 */
class PropertyGroup extends Collection
{
    /**
     * Values that all assets of the group share
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Attributes of individual assets that do not take part in grouping
     *
     * @var array
     */
    private $assetAttributes = [];

    /**
     * Constructor
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Retrieve values of all properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Retrieve value of an individual property
     *
     * @param string $name
     * @return mixed
     */
    public function getProperty($name)
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * Set attributes that apply to a single asset of the group
     *
     * @param string $identifier
     * @param array $attributes
     * @return void
     */
    public function setAssetAttributes($identifier, array $attributes)
    {
        if ($attributes) {
            $this->assetAttributes[$identifier] = $attributes;
        } else {
            unset($this->assetAttributes[$identifier]);
        }
    }

    /**
     * Retrieve attributes that apply to a single asset of the group
     *
     * @param string $identifier
     * @return array
     */
    public function getAssetAttributes($identifier): array
    {
        return $this->assetAttributes[$identifier] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier)
    {
        parent::remove($identifier);
        unset($this->assetAttributes[$identifier]);
    }
}
