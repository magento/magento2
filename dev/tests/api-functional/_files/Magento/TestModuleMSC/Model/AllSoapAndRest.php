<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModuleMSC\Model;

use Magento\TestModuleMSC\Api\Data\CustomAttributeDataObjectDataBuilder;
use Magento\TestModuleMSC\Api\Data\ItemDataBuilder;

class AllSoapAndRest implements \Magento\TestModuleMSC\Api\AllSoapAndRestInterface
{
    /**
     * @var ItemDataBuilder
     */
    protected $itemDataBuilder;

    /**
     * @var CustomAttributeDataObjectDataBuilder
     */
    protected $customAttributeDataObjectDataBuilder;

    /**
     * @param ItemDataBuilder $itemDataBuilder
     * @param CustomAttributeDataObjectDataBuilder $customAttributeNestedDataObjectBuilder
     */
    public function __construct(
        ItemDataBuilder $itemDataBuilder,
        CustomAttributeDataObjectDataBuilder $customAttributeNestedDataObjectBuilder
    ) {
        $this->itemDataBuilder = $itemDataBuilder;
        $this->customAttributeDataObjectDataBuilder = $customAttributeNestedDataObjectBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function item($itemId)
    {
        return $this->itemDataBuilder->setItemId($itemId)->setName('testProduct1')->create();
    }

    /**
     * {@inheritdoc}
     */
    public function items()
    {
        $result1 = $this->itemDataBuilder->setItemId(1)->setName('testProduct1')->create();
        $result2 = $this->itemDataBuilder->setItemId(2)->setName('testProduct2')->create();

        return [$result1, $result2];
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        return $this->itemDataBuilder->setItemId(rand())->setName($name)->create();
    }

    /**
     * {@inheritdoc}
     */
    public function update(\Magento\TestModuleMSC\Api\Data\ItemInterface $entityItem)
    {
        return $this->itemDataBuilder->setItemId($entityItem->getItemId())
            ->setName('Updated' . $entityItem->getName())
            ->create();
    }

    public function testOptionalParam($name = null)
    {
        if (is_null($name)) {
            return $this->itemDataBuilder->setItemId(3)->setName('No Name')->create();
        } else {
            return $this->itemDataBuilder->setItemId(3)->setName($name)->create();
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
        $customAttributeDataObject = $this->customAttributeDataObjectDataBuilder
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1)
            ->create();

        $item = $this->itemDataBuilder
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue')
            ->create();

        return $item;
    }
}
