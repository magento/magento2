<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule5\Service\V1;

/**
 * Both SOAP and REST Version ONE
 * @package Magento\TestModule5\Service\V1
 */
interface AllSoapAndRestInterface
{
    /**
     * Retrieve an item.
     *
     * @param int $entityId
     * @return \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function item($entityId);

    /**
     * Retrieve all items.
     *
     * @return \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest[]
     */
    public function items();

    /**
     * Create a new item.
     *
     * @param \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest $item
     * @return \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest
     */
    public function create(\Magento\TestModule5\Service\V1\Entity\AllSoapAndRest $item);

    /**
     * Update existing item.
     *
     * @param \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest $entityItem
     * @return \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest
     */
    public function update(\Magento\TestModule5\Service\V1\Entity\AllSoapAndRest $entityItem);

    /**
     * Update existing item.
     *
     * @param string $parentId
     * @param string $entityId
     * @param \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest $entityItem
     * @return \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest
     */
    public function nestedUpdate(
        $parentId,
        $entityId,
        \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest $entityItem
    );
}
