<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Factory;

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
