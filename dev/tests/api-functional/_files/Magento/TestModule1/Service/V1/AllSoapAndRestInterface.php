<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Service\V1;

use Magento\TestModule1\Service\V1\Entity\Item;

interface AllSoapAndRestInterface
{
    /**
     * @param int $itemId
     * @return \Magento\TestModule1\Service\V1\Entity\Item
     */
    public function item($itemId);

    /**
     * @param string $name
     * @return \Magento\TestModule1\Service\V1\Entity\Item
     */
    public function create($name);

    /**
     * @param \Magento\TestModule1\Service\V1\Entity\Item $entityItem
     * @return \Magento\TestModule1\Service\V1\Entity\Item
     */
    public function update(Item $entityItem);

    /**
     * @return \Magento\TestModule1\Service\V1\Entity\Item[]
     */
    public function items();

    /**
     * @param string $name
     * @return \Magento\TestModule1\Service\V1\Entity\Item
     */
    public function testOptionalParam($name = null);

    /**
     * @param \Magento\TestModule1\Service\V1\Entity\Item $entityItem
     * @return \Magento\TestModule1\Service\V1\Entity\Item
     */
    public function itemAnyType(Item $entityItem);

    /**
     * @return \Magento\TestModule1\Service\V1\Entity\Item
     */
    public function getPreconfiguredItem();
}
