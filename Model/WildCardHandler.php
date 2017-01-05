<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleAsyncAmqp\Model;

class WildCardHandler
{
    /**
     * @param AsyncTestData $simpleDataItem
     */
    public function methodOne(AsyncTestData $simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodOne - wildcard.queue.one - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * @param AsyncTestData $simpleDataItem
     */
    public function methodTwo(AsyncTestData $simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodTwo - wildcard.queue.two - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param AsyncTestData $simpleDataItem
     */
    public function methodThree(AsyncTestData $simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodThree - wildcard.queue.three - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param AsyncTestData $simpleDataItem
     */
    public function methodFour(AsyncTestData $simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodFour - wildcard.queue.four - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }
}
