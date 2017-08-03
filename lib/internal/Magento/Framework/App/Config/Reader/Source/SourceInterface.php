<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Reader\Source;

/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * @package Magento\Framework\App\Config\Reader\Source
 * @since 2.1.3
 */
interface SourceInterface
{
    /**
     * Retrieve config by scope
     *
     * @param string|null $scopeCode
     * @return array
     * @since 2.1.3
     */
    public function get($scopeCode = null);
}
