<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule2\Service\V1;

use Magento\TestModule2\Service\V1\Entity\Item;

interface NoWebApiXmlInterface
{
    /**
     * Get an item.
     *
     * @param int $id
     * @return \Magento\TestModule2\Service\V1\Entity\Item
     */
    public function item($id);

    /**
     * Create an item.
     *
     * @param string $name
     * @return \Magento\TestModule2\Service\V1\Entity\Item
     */
    public function create($name);

    /**
     * Update an item.
     *
     * @param \Magento\TestModule2\Service\V1\Entity\Item $item
     * @return \Magento\TestModule2\Service\V1\Entity\Item
     */
    public function update(Item $item);

    /**
     * Retrieve a list of items.
     *
     * @return \Magento\TestModule2\Service\V1\Entity\Item[]
     */
    public function items();
}
