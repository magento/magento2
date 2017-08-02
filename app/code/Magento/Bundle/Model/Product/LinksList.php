<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

/**
 * Class \Magento\Bundle\Model\Product\LinksList
 *
 * @since 2.0.0
 */
class LinksList
{
    /**
     * @var \Magento\Bundle\Api\Data\LinkInterfaceFactory
     * @since 2.0.0
     */
    protected $linkFactory;

    /**
     * @var Type
     * @since 2.0.0
     */
    protected $type;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Bundle\Api\Data\LinkInterfaceFactory $linkFactory
     * @param Type $type
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @since 2.0.0
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
     * @since 2.0.0
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
                \Magento\Bundle\Api\Data\LinkInterface::class
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
