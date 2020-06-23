<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

/**
 * Interception config writer interface.
 */
interface ConfigWriterInterface
{
    /**
     * Write interception configuration for scopes.
     *
     * @param array $scopes
     * @return void
     */
    public function write($scopes);
}
