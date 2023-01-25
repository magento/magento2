<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CacheInvalidate\Model;

/**
 * Factory for the \Laminas\Http\Client\Adapter\Socket
 */
class SocketFactory
{
    /**
     * Create object
     *
     * @return \Laminas\Http\Client\Adapter\Socket
     */
    public function create()
    {
        return new \Laminas\Http\Client\Adapter\Socket();
    }
}
