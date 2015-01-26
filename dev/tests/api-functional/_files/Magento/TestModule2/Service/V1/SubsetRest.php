<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule2\Service\V1;

use Magento\TestModule2\Service\V1\Entity\Item;
use Magento\TestModule2\Service\V1\Entity\ItemBuilder;

class SubsetRest implements \Magento\TestModule2\Service\V1\SubsetRestInterface
{
    /**
     * @var ItemBuilder
     */
    protected $itemBuilder;

    /**
     * @param ItemBuilder $itemBuilder
     */
    public function __construct(ItemBuilder $itemBuilder)
    {
        $this->itemBuilder = $itemBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function item($id)
    {
        return $this->itemBuilder->setId($id)->setName('testItem' . $id)->create();
    }

    /**
     * {@inheritdoc}
     */
    public function items()
    {
        $result1 = $this->itemBuilder->setId(1)->setName('testItem1')->create();

        $result2 = $this->itemBuilder->setId(2)->setName('testItem2')->create();

        return [$result1, $result2];
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        return $this->itemBuilder->setId(rand())->setName($name)->create();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Item $item)
    {
        return $this->itemBuilder->setId($item->getId())->setName('Updated' . $item->getName())->create();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($id)
    {
        return $this->itemBuilder->setId(1)->create();
    }
}
