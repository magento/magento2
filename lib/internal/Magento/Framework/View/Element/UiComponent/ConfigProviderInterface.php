<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ConfigProviderInterface
 */
interface ConfigProviderInterface
{
    /**
     * Get all UI registered components
     *
     * @return UiComponentInterface[]
     */
    public function getComponents();

    /**
     * Get UI component
     *
     * @param string|UiComponentInterface $uniqueKey
     * @return UiComponentInterface
     */
    public function getComponent($uniqueKey);

    /**
     * Add component
     *
     * @param UiComponentInterface $component
     * @param string|null $uniqueKey
     * @return void
     */
    public function setComponent(UiComponentInterface $component, $uniqueKey = null);

    /**
     * Get component data
     *
     * @param string|UiComponentInterface $uniqueKey
     * @return mixed
     */
    public function getComponentData($uniqueKey);

    /**
     * Add component data
     *
     * @param mixed $data
     * @param string|UiComponentInterface $uniqueKey
     * @return void
     */
    public function setComponentData($data, $uniqueKey);

    /**
     * Get all components data
     *
     * @return \ArrayObject
     */
    public function getComponentsData();
}
