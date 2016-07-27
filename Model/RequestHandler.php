<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleAsyncAmqp\Model;

use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class RequestHandler
{
    /**
     * @var \Magento\TestModuleAsyncAmqp\Model\AsyncTestData
     */
    private $asyncTestData;

    /**
     * @param \Magento\TestModuleAsyncAmqp\Model\AsyncTestData $simpleDataItem
     */
    public function process($simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'InvokedFromRequestHandler-' . $simpleDataItem->getValue() . PHP_EOL, FILE_APPEND
        );
    }
}
