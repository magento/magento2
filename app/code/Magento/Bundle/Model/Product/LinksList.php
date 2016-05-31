<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

class LinksList
{
    /**
     * @var \Magento\Bundle\Api\Data\LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Bundle\Api\Data\LinkInterfaceFactory $linkFactory
     * @param Type $type
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Bundle\Api\Data\LinkInterfaceFactory $linkFactory,
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->linkFactory = $linkFactory;
        $this->type = $type;
        $this->dataObjectHelper = $dataObjectHelper;
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

            /** @var \Magento\Bundle\Api\Data\LinkInterface $productLink */
            $productLink = $this->linkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productLink,
                $selection->getData(),
                '\Magento\Bundle\Api\Data\LinkInterface'
            );
            $productLink->setIsDefault($selection->getIsDefault())
                ->setId($selection->getSelectionId())
                ->setQty($selection->getSelectionQty())
                ->setCanChangeQuantity($selection->getSelectionCanChangeQty())
                ->setPrice($selectionPrice)
                ->setPriceType($selectionPriceType);
            $productLinks[] = $productLink;
        }
        return $productLinks;
    }
}
