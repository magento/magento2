<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \ArrayObject
     */
    protected $componentsData;

    /**
     * @var UiComponentInterface[]
     */
    protected $components = [];

    /**
     * Constructor
     *
     * @param ArrayObjectFactory $arrayObjectFactory
     */
    public function __construct(ArrayObjectFactory $arrayObjectFactory)
    {
        $this->componentsData = $arrayObjectFactory->create();
    }

    /**
     * Get all UI registered components
     *
     * @return UiComponentInterface[]
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * Get UI component
     *
     * @param string|UiComponentInterface $uniqueKey
     * @return UiComponentInterface
     */
    public function getComponent($uniqueKey)
    {
        $uniqueKey = is_object($uniqueKey) ? $this->getHashKey($uniqueKey) : $uniqueKey;
        return isset($this->components[$uniqueKey]) ? $this->components[$uniqueKey] : null;
    }

    /**
     * Add component
     *
     * @param UiComponentInterface $component
     * @param string|null $uniqueKey
     * @return void
     */
    public function setComponent(UiComponentInterface $component, $uniqueKey = null)
    {
        $uniqueKey = $uniqueKey === null ? $this->getHashKey($component) : $uniqueKey;
        $this->components[$uniqueKey] = $component;
    }

    /**
     * Get all components data
     *
     * @return \ArrayObject
     */
    public function getComponentsData()
    {
        return $this->componentsData;
    }

    /**
     * Get component data
     *
     * @param string|UiComponentInterface $uniqueKey
     * @return mixed
     */
    public function getComponentData($uniqueKey)
    {
        $uniqueKey = is_object($uniqueKey) ? $this->getHashKey($uniqueKey) : $uniqueKey;
        return isset($this->componentsData[$uniqueKey]) ? $this->componentsData[$uniqueKey] : null;
    }

    /**
     * Add component data
     *
     * @param mixed $data
     * @param string|UiComponentInterface $uniqueKey
     * @return void
     */
    public function setComponentData($data, $uniqueKey)
    {
        $uniqueKey = is_object($uniqueKey) ? $this->getHashKey($uniqueKey) : $uniqueKey;
        $this->componentsData[$uniqueKey] = $data;
    }

    /**
     * Get hash of object for the key
     *
     * @param object $object
     * @return string
     */
    protected function getHashKey($object)
    {
        return sprintf('%x', crc32(spl_object_hash($object)));
    }
}
