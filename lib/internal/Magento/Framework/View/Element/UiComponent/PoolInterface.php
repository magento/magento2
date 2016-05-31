<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface PoolInterface
 */
interface PoolInterface
{
    /**
     * Register component at pool
     *
     * @param UiComponentInterface $component
     * @return void
     */
    public function register(UiComponentInterface $component);

    /**
     * Retrieve components pool
     *
     * @return UiComponentInterface[]
     */
    public function getComponents();
}
