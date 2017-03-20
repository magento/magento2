<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Interface UiComponentInterface
 */
interface UiComponentInterface extends BlockInterface
{
    /**
     * Get component instance name
     *
     * @return string
     */
    public function getName();

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName();

    /**
     * Get component configuration
     *
     * @return array
     */
    public function getConfiguration();

    /**
     * Render component
     *
     * @return string
     */
    public function render();

    /**
     * Add component
     *
     * @param string $name
     * @param UiComponentInterface $component
     * @return void
     */
    public function addComponent($name, UiComponentInterface $component);

    /**
     * @param string $name
     * @return UiComponentInterface
     */
    public function getComponent($name);

    /**
     * Get child components
     *
     * @return UiComponentInterface[]
     */
    public function getChildComponents();

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Get component context
     *
     * @return ContextInterface
     */
    public function getContext();

    /**
     * Render child component
     *
     * @param string $name
     * @return string
     */
    public function renderChildComponent($name);

    /**
     * Component data setter
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public function setData($key, $value = null);

    /**
     * Component data getter
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null);

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare();

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource);

    /**
     * Get Data Source data
     *
     * @return array
     */
    public function getDataSourceData();
}
