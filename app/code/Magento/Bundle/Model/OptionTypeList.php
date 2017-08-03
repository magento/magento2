<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

/**
 * Class \Magento\Bundle\Model\OptionTypeList
 *
 * @since 2.0.0
 */
class OptionTypeList implements \Magento\Bundle\Api\ProductOptionTypeListInterface
{
    /**
     * @var Source\Option\Type
     * @since 2.0.0
     */
    protected $types;

    /**
     * @var \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory
     * @since 2.0.0
     */
    protected $typeFactory;

    /**
     * @param Source\Option\Type $type
     * @param \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory $typeFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
