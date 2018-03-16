<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleGraphQlQuery\Model;

use Magento\TestModuleGraphQlQuery\Api\Data\ItemInterfaceFactory;

class AllSoapAndRest implements \Magento\TestModuleGraphQlQuery\Api\AllSoapAndRestInterface
{
    /**
     * @var ItemInterfaceFactory
     */
    protected $itemDataFactory;

    /**
     * @param ItemInterfaceFactory $itemDataFactory
     */
    public function __construct(
        ItemInterfaceFactory $itemDataFactory
    ) {
        $this->itemDataFactory = $itemDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function item($itemId)
    {
        return $this->itemDataFactory->create()->setItemId($itemId)->setName('testProduct1');
    }
}
