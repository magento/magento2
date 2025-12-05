<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\FilterManager;

/**
 * Filter manager config interface
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get list of factories
     *
     * @return string[]
     */
    public function getFactories();
}
