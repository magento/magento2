<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule5\Service\V2;

interface AllSoapAndRestInterface
{
    /**
     * Retrieve existing item.
     *
     * @param int $id
     * @return \Magento\TestModule5\Service\V2\Entity\AllSoapAndRest
     * @throws \Magento\Webapi\Exception
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
