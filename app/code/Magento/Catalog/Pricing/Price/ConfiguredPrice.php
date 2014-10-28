<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;

/**
 * Configured price model
 */
class ConfiguredPrice extends FinalPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const PRICE_CODE = self::CONFIGURED_PRICE_CODE;

    /**
     * @var null|ItemInterface
     */
    protected $item;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param ItemInterface $item
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        ItemInterface $item = null
    ) {
        $this->item = $item;
        parent::__construct($saleableItem, $quantity, $calculator);
    }

    /**
     * @param ItemInterface $item
     * @return $this
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get value of configured options
     *
     * @return array
     */
    protected function getOptionsValue()
    {
        $product = $this->item->getProduct();
        $value = 0.;
        $basePrice = parent::getValue();
        $optionIds = $this->item->getOptionByCode('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $itemOption = $this->item->getOptionByCode('option_' . $option->getId());
                    /** @var $group \Magento\Catalog\Model\Product\Option\Type\DefaultType */
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItem($this->item)
                        ->setConfigurationItemOption($itemOption);
                    $value += $group->getOptionPrice($itemOption->getValue(), $basePrice);
                }
            }
        }
        return $value;
    }

    /**
     * Price value of product with configured options
     *
     * @return bool|float
     */
    public function getValue()
    {
        return $this->item ? parent::getValue() + $this->getOptionsValue() : parent::getValue();
    }
}
