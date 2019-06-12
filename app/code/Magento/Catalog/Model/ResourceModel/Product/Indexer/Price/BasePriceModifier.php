<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
<<<<<<< HEAD
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = [])
=======
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        foreach ($this->priceModifiers as $priceModifier) {
            $priceModifier->modifyPrice($priceTable, $entityIds);
        }
    }
}
