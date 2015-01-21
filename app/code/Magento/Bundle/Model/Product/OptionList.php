<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

class OptionList
{
    /**
     * @var \Magento\Bundle\Api\Data\OptionDataBuilder
     */
    protected $optionBuilder;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var LinksList
     */
    protected $linkList;

    /**
     * @param Type $type
     * @param \Magento\Bundle\Api\Data\OptionDataBuilder $optionBuilder
     * @param LinksList $linkList
     */
    public function __construct(
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Bundle\Api\Data\OptionDataBuilder $optionBuilder,
        \Magento\Bundle\Model\Product\LinksList $linkList
    ) {
        $this->type = $type;
        $this->optionBuilder = $optionBuilder;
        $this->linkList = $linkList;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function getItems(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $optionCollection = $this->type->getOptionsCollection($product);
        $optionList = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionCollection as $option) {
            $productLinks = $this->linkList->getItems($product, $option->getOptionId());
            $this->optionBuilder->populateWithArray($option->getData())
                ->setOptionId($option->getOptionId())
                ->setTitle(is_null($option->getTitle()) ? $option->getDefaultTitle() : $option->getTitle())
                ->setSku($product->getSku())
                ->setProductLinks($productLinks);
            $optionList[] = $this->optionBuilder->create();
        }
        return $optionList;
    }
}
