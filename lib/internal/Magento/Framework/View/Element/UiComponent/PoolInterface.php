<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface PoolInterface
 * @since 2.0.0
 */
interface PoolInterface
{
    /**
     * Register component at pool
     *
     * @param UiComponentInterface $component
     * @return void
     * @since 2.0.0
     */
    public function register(UiComponentInterface $component);

    /**
     * Retrieve components pool
     *
     * @return UiComponentInterface[]
     * @since 2.0.0
     */
    public function getComponents();
}
