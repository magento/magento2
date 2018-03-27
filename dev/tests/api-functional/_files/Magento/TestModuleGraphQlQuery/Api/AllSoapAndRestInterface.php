<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\TestModuleGraphQlQuery\Api;

interface AllSoapAndRestInterface
{
    /**
     * @param int $itemId
     * @return \Magento\TestModuleGraphQlQuery\Api\Data\ItemInterface
     */
    public function item($itemId) : \Magento\TestModuleGraphQlQuery\Api\Data\ItemInterface;
}
