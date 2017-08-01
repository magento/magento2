<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Control;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ActionPoolInterface
 * @since 2.0.0
 */
interface ActionPoolInterface
{
    /**
     * Add button
     *
     * @param string $key
     * @param array $data
     * @param UiComponentInterface $context
     * @return void
     * @since 2.0.0
     */
    public function add($key, array $data, UiComponentInterface $context);

    /**
     * Remove button
     *
     * @param string $key
     * @return void
     * @since 2.0.0
     */
    public function remove($key);

    /**
     * Update button
     *
     * @param string $key
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function update($key, array $data);

    /**
     * Get toolbar block
     *
     * @return bool|BlockInterface
     * @since 2.0.0
     */
    public function getToolbar();

    /**
     * Add html block
     *
     * @param  string $type
     * @param  string $name
     * @param  array $arguments
     * @return void
     * @since 2.1.0
     */
    public function addHtmlBlock($type, $name = '', array $arguments = []);
}
