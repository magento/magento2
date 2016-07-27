<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleAsyncAmqp\Model;

use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class CustomHandler
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
            'custom-string-' . $simpleDataItem->getValue() . PHP_EOL, FILE_APPEND
        );
    }

    /**
     * @param \Magento\TestModuleAsyncAmqp\Model\AsyncTestData[] $simpleDataItems
     */
    public function processArray($simpleDataItems)
    {
        foreach ($simpleDataItems as $objItem) {
            file_put_contents(
                $objItem->getTextFilePath(),
                'custom-array-' . $objItem->getValue() . PHP_EOL, FILE_APPEND
            );
        }
    }

    /**
     * @param mixed $simpleDataItems
     */
    public function processMixed($simpleDataItems)
    {
        /** @var \Magento\TestModuleAsyncAmqp\Model\AsyncTestData[] $simpleDataItems */
        $simpleDataItems = (array)$simpleDataItems;
        foreach ($simpleDataItems as $simpleDataItem) {
            file_put_contents(
                $simpleDataItem->getTextFilePath(),
                'custom-mixed-' . $simpleDataItem->getValue() . PHP_EOL, FILE_APPEND
            );
        }
    }
}
