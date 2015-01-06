<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule1\Service\V1;

use Magento\TestModule1\Service\V1\Entity\CustomAttributeDataObjectBuilder;
use Magento\TestModule1\Service\V1\Entity\Item;
use Magento\TestModule1\Service\V1\Entity\ItemBuilder;

class AllSoapAndRest implements \Magento\TestModule1\Service\V1\AllSoapAndRestInterface
{
    /**
     * @var ItemBuilder
     */
    protected $itemBuilder;

    /**
     * @var CustomAttributeDataObjectBuilder
     */
    protected $customAttributeDataObjectBuilder;

    /**
     * @param ItemBuilder $itemBuilder
     * @param CustomAttributeDataObjectBuilder $customAttributeNestedDataObjectBuilder
     */
    public function __construct(
        ItemBuilder $itemBuilder,
        CustomAttributeDataObjectBuilder $customAttributeNestedDataObjectBuilder
    ) {
        $this->itemBuilder = $itemBuilder;
        $this->customAttributeDataObjectBuilder = $customAttributeNestedDataObjectBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function item($itemId)
    {
        return $this->itemBuilder->setItemId($itemId)->setName('testProduct1')->create();
    }

    /**
     * {@inheritdoc}
     */
    public function items()
    {
        $result1 = $this->itemBuilder->setItemId(1)->setName('testProduct1')->create();
        $result2 = $this->itemBuilder->setItemId(2)->setName('testProduct2')->create();

        return [$result1, $result2];
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        return $this->itemBuilder->setItemId(rand())->setName($name)->create();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Item $entityItem)
    {
        return $this->itemBuilder->setItemId($entityItem->getItemId())
            ->setName('Updated' . $entityItem->getName())
            ->create();
    }

    public function testOptionalParam($name = null)
    {
        if (is_null($name)) {
            return $this->itemBuilder->setItemId(3)->setName('No Name')->create();
        } else {
            return $this->itemBuilder->setItemId(3)->setName($name)->create();
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
        $customAttributeDataObject = $this->customAttributeDataObjectBuilder
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1)
            ->create();

        $item = $this->itemBuilder
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue')
            ->create();

        return $item;
    }
}
