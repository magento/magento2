<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page\Config;

/**
 * Page config structure model
 *
 * @api
 * @since 2.0.0
 */
class Structure
{
    /**
     * Map of class properties.
     *
     * @var array
     * @since 2.2.0
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
     * @since 2.0.0
     */
    protected $assets = [];

    /**
     * List asset which will be removed
     *
     * @var array
     * @since 2.0.0
     */
    protected $removeAssets = [];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $title;

    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $metadata = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $elementAttributes = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $removeElementAttributes = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $bodyClasses = [];

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $isBodyClassesDeleted = false;

    /**
     * @param string $element
     * @param string $attributeName
     * @param string $attributeValue
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBodyClasses()
    {
        return $this->isBodyClassesDeleted ? [] : $this->bodyClasses;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getElementAttributes()
    {
        return $this->elementAttributes;
    }

    /**
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $name
     * @param string $content
     * @return $this
     * @since 2.0.0
     */
    public function setMetadata($name, $content)
    {
        $this->metadata[$name] = (string)$content;
        return $this;
    }

    /**
     * @return string[]
     * @since 2.0.0
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string $name
     * @param array $attributes
     * @return $this
     * @since 2.0.0
     */
    public function addAssets($name, $attributes)
    {
        $this->assets[$name] = $attributes;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function removeAssets($name)
    {
        $this->removeAssets[$name] = $name;
        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function processRemoveAssets()
    {
        $this->assets = array_diff_key($this->assets, $this->removeAssets);
        $this->removeAssets = [];
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Reformat 'Page config structure' to array.
     *
     * @return array
     * @since 2.2.0
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
     * Update 'Page config structure' data.
     *
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function populateWithArray(array $data)
    {
        foreach ($this->serializableProperties as $property) {
            $this->{$property} = $this->getArrayValueByKey($property, $data);
        }
    }

    /**
     * Get value from array by key.
     *
     * @param string $key
     * @param array $array
     * @return array
     * @since 2.2.0
     */
    private function getArrayValueByKey($key, array $array)
    {
        return isset($array[$key]) ? $array[$key] : [];
    }
}
