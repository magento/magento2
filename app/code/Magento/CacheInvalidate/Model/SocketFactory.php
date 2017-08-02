<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

/**
 * Class \Magento\CacheInvalidate\Model\SocketFactory
 *
 * @since 2.0.0
 */
class SocketFactory
{
    /**
     * @return \Zend\Http\Client\Adapter\Socket
     * @since 2.0.0
     */
    public function create()
    {
        return new \Zend\Http\Client\Adapter\Socket();
    }
}
