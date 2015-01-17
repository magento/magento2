<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Bundle\Api\Data\OptionTypeDataBuilder
     */
    protected $typeBuilder;

    /**
     * @param Source\Option\Type $type
     * @param \Magento\Bundle\Api\Data\OptionTypeDataBuilder $typeBuilder
     */
    public function __construct(
        \Magento\Bundle\Model\Source\Option\Type $type,
        \Magento\Bundle\Api\Data\OptionTypeDataBuilder $typeBuilder
    ) {
        $this->types = $type;
        $this->typeBuilder = $typeBuilder;
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
            $typeList[] = $this->typeBuilder->setCode($option['value'])->setLabel($option['label'])->create();
        }
        return $typeList;
    }
}
