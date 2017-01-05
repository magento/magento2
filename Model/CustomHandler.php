<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleAsyncAmqp\Model;

class CustomHandler
{
    /**
     * @param AsyncTestData $simpleDataItem
     */
    public function process($simpleDataItem)
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'custom-string-' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param AsyncTestData[] $simpleDataItems
     */
    public function processArray($simpleDataItems)
    {
        foreach ($simpleDataItems as $objItem) {
            file_put_contents(
                $objItem->getTextFilePath(),
                'custom-array-' . $objItem->getValue() . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    /**
     * @param mixed $simpleDataItems
     */
    public function processMixed($simpleDataItems)
    {
        /** @var AsyncTestData[] $simpleDataItems */
        $simpleDataItems = is_array($simpleDataItems) ? $simpleDataItems : [$simpleDataItems];
        foreach ($simpleDataItems as $simpleDataItem) {
            if (!($simpleDataItem instanceof AsyncTestData)) {
                file_put_contents(
                    $simpleDataItem->getTextFilePath(),
                    'Invalid data item given. Was expected instance of ' . AsyncTestData::class . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }
            file_put_contents(
                $simpleDataItem->getTextFilePath(),
                'custom-mixed-' . $simpleDataItem->getValue() . PHP_EOL,
                FILE_APPEND
            );
        }
    }
}
