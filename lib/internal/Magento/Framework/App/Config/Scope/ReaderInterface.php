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
 */
interface ReaderInterface
{
    /**
     * Read configuration scope
     *
     * @param string|null $scopeType
     * @throws \Exception May throw an exception if the given scope is invalid
     * @return array
     */
    public function read($scopeType = null);
}
