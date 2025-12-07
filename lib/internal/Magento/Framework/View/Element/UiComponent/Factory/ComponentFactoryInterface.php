<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent\Factory;

/**
 * Interface \Magento\Framework\View\Element\UiComponent\Factory\ComponentFactoryInterface
 *
 * @api
 */
interface ComponentFactoryInterface
{
    /**
     * Create child components
     *
     * @param array $bundleComponents
     * @param array $arguments
     * @return bool|mixed
     */
    public function create(array &$bundleComponents, array $arguments = []);
}
