<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule2\Service\V1;

use Magento\TestModule2\Service\V1\Entity\Item;
use Magento\TestModule2\Service\V1\Entity\ItemFactory;

class NoWebApiXml implements \Magento\TestModule2\Service\V1\NoWebApiXmlInterface
{
    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @param ItemFactory $itemFactory
     */
    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function item($id)
    {
        return $this->itemFactory->create()->setId($id)->setName('testProduct1');
    }

    /**
     * {@inheritdoc}
     */
    public function items()
    {
        $result1 = $this->itemFactory->create()->setId(1)->setName('testProduct1');

        $result2 = $this->itemFactory->create()->setId(2)->setName('testProduct2');

        return [$result1, $result2];
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        return $this->itemFactory->create()->setId(rand())->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Item $item)
    {
        return $this->itemFactory->create()->setId($item->getId())->setName('Updated' . $item->getName());
    }
}
