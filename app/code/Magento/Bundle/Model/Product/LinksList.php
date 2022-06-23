<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Retrieve bundle product links service.
 */
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
     * Get Bundle Product Items Data.
     *
     * @param ProductInterface $product
     * @param int $optionId
     * @return LinkInterface[]
     */
    public function getItems(ProductInterface $product, $optionId)
    {
        $selectionCollection = $this->type->getSelectionsCollection([$optionId], $product);

        $productLinks = [];
        /** @var \Magento\Catalog\Model\Product $selection */
        foreach ($selectionCollection as $selection) {
            $priceType = $product->getPriceType();
            $selectionPriceType = $priceType ? $selection->getSelectionPriceType() : null;
            $selectionPriceValue = $selection->getSelectionPriceValue() < 0
                ? $selection->getPrice()
                : $selection->getSelectionPriceValue();
            $selectionPrice = $priceType ? $selectionPriceValue : $selection->getPrice();

            /** @var LinkInterface $productLink */
            $productLink = $this->linkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productLink,
                $selection->getData(),
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
}
