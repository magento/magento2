<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\PriceModifier;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Framework\ObjectManagerInterface;

class Composite implements PriceModifierInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $modifiers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $modifiers
     */
    public function __construct(ObjectManagerInterface $objectManager, array $modifiers = [])
    {
        $this->objectManager = $objectManager;
        $this->modifiers = $modifiers;
    }

    /**
     * Modify price
     *
     * @param mixed $price
     * @param Product $product
     * @return mixed
     */
    public function modifyPrice($price, Product $product)
    {
        foreach ($this->modifiers as $modifierClass) {
            $price = $this->objectManager->get($modifierClass)->modifyPrice($price, $product);
        }
        return $price;
    }
}
