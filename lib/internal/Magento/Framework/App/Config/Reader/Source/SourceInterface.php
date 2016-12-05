<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Reader\Source;

/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * @package Magento\Framework\App\Config\Reader\Source
 */
interface SourceInterface
{
    /**
     * Retrieve config by scope
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function get($scopeCode = null);
}
