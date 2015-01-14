<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Group price model
 */
class GroupPrice extends AbstractPrice implements BasePriceProviderInterface
{
    /**
     * Price type group
     */
    const PRICE_CODE = 'group_price';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var array|null
     */
    protected $storedGroupPrice;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Session $customerSession
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Session $customerSession
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->customerSession = $customerSession;
    }

    /**
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            $this->value = false;
            $customerGroup = $this->getCustomerGroupId();
            foreach ($this->getStoredGroupPrice() as $groupPrice) {
                if ($groupPrice['cust_group'] == $customerGroup) {
                    $this->value = (float) $groupPrice['website_price'];
                    if (!$this->isPercentageDiscount()) {
                        $this->value = $this->priceCurrency->convertAndRound($this->value);
                    }
                    break;
                }
            }
        }
        return $this->value;
    }

    /**
     * @return int
     */
    protected function getCustomerGroupId()
    {
        if ($this->product->getCustomerGroupId()) {
            return (int) $this->product->getCustomerGroupId();
        }
        return (int) $this->customerSession->getCustomerGroupId();
    }

    /**
     * @return array
     */
    protected function getStoredGroupPrice()
    {
        if (null === $this->storedGroupPrice) {
            $resource = $this->product->getResource();
            $attribute =  $resource->getAttribute('group_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($this->product);
                $this->storedGroupPrice = $this->product->getData('group_price');
            }
            if (null === $this->storedGroupPrice || !is_array($this->storedGroupPrice)) {
                $this->storedGroupPrice = [];
            }
        }
        return $this->storedGroupPrice;
    }

    /**
     * @return bool
     */
    public function isPercentageDiscount()
    {
        return false;
    }
}
