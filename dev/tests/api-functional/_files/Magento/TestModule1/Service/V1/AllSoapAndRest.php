<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Service\V1;

use Magento\TestModuleMSC\Model\Data\CustomAttributeDataObjectFactory;
use Magento\TestModule1\Service\V1\Entity\Item;
use Magento\TestModule1\Service\V1\Entity\ItemFactory;

class AllSoapAndRest implements \Magento\TestModule1\Service\V1\AllSoapAndRestInterface
{
    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var CustomAttributeDataObjectFactory
     */
    protected $customAttributeDataObjectFactory;

    /**
     * @param ItemFactory $itemFactory
     * @param CustomAttributeDataObjectFactory $customAttributeNestedDataObjectFactory
     */
    public function __construct(
        ItemFactory $itemFactory,
        CustomAttributeDataObjectFactory $customAttributeNestedDataObjectFactory
    ) {
        $this->itemFactory = $itemFactory;
        $this->customAttributeDataObjectFactory = $customAttributeNestedDataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function item($itemId)
    {
        return $this->itemFactory->create()->setItemId($itemId)->setName('testProduct1');
    }

    /**
     * {@inheritdoc}
     */
    public function items()
    {
        $result1 = $this->itemFactory->create()->setItemId(1)->setName('testProduct1');
        $result2 = $this->itemFactory->create()->setItemId(2)->setName('testProduct2');

        return [$result1, $result2];
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        return $this->itemFactory->create()->setItemId(rand())->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Item $entityItem)
    {
        return $this->itemFactory->create()->setItemId($entityItem->getItemId())
            ->setName('Updated' . $entityItem->getName());
    }

    public function testOptionalParam($name = null)
    {
        if ($name === null) {
            return $this->itemFactory->create()->setItemId(3)->setName('No Name');
        } else {
            return $this->itemFactory->create()->setItemId(3)->setName($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function itemAnyType(Item $item)
    {
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreconfiguredItem()
    {
        $customAttributeDataObject = $this->customAttributeDataObjectFactory->create()
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1);

        $item = $this->itemFactory->create()
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue');

        return $item;
    }
}
