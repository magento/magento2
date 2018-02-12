<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;

class LinksList
{
    /**
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param LinkInterfaceFactory $linkFactory
     * @param Type $type
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        LinkInterfaceFactory $linkFactory,
        Type $type,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->linkFactory = $linkFactory;
        $this->type = $type;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param ProductInterface $product
     * @param int $optionId
     * @return LinkInterface[]
     */
    public function getItems(ProductInterface $product, $optionId)
    {
        $selectionCollection = $this->type->getSelectionsCollection([$optionId], $product);

        $productLinks = [];
        /** @var Product $selection */
        foreach ($selectionCollection as $selection) {
            $selectionPriceType = $product->getPriceType() ? $selection->getSelectionPriceType() : null;
            $selectionPrice = $product->getPriceType() ? $selection->getSelectionPriceValue() : null;

            /** @var LinkInterface $productLink */
            $productLink = $this->linkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productLink,
                $this->getSelectionData($selection),
                LinkInterface::class
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

    /**
     * Get selection product data and remove extension attributes if necessary
     *
     * @param ProductInterface $selection
     * @return array
     */
    private function getSelectionData(ProductInterface $selection)
    {
        $selectionData = $selection->getData();
        if (array_key_exists(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY, $selectionData)) {
            unset($selectionData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        }

        return $selectionData;
    }
}
