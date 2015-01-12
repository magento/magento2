<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\View\Element\UiComponent\Context as RenderContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;

/**
 * Class UiComponentInterface
 */
interface UiComponentInterface extends BlockInterface
{
    /**
     * Update component data
     *
     * @param array $arguments
     * @return string
     */
    public function update(array $arguments = []);

    /**
     * Prepare component data
     *
     * @return void
     */
    public function prepare();

    /**
     * Render component
     *
     * @param array $data
     * @return string
     */
    public function render(array $data = []);

    /**
     * Render label
     *
     * @return mixed|string
     */
    public function renderLabel();

    /**
     * Getting template for rendering content
     *
     * @return string|false
     */
    public function getContentTemplate();

    /**
     * Getting template for rendering label
     *
     * @return string|false
     */
    public function getLabelTemplate();

    /**
     * Getting instance name
     *
     * @return string
     */
    public function getName();

    /**
     * Getting parent name component instance
     *
     * @return string
     */
    public function getParentName();

    /**
     * Get render context
     *
     * @return RenderContext
     */
    public function getRenderContext();

    /**
     * Get elements
     *
     * @return UiComponentInterface[]
     */
    public function getElements();

    /**
     * Set elements
     *
     * @param array $elements
     * @return mixed
     */
    public function setElements(array $elements);

    /**
     * Get configuration builder
     *
     * @return ConfigBuilderInterface
     */
    public function getConfigBuilder();
}
