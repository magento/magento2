<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

class OptionTypeList implements \Magento\Bundle\Api\ProductOptionTypeListInterface
{
    /**
     * @var Source\Option\Type
     */
    protected $types;

    /**
     * @var \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory
     */
    protected $typeFactory;

    /**
     * @param Source\Option\Type $type
     * @param \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory $typeFactory
     */
    public function __construct(
        \Magento\Bundle\Model\Source\Option\Type $type,
        \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory $typeFactory
    ) {
        $this->types = $type;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $optionList = $this->types->toOptionArray();

        /** @var \Magento\Bundle\Api\Data\OptionTypeInterface[] $typeList */
        $typeList = [];
        foreach ($optionList as $option) {
            $typeList[] = $this->typeFactory->create()->setCode($option['value'])->setLabel($option['label']);
        }
        return $typeList;
    }
}
