<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Scope;

interface ReaderPoolInterface
{
    /**
     * Retrieve reader by scope
     *
     * @param string $scopeType
     * @return ReaderInterface|null
     */
    public function getReader($scopeType);
}
