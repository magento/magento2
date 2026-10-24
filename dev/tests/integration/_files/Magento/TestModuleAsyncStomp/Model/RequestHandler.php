<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleAsyncStomp\Model;

class RequestHandler
{
    /**
     * @param AsyncTestData $simpleDataItem
     */
    public function process(AsyncTestData $simpleDataItem): void
    {
        file_put_contents(
            $simpleDataItem->getTextFilePath(),
            'InvokedFromRequestHandler-' . $simpleDataItem->getValue() . PHP_EOL,
            FILE_APPEND
        );
    }
}
