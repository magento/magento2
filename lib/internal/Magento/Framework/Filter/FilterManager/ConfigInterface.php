<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\FilterManager;

/**
 * Filter manager config interface
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get list of factories
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getFactories();
}
