<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleAsyncStomp\Model;

class CustomHandler
{
    /**
     * @param AsyncTestData $simpleDataItem
     */
    public function process($simpleDataItem): void
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'stomp-string-' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param AsyncTestData[] $simpleDataItems
     */
    public function processArray(array $simpleDataItems): void
    {
        foreach ($simpleDataItems as $objItem) {
            file_put_contents(
                $objItem->getTextFilePath(),
                'stomp-array-' . $objItem->getValue() . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    /**
     * @param mixed $simpleDataItems
     */
    public function processMixed($simpleDataItems): void
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
                'stomp-mixed-' . $simpleDataItem->getValue() . PHP_EOL,
                FILE_APPEND
            );
        }
    }
}
