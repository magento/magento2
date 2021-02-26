<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Helper\Model\Link;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;

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
     * @var Link
     */
    private $linkHelper;

    /**
     * @param LinkInterfaceFactory $linkFactory
     * @param Type $type
     * @param DataObjectHelper $dataObjectHelper
     * @param Link $linkHelper
     */
    public function __construct(
        LinkInterfaceFactory $linkFactory,
        Type $type,
        DataObjectHelper $dataObjectHelper,
        Link  $linkHelper
    ) {
        $this->linkFactory = $linkFactory;
        $this->type = $type;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->linkHelper = $linkHelper;
    }

    /**
     * Bundle Product Items Data
     *
     * @param ProductInterface $product
     * @param int $optionId
     * @return LinkInterface[]
     */
    public function getItems(ProductInterface $product, $optionId)
    {
        $selectionCollection = $this->type->getSelectionsCollection([$optionId], $product);

        $productLinks = [];
        /** @var ProductInterface|\Magento\Catalog\Model\Product $selection */
        foreach ($selectionCollection as $selection) {
            $bundledProductPrice = $selection->getSelectionPriceValue();
            if ($bundledProductPrice <= 0) {
                $bundledProductPrice = $selection->getPrice();
            }
            $selectionPriceType = $product->getPriceType() ? $selection->getSelectionPriceType() : null;
            $selectionPrice = $bundledProductPrice ?: null;

            $selectionData = $this->linkHelper->cleanExtensionAttribute($selection->getData());

            /** @var LinkInterface $productLink */
            $productLink = $this->linkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productLink,
                $selectionData,
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
