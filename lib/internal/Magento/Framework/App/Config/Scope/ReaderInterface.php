<?php
/**
 * Scope Reader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Scope;

/**
 * Interface \Magento\Framework\App\Config\Scope\ReaderInterface
 *
 * @since 2.0.0
 */
interface ReaderInterface
{
    /**
     * Read configuration scope
     *
     * @param string|null $scopeType
     * @throws \Exception May throw an exception if the given scope is invalid
     * @return array
     * @since 2.0.0
     */
    public function read($scopeType = null);
}
