<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Factory;

/**
 * Interface \Magento\Framework\View\Element\UiComponent\Factory\ComponentFactoryInterface
 *
 * @since 2.2.0
 */
interface ComponentFactoryInterface
{
    /**
     * Create child components
     *
     * @param array $bundleComponents
     * @param array $arguments
     * @return bool|mixed
     * @since 2.2.0
     */
    public function create(array &$bundleComponents, array $arguments = []);
}
