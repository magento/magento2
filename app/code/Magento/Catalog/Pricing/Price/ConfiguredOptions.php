<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Configured Options model.
 */
class ConfiguredOptions
{
    /**
     * Get value of configured options.
     *
     * @param float $basePrice
     * @param ItemInterface $item
<<<<<<< HEAD
=======
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return float
     */
    public function getItemOptionsValue(float $basePrice, ItemInterface $item): float
    {
        $product = $item->getProduct();
        $value = 0.;
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
<<<<<<< HEAD
                if ($option) {
=======
                if ($option !== null) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                    $itemOption = $item->getOptionByCode('option_' . $option->getId());
                    /** @var $group \Magento\Catalog\Model\Product\Option\Type\DefaultType */
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItem($item)
                        ->setConfigurationItemOption($itemOption);
                    $value += $group->getOptionPrice($itemOption->getValue(), $basePrice);
                }
            }
        }
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $value;
    }
}
