<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestModuleAsyncAmqp\Model;

class RequestHandler
{
    /**
     * @param \Magento\TestModuleAsyncAmqp\Model\AsyncTestData $simpleDataItem
     */
    public function process($simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'InvokedFromRequestHandler-' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }
}
