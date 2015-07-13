<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use \Magento\Framework\Pricing\Price\BasePriceProviderInterface;

class BasePrice extends \Magento\Catalog\Pricing\Price\BasePrice
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var null|boolean|float
     */
    protected $minimumAdditionalPrice;

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $selectedConfigurableOption = $this->product->getSelectedConfigurableOption();
        $productId = $selectedConfigurableOption ? $selectedConfigurableOption->getId() : $this->product->getId();
        if (!isset($this->values[$productId])) {
            $this->value = null;
            if (!$selectedConfigurableOption) {
                $this->values[$productId] = parent::getValue();
            } else {
                if (false !== $this->getMinimumAdditionalPrice()) {
                    $this->values[$productId] = $this->getMinimumAdditionalPrice();
                } else {
                    $this->values[$productId] = parent::getValue();
                }
            }
        }
        return $this->values[$productId];
    }

    /**
     * @return bool|float
     */
    protected function getMinimumAdditionalPrice()
    {
        if (null === $this->minimumAdditionalPrice) {
            $priceCodes = [
                \Magento\Catalog\Pricing\Price\SpecialPrice::PRICE_CODE,
                \Magento\Catalog\Pricing\Price\GroupPrice::PRICE_CODE,
                \Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE,
            ];
            $this->minimumAdditionalPrice = false;
            foreach ($priceCodes as $priceCode) {
                $price = $this->product->getPriceInfo()->getPrice($priceCode);
                if ($price instanceof BasePriceProviderInterface && $price->getValue() !== false) {
                    $this->minimumAdditionalPrice = min(
                        $price->getValue(),
                        $this->minimumAdditionalPrice ?: $price->getValue()
                    );
                }
            }
        }
        return $this->minimumAdditionalPrice;
    }
}
