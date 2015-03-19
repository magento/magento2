<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleMSC\Model;

use Magento\TestModuleMSC\Api\Data\CustomAttributeDataObjectInterfaceFactory;
use Magento\TestModuleMSC\Api\Data\ItemInterfaceFactory;

class AllSoapAndRest implements \Magento\TestModuleMSC\Api\AllSoapAndRestInterface
{
    /**
     * @var ItemInterfaceFactory
     */
    protected $itemDataFactory;

    /**
     * @var CustomAttributeDataObjectInterfaceFactory
     */
    protected $customAttributeDataObjectDataFactory;

    /**
     * @param ItemInterfaceFactory $itemDataFactory
     * @param CustomAttributeDataObjectInterfaceFactory $customAttributeNestedDataObjectFactory
     */
    public function __construct(
        ItemInterfaceFactory $itemDataFactory,
        CustomAttributeDataObjectInterfaceFactory $customAttributeNestedDataObjectFactory
    ) {
        $this->itemDataFactory = $itemDataFactory;
        $this->customAttributeDataObjectDataFactory = $customAttributeNestedDataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function item($itemId)
    {
        return $this->itemDataFactory->create()->setItemId($itemId)->setName('testProduct1');
    }

    /**
     * {@inheritdoc}
     */
    public function items()
    {
        $result1 = $this->itemDataFactory->create()->setItemId(1)->setName('testProduct1');
        $result2 = $this->itemDataFactory->create()->setItemId(2)->setName('testProduct2');

        return [$result1, $result2];
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        return $this->itemDataFactory->create()->setItemId(rand())->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update(\Magento\TestModuleMSC\Api\Data\ItemInterface $entityItem)
    {
        return $this->itemDataFactory->create()->setItemId($entityItem->getItemId())
            ->setName('Updated' . $entityItem->getName());
    }

    public function testOptionalParam($name = null)
    {
        if ($name === null) {
            return $this->itemDataFactory->create()->setItemId(3)->setName('No Name');
        } else {
            return $this->itemDataFactory->create()->setItemId(3)->setName($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function itemAnyType(\Magento\TestModuleMSC\Api\Data\ItemInterface $entityItem)
    {
        return $entityItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreconfiguredItem()
    {
        $customAttributeDataObject = $this->customAttributeDataObjectDataFactory->create()
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1);

        $item = $this->itemDataFactory->create()
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue');

        return $item;
    }
}
