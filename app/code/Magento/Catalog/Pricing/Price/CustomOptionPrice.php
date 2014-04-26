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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Pricing\Price;
use Magento\Catalog\Model\Product\Option\Value;

/**
 * Class OptionPrice
 *
 * @package Magento\Catalog\Pricing\Price
 */
class CustomOptionPrice extends RegularPrice implements CustomOptionPriceInterface
{
    /**
     * @var string
     */
    protected $priceType = self::PRICE_TYPE_CUSTOM_OPTION;

    /**
     * @var array
     */
    protected $priceOptions;

    /**
     * Get Value
     *
     * @return bool|float
     */
    public function getValue()
    {
        if (null !== $this->value) {
            return $this->value;
        }
        $this->value = false;
        $optionIds = $this->salableItem->getCustomOption('option_ids');
        if (!$optionIds) {
            return $this->value;
        }
        $this->value = 0.;

        if ($optionIds) {
            $values = explode(',', $optionIds->getValue());
            $values = array_filter($values);
            if (!empty($values)) {
                $this->value = $this->processOptions($values);
            }
        }
        return $this->value;
    }

    /**
     * Process Product Options
     *
     * @param array $values
     * @return float
     */
    protected function processOptions(array $values)
    {
        $value = 0.;
        foreach ($values as $optionId) {
            $option = $this->salableItem->getOptionById($optionId);
            if (!$option) {
                continue;
            }
            $confItemOption = $this->salableItem->getCustomOption('option_' . $option->getId());

            $group = $option->groupFactory($option->getType())
                ->setOption($option)
                ->setConfigurationItemOption($confItemOption);
            $value += $group->getOptionPrice($confItemOption->getValue(), $this->value);
        }
        return $value;
    }

    /**
     * Get Product Options
     *
     * @return array
     */
    public function getOptions()
    {
        if (null !== $this->priceOptions) {
            return $this->priceOptions;
        }
        $this->priceOptions = [];
        $options = $this->salableItem->getOptions();
        if ($options) {
            /** @var $optionItem \Magento\Catalog\Model\Product\Option */
            foreach ($options as $optionItem) {
                /** @var $optionValue \Magento\Catalog\Model\Product\Option\Value */
                foreach ($optionItem->getValues() as $optionValue) {
                    $price = $optionValue->getPrice($optionValue->getPriceType() == Value::TYPE_PERCENT);
                    $this->priceOptions[$optionValue->getId()][$price] = [
                        'base_amount' => $price,
                        'adjustment' => $this->getAmount()->getValue()
                    ];
                }
            }
        }
        return $this->priceOptions;
    }
}
