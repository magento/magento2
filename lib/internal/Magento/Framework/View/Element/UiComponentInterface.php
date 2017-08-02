<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Interface UiComponentInterface
 *
 * @api
 * @since 2.0.0
 */
interface UiComponentInterface extends BlockInterface
{
    /**
     * Get component instance name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Get component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName();

    /**
     * Get component configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getConfiguration();

    /**
     * Render component
     *
     * @return string
     * @since 2.0.0
     */
    public function render();

    /**
     * Add component
     *
     * @param string $name
     * @param UiComponentInterface $component
     * @return void
     * @since 2.0.0
     */
    public function addComponent($name, UiComponentInterface $component);

    /**
     * @param string $name
     * @return UiComponentInterface
     * @since 2.0.0
     */
    public function getComponent($name);

    /**
     * Get child components
     *
     * @return UiComponentInterface[]
     * @since 2.0.0
     */
    public function getChildComponents();

    /**
     * Get template
     *
     * @return string
     * @since 2.0.0
     */
    public function getTemplate();

    /**
     * Get component context
     *
     * @return ContextInterface
     * @since 2.0.0
     */
    public function getContext();

    /**
     * Render child component
     *
     * @param string $name
     * @return string
     * @since 2.0.0
     */
    public function renderChildComponent($name);

    /**
     * Component data setter
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function setData($key, $value = null);

    /**
     * Component data getter
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     * @since 2.0.0
     */
    public function getData($key = '', $index = null);

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 2.0.0
     */
    public function prepare();

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 2.0.0
     */
    public function prepareDataSource(array $dataSource);

    /**
     * Get Data Source data
     *
     * @return array
     * @since 2.0.0
     */
    public function getDataSourceData();
}
