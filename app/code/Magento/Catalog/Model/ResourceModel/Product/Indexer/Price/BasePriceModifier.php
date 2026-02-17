<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Apply price modifiers to product price indexer which are common for all product types:
 * custom options, catalog rule, catalog inventory modifiers
 */
class BasePriceModifier implements PriceModifierInterface
{
    /**
     * @var PriceModifierInterface[]
     */
    private $priceModifiers;

    /**
     * @param array $priceModifiers
     */
    public function __construct(array $priceModifiers)
    {
        $this->priceModifiers = $priceModifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void
    {
        foreach ($this->priceModifiers as $priceModifier) {
            $priceModifier->modifyPrice($priceTable, $entityIds);
        }
    }
}
