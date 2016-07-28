<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleAsyncAmqp\Model;

use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class WildCardHandler
{
    /**
     * @param asyncTestData $simpleDataItem
     */
    public function methodOne($simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodOne - wildcard.queue.one - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * @param asyncTestData $simpleDataItem
     */
    public function methodTwo($simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodTwo - wildcard.queue.two - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param asyncTestData $simpleDataItem
     */
    public function methodThree($simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodThree - wildcard.queue.three - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param asyncTestData $simpleDataItem
     */
    public function methodFour($simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'WildCardHandler::methodFour - wildcard.queue.four - ' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }
}
