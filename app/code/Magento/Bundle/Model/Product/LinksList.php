<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

class LinksList
{
    /**
     * @var \Magento\Bundle\Api\Data\LinkDataBuilder
     */
    protected $linkBuilder;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @param \Magento\Bundle\Api\Data\LinkDataBuilder $linkBuilder
     * @param Type $type
     */
    public function __construct(
        \Magento\Bundle\Api\Data\LinkDataBuilder $linkBuilder,
        \Magento\Bundle\Model\Product\Type $type
    ) {
        $this->linkBuilder = $linkBuilder;
        $this->type = $type;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $optionId
     * @return \Magento\Bundle\Api\Data\LinkInterface[]
     */
    public function getItems(\Magento\Catalog\Api\Data\ProductInterface $product, $optionId)
    {
        $selectionCollection = $this->type->getSelectionsCollection([$optionId], $product);

        $productLinks = [];
        /** @var \Magento\Catalog\Model\Product $selection */
        foreach ($selectionCollection as $selection) {
            $selectionPriceType = $product->getPriceType() ? $selection->getSelectionPriceType() : null;
            $selectionPrice = $product->getPriceType() ? $selection->getSelectionPriceValue() : null;

            $productLinks[] = $this->linkBuilder->populateWithArray($selection->getData())
                ->setIsDefault($selection->getIsDefault())
                ->setQty($selection->getSelectionQty())
                ->setIsDefined($selection->getSelectionCanChangeQty())
                ->setPrice($selectionPrice)
                ->setPriceType($selectionPriceType)
                ->create();
        }
        return $productLinks;
    }
}
