<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Framework\DB\Select;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;

/**
 * Provides a list of the most expensive configurable options
 * for each possible price type.
 */
class HighestPriceOptionsProvider
{
    /**
     * @var array
     */
    private $products;

    /**
     * @var OptionsCollectionProvider
     */
    private $optionsCollectionProvider;

    /**
     * @var LinkedProductSelectBuilderInterface
     */
    private $linkedProductSelectBuilder;

    /**
     * @param OptionsCollectionProvider $optionsCollectionProvider
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     */
    public function __construct(
        OptionsCollectionProvider $optionsCollectionProvider,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
    ) {
        $this->optionsCollectionProvider = $optionsCollectionProvider;
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
    }

    /**
     * @param int $productId
     *
     * @return ProductInterface[]
     */
    public function getProducts($productId)
    {
        if (!isset($this->products[$productId])) {
            $optionsCollection = $this->optionsCollectionProvider->getCollection(
                $this->prepareLinkedProductSelects(
                    $this->linkedProductSelectBuilder->build($productId)
                )
            );

            $this->products[$productId] = $optionsCollection->getItems();
        }

        return $this->products[$productId];
    }

    /**
     * Modifies given Select objects to make them select
     * products with the highest price.
     *
     * @param Select[] $selects
     *
     * @return Select[]
     */
    private function prepareLinkedProductSelects(array $selects)
    {
        foreach ($selects as $select) {
            $selectOrderByParts = $select->getPart(Select::ORDER);

            if ($selectOrderByParts) {
                $select->reset(Select::ORDER);

                foreach ($selectOrderByParts as $part) {
                    $select->order(
                        implode(' ', [$part[0], Select::SQL_DESC])
                    );
                }
            }
        }

        return $selects;
    }
}
