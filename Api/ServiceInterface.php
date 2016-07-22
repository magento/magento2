<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleSynchronousAmqp\Api;

interface ServiceInterface
{
    /**
     * @param string $simpleDataItem
     * @return string
     */
    public function execute($simpleDataItem);
}
