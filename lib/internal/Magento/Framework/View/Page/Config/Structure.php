<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page\Config;


/**
 * Page config structure model
 */
class Structure
{
    /**
     * Map of class properties.
     *
     * @var array
     */
    private $serializableProperties = [
        'assets',
        'removeAssets',
        'title',
        'metadata',
        'elementAttributes',
        'removeElementAttributes',
        'bodyClasses',
        'isBodyClassesDeleted',
    ];

    /**
     * Information assets elements on page
     *
     * @var array
     */
    protected $assets = [];

    /**
     * List asset which will be removed
     *
     * @var array
     */
    protected $removeAssets = [];

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string[]
     */
    protected $metadata = [];

    /**
     * @var array
     */
    protected $elementAttributes = [];

    /**
     * @var array
     */
    protected $removeElementAttributes = [];

    /**
     * @var array
     */
    protected $bodyClasses = [];

    /**
     * @var bool
     */
    protected $isBodyClassesDeleted = false;

    /**
     * @param string $element
     * @param string $attributeName
     * @param string $attributeValue
     * @return $this
     */
    public function setElementAttribute($element, $attributeName, $attributeValue)
    {
        if (empty($attributeValue)) {
            $this->removeElementAttributes[$element][] = $attributeName;
        } else {
            $this->elementAttributes[$element][$attributeName] = (string)$attributeValue;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function processRemoveElementAttributes()
    {
        foreach ($this->removeElementAttributes as $element => $attributes) {
            foreach ($attributes as $attributeName) {
                unset($this->elementAttributes[$element][$attributeName]);
            }
            if (empty($this->elementAttributes[$element])) {
                unset($this->elementAttributes[$element]);
            }
        }
        $this->removeElementAttributes = [];
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setBodyClass($value)
    {
        if (empty($value)) {
            $this->isBodyClassesDeleted = true;
        } else {
            $this->bodyClasses[] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getBodyClasses()
    {
        return $this->isBodyClassesDeleted ? [] : $this->bodyClasses;
    }

    /**
     * @return array
     */
    public function getElementAttributes()
    {
        return $this->elementAttributes;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $name
     * @param string $content
     * @return $this
     */
    public function setMetadata($name, $content)
    {
        $this->metadata[$name] = (string)$content;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string $name
     * @param array $attributes
     * @return $this
     */
    public function addAssets($name, $attributes)
    {
        $this->assets[$name] = $attributes;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeAssets($name)
    {
        $this->removeAssets[$name] = $name;
        return $this;
    }

    /**
     * @return $this
     */
    public function processRemoveAssets()
    {
        $this->assets = array_diff_key($this->assets, $this->removeAssets);
        $this->removeAssets = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Reformat page config structure to array.
     * It can be possible for serialization and save to cache storage for example.
     *
     * @return array
     */
    public function __toArray()
    {
        $result = [];
        foreach ($this->serializableProperties as $property) {
            $result[$property] = $this->{$property};
        }

        return $result;
    }

    /**
     * Update page config structure data.
     * It can be used for case of set data from cache storage after initialization this class.
     *
     * @param array $data
     * @return void
     */
    public function populateWithArray(array $data)
    {
        foreach ($this->serializableProperties as $property) {
            $this->{$property} = $this->getDataValue($property, $data);
        }
    }

    /**
     * Get value from array by key.
     *
     * @param string $name
     * @param array $data
     * @return array
     */
    private function getDataValue($name, array $data)
    {
        return isset($data[$name]) ? $data[$name] : [];
    }
}
