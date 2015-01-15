<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Service\V2;

use Magento\TestModule1\Service\V2\Entity\Item;
use Magento\TestModule1\Service\V2\Entity\ItemBuilder;

class AllSoapAndRest implements \Magento\TestModule1\Service\V2\AllSoapAndRestInterface
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
        return $this->itemBuilder->setId($id)->setName('testProduct1')->setPrice('1')->create();
    }

    /**
     * {@inheritdoc}
     */
    public function items($filters = [], $sortOrder = 'ASC')
    {
        $result = [];
        $firstItem = $this->itemBuilder->setId(1)->setName('testProduct1')->setPrice('1')->create();
        $secondItem = $this->itemBuilder->setId(2)->setName('testProduct2')->setPrice('2')->create();

        /** Simple filtration implementation */
        if (!empty($filters)) {
            /** @var \Magento\Framework\Api\Filter $filter */
            foreach ($filters as $filter) {
                if ('id' == $filter->getField() && $filter->getValue() == 1) {
                    $result[] = $firstItem;
                } elseif ('id' == $filter->getField() && $filter->getValue() == 2) {
                    $result[] = $secondItem;
                }
            }
        } else {
            /** No filter is specified. */
            $result = [$firstItem, $secondItem];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        return $this->itemBuilder->setId(rand())->setName($name)->setPrice('10')->create();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Item $entityItem)
    {
        return $this->itemBuilder
            ->setId($entityItem->getId())
            ->setName('Updated' . $entityItem->getName())
            ->setPrice('5')->create();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->itemBuilder->setId($id)->setName('testProduct1')->setPrice('1')->create();
    }
}
