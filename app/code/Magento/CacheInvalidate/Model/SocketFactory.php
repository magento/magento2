<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

/**
 * Class \Magento\CacheInvalidate\Model\SocketFactory
 *
 */
class SocketFactory
{
    /**
     * @return \Zend\Http\Client\Adapter\Socket
     */
    public function create()
    {
        return new \Zend\Http\Client\Adapter\Socket();
    }
}
