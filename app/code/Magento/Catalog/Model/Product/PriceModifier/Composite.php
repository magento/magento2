<?php
/**
 * Composite price modifier can be used.
 * Any module can add its price modifier to extend price modification from other modules.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\PriceModifier;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Catalog\Model\Product\PriceModifier\Composite
 *
 * @since 2.0.0
 */
class Composite implements PriceModifierInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $modifiers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $modifiers
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function modifyPrice($price, Product $product)
    {
        foreach ($this->modifiers as $modifierClass) {
            $price = $this->objectManager->get($modifierClass)->modifyPrice($price, $product);
        }
        return $price;
    }
}
