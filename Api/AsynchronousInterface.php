<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleAsyncAmqp\Api;

interface AsynchronousInterface
{
    /**
     * @param string $topic
     * @param string $simpleDataItem
     * @return bool
     */
    public function execute($topic, $simpleDataItem);
}
