<?php
/**
 * Scope Reader
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Scope;

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
