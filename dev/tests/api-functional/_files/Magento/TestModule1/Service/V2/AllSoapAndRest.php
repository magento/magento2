<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Service\V2;

use Magento\TestModule1\Service\V2\Entity\Item;
use Magento\TestModule1\Service\V2\Entity\ItemFactory;

class AllSoapAndRest implements \Magento\TestModule1\Service\V2\AllSoapAndRestInterface
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
        return $this->itemFactory->create()->setId($id)->setName('testProduct1')->setPrice('1');
    }

    /**
     * {@inheritdoc}
     */
    public function items($filters = [], $sortOrder = 'ASC')
    {
        $result = [];
        $firstItem = $this->itemFactory->create()->setId(1)->setName('testProduct1')->setPrice('1');
        $secondItem = $this->itemFactory->create()->setId(2)->setName('testProduct2')->setPrice('2');

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
        return $this->itemFactory->create()->setId(rand())->setName($name)->setPrice('10');
    }

    /**
     * {@inheritdoc}
     */
    public function update(Item $entityItem)
    {
        return $this->itemFactory->create()
            ->setId($entityItem->getId())
            ->setName('Updated' . $entityItem->getName())
            ->setPrice('5');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->itemFactory->create()->setId($id)->setName('testProduct1')->setPrice('1');
    }
}
