<?php
/**
 * Product type price model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * @var \Magento\Catalog\Model\Product\PriceModifierInterface
     */
    protected $priceModifier;

    /**
     * @param \Magento\CatalogRule\Model\Resource\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param PriceModifierInterface $priceModifier
     */
    public function __construct(
        \Magento\CatalogRule\Model\Resource\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        PriceModifierInterface $priceModifier
    ) {
        $this->priceModifier = $priceModifier;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement
        );
    }

    /**
     * Get product final price
     *
     * @param   float $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     */
    public function getFinalPrice($qty, $product)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $basePrice = $this->getBasePrice($product, $qty);
        $finalPrice = $basePrice;
        $product->setFinalPrice($finalPrice);
        $this->_eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);
        $finalPrice = $product->getData('final_price');

        $finalPrice += $this->getTotalConfigurableItemsPrice($product, $finalPrice);
        $finalPrice += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
        $finalPrice = max(0, $finalPrice);

        $product->setFinalPrice($finalPrice);
        return $finalPrice;
    }

    /**
     * Get Total price for configurable items
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param float $finalPrice
     * @return float
     */
    public function getTotalConfigurableItemsPrice($product, $finalPrice)
    {
        $price = 0.0;

        $product->getTypeInstance()->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance()->getConfigurableAttributes($product);

        $selectedAttributes = [];
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }

        foreach ($attributes as $attribute) {
            $attributeId = $attribute->getProductAttribute()->getId();
            $value = $this->_getValueByIndex(
                $attribute->getPrices() ? $attribute->getPrices() : [],
                isset($selectedAttributes[$attributeId]) ? $selectedAttributes[$attributeId] : null
            );
            $product->setParentId(true);
            if ($value) {
                if ($value['pricing_value'] != 0) {
                    $product->setConfigurablePrice($this->_calcSelectionPrice($value, $finalPrice));
                    $product->setConfigurablePrice(
                        $this->priceModifier->modifyPrice($product->getConfigurablePrice(), $product)
                    );
                    $price += $product->getConfigurablePrice();
                }
            }
        }
        return $price;
    }

    /**
     * Calculate configurable product selection price
     *
     * @param   array $priceInfo
     * @param   float $productPrice
     * @return  float
     */
    protected function _calcSelectionPrice($priceInfo, $productPrice)
    {
        if ($priceInfo['is_percent']) {
            $ratio = $priceInfo['pricing_value'] / 100;
            $price = $productPrice * $ratio;
        } else {
            $price = $priceInfo['pricing_value'];
        }
        return $price;
    }

    /**
     * Find value in array by index
     *
     * @param array $values
     * @param string $index
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function _getValueByIndex($values, $index)
    {
        foreach ($values as $value) {
            if ($value['value_index'] == $index) {
                return $value;
            }
        }
        return false;
    }
}
