<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Control;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ActionPoolInterface
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
     */
    public function add($key, array $data, UiComponentInterface $context);

    /**
     * Remove button
     *
     * @param string $key
     * @return void
     */
    public function remove($key);

    /**
     * Update button
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function update($key, array $data);

    /**
     * Get toolbar block
     *
     * @return bool|BlockInterface
     */
    public function getToolbar();
}
