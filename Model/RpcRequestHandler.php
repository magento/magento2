<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleSynchronousAmqp\Model;

class RpcRequestHandler
{
    /**
     * @param string $simpleDataItem
     * @return string
     */
    public function process($simpleDataItem)
    {
        return $simpleDataItem . ' processed by RPC handler';
    }
}
