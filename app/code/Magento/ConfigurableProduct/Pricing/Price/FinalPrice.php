<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        if ($this->product->getSelectedConfigurableOption()) {
            $this->amount = null;
        }
        return parent::getAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $selectedConfigurableOption = $this->product->getSelectedConfigurableOption();
        $productId = $selectedConfigurableOption ? $selectedConfigurableOption->getId() : $this->product->getId();
        if (!isset($this->values[$productId])) {
            $price = null;
            if ($selectedConfigurableOption) {
                $price = $selectedConfigurableOption->getPriceInfo()->getPrice(self::PRICE_CODE)->getAmount()
                    ->getValue();
            } else {
                foreach ($this->getUsedProducts() as $product) {
                    $productPrice = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getAmount()->getValue();
                    $price = $price ? min($price, $productPrice) : $productPrice;
                }
            }

            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $this->values[$productId] = $priceInCurrentCurrency ? floatval($priceInCurrentCurrency) : false;
        }

        return $this->values[$productId];
    }

    /**
     * Get children simple products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    protected function getUsedProducts()
    {
        return $this->product->getTypeInstance()->getUsedProducts($this->product);
    }
}
