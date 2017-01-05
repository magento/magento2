<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
