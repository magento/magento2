<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

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
