<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleGraphQlQuery\Api;

interface AllSoapAndRestInterface
{
    /**
     * @param int $itemId
     * @return \Magento\TestModuleGraphQlQuery\Api\Data\ItemInterface
     */
    public function item($itemId);
}
