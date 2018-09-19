<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule5\Service\V2;

/**
 * Both SOAP and REST Version TWO
 * @package Magento\TestModule5\Service\V2
 */
interface AllSoapAndRestInterface
{
    /**
     * Retrieve existing item.
     *
     * @param int $id
     * @return \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function item($id);

    /**
     * Retrieve a list of all existing items.
     *
     * @return \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest[]
     */
    public function items();

    /**
     * Add new item.
     *
     * @param \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item
     * @return \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest
     */
    public function create(\Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item);

    /**
     * Update one item.
     *
     * @param \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item
     * @return \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest
     */
    public function update(\Magento\TestModule5\Service\V2\Entity\AllSoapAndRest $item);

    /**
     * Delete existing item.
     *
     * @param string $id
     * @return \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest
     */
    public function delete($id);
}
