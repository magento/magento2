<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
