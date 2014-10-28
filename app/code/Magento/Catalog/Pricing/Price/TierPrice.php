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

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Tire prices model
 */
class TierPrice extends AbstractPrice implements TierPriceInterface, BasePriceProviderInterface
{
    /**
     * Price type tier
     */
    const PRICE_CODE = 'tier_price';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var int
     */
    protected $customerGroup;

    /**
     * Raw price list stored in DB
     *
     * @var array
     */
    protected $rawPriceList;

    /**
     * Applicable price list
     *
     * @var array
     */
    protected $priceList;

    /**
     * Should filter by base price or not
     *
     * @var bool
     */
    protected $filterByBasePrice = true;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param Session $customerSession
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        Session $customerSession
    ) {
        $quantity = $quantity ?: 1;
        parent::__construct($saleableItem, $quantity, $calculator);
        $this->customerSession = $customerSession;
        if ($saleableItem->hasCustomerGroupId()) {
            $this->customerGroup = (int) $saleableItem->getCustomerGroupId();
        } else {
            $this->customerGroup = (int) $this->customerSession->getCustomerGroupId();
        }
    }

    /**
     * Get price value
     *
     * @return bool|float
     */
    public function getValue()
    {
        if (null === $this->value) {
            $prices = $this->getStoredTierPrices();
            $prevQty = PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT;
            $this->value = $prevPrice = $tierPrice = false;
            $priceGroup = Group::CUST_GROUP_ALL;

            foreach ($prices as $price) {
                if (!$this->canApplyTierPrice($price, $priceGroup, $prevQty)) {
                    continue;
                }
                if (false === $prevPrice || $this->isFirstPriceBetter($price['website_price'], $prevPrice)) {
                    $tierPrice = $prevPrice = $price['website_price'];
                    $prevQty = $price['price_qty'];
                    $priceGroup = $price['cust_group'];
                    $this->value = (float)$tierPrice;
                }
            }
        }
        return $this->value;
    }

    /**
     * Returns true if first price is better
     *
     * Method filters tiers price values, lower tier price value is better
     *
     * @param float $firstPrice
     * @param float $secondPrice
     * @return bool
     */
    protected function isFirstPriceBetter($firstPrice, $secondPrice)
    {
        return $firstPrice < $secondPrice;
    }

    /**
     * @return int
     */
    public function getTierPriceCount()
    {
        return count($this->getTierPriceList());
    }

    /**
     * @return array
     */
    public function getTierPriceList()
    {
        if (null === $this->priceList) {
            $priceList = $this->getStoredTierPrices();
            $this->priceList = $this->filterTierPrices($priceList);
            array_walk(
                $this->priceList,
                function (&$priceData) {
                    /* convert string value to float */
                    $priceData['price_qty'] = $priceData['price_qty'] * 1;
                    $priceData['price'] = $this->applyAdjustment($priceData['price']);
                }
            );
        }
        return $this->priceList;
    }

    /**
     * @param array $priceList
     * @return array
     */
    protected function filterTierPrices(array $priceList)
    {
        $qtyCache = [];
        foreach ($priceList as $priceKey => $price) {
            /* filter price by customer group */
            if ($price['cust_group'] !== $this->customerGroup && $price['cust_group'] !== Group::CUST_GROUP_ALL) {
                unset($priceList[$priceKey]);
                continue;
            }
            /* select a lower price between Tier price and base price */
            if ($this->filterByBasePrice && $price['price'] > $this->getBasePrice()) {
                unset($priceList[$priceKey]);
                continue;
            }
            /* select a lower price for each quantity */
            if (isset($qtyCache[$price['price_qty']])) {
                $priceQty = $qtyCache[$price['price_qty']];
                if ($this->isFirstPriceBetter($price['website_price'], $priceList[$priceQty]['website_price'])) {
                    unset($priceList[$priceQty]);
                    $qtyCache[$price['price_qty']] = $priceKey;
                } else {
                    unset($priceList[$priceKey]);
                }
            } else {
                $qtyCache[$price['price_qty']] = $priceKey;
            }
        }
        return array_values($priceList);
    }

    /**
     * @return float
     */
    protected function getBasePrice()
    {
        /** @var float $productPrice is a minimal available price */
        return $this->priceInfo->getPrice(BasePrice::PRICE_CODE)->getValue();
    }

    /**
     * @param AmountInterface $amount
     * @return float
     */
    public function getSavePercent(AmountInterface $amount)
    {
        return ceil(
            100 - ((100 / $this->priceInfo->getPrice(BasePrice::PRICE_CODE)->getAmount()->getBaseAmount())
                * $amount->getBaseAmount())
        );
    }

    /**
     * @param float|string $price
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function applyAdjustment($price)
    {
        return $this->calculator->getAmount($price, $this->product);
    }

    /**
     * Can apply tier price
     *
     * @param array $currentTierPrice
     * @param int $prevPriceGroup
     * @param float|string $prevQty
     * @return bool
     */
    protected function canApplyTierPrice(array $currentTierPrice, $prevPriceGroup, $prevQty)
    {
        // Tier price can be applied, if:
        // tier price is for current customer group or is for all groups
        if ($currentTierPrice['cust_group'] !== $this->customerGroup
            && $currentTierPrice['cust_group'] !== Group::CUST_GROUP_ALL
        ) {
            return false;
        }
        // and tier qty is lower than product qty
        if ($this->quantity < $currentTierPrice['price_qty']) {
            return false;
        }
        // and tier qty is bigger than previous qty
        if ($currentTierPrice['price_qty'] < $prevQty) {
            return false;
        }
        // and found tier qty is same as previous tier qty, but current tier group isn't ALL_GROUPS
        if ($currentTierPrice['price_qty'] == $prevQty
            && $prevPriceGroup !== Group::CUST_GROUP_ALL
            && $currentTierPrice['cust_group'] === Group::CUST_GROUP_ALL
        ) {
            return false;
        }
        return true;
    }

    /**
     * Get clear tier price list stored in DB
     *
     * @return array
     */
    protected function getStoredTierPrices()
    {
        if (null === $this->rawPriceList) {
            $this->rawPriceList = $this->product->getData(self::PRICE_CODE);
            if (null === $this->rawPriceList || !is_array($this->rawPriceList)) {
                /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
                $attribute = $this->product->getResource()->getAttribute(self::PRICE_CODE);
                if ($attribute) {
                    $attribute->getBackend()->afterLoad($this->product);
                    $this->rawPriceList = $this->product->getData(self::PRICE_CODE);
                }
            }
            if (null === $this->rawPriceList || !is_array($this->rawPriceList)) {
                $this->rawPriceList = array();
            }
        }
        return $this->rawPriceList;
    }
}
