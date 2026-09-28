<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface PoolInterface
 *
 * @api
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
