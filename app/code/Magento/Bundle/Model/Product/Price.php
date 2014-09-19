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

namespace Magento\Bundle\Model\Product;

use Magento\Customer\Service\V1\CustomerGroupServiceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Bundle Price Model
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Fixed bundle price type
     */
    const PRICE_TYPE_FIXED = 1;

    /**
     * Dynamic bundle price type
     */
    const PRICE_TYPE_DYNAMIC = 0;

    /**
     * Flag which indicates - is min/max prices have been calculated by index
     *
     * @var bool
     */
    protected $_isPricesCalculatedByIndex;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @param \Magento\CatalogRule\Model\Resource\RuleFactory $ruleFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Helper\Data $catalogData
     */
    public function __construct(
        \Magento\CatalogRule\Model\Resource\RuleFactory $ruleFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Data $catalogData
    ) {
        $this->_catalogData = $catalogData;
        parent::__construct($ruleFactory, $storeManager, $localeDate, $customerSession, $eventManager, $priceCurrency);
    }

    /**
     * Is min/max prices have been calculated by index
     *
     * @return bool
     */
    public function getIsPricesCalculatedByIndex()
    {
        return $this->_isPricesCalculatedByIndex;
    }

    /**
     * Return product base price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     * @deprecated
     */
    public function getPrice($product)
    {
        if ($product->getPriceType() == self::PRICE_TYPE_FIXED) {
            return $product->getData('price');
        } else {
            return 0;
        }
    }

    /**
     * Get Total price  for Bundle items
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|float $qty
     * @return float
     */
    public function getTotalBundleItemsPrice($product, $qty = null)
    {
        $price = 0.0;
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('bundle_selection_ids');
            if ($customOption) {
                $selectionIds = unserialize($customOption->getValue());
                $selections = $product->getTypeInstance()->getSelectionsByIds($selectionIds, $product);
                $selections->addTierPriceData();
                $this->_eventManager->dispatch(
                    'prepare_catalog_product_collection_prices',
                    array('collection' => $selections, 'store_id' => $product->getStoreId())
                );
                foreach ($selections->getItems() as $selection) {
                    if ($selection->isSalable()) {
                        $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                        if ($selectionQty) {
                            $price += $this->getSelectionFinalTotalPrice(
                                $product,
                                $selection,
                                $qty,
                                $selectionQty->getValue()
                            );
                        }
                    }
                }
            }
        }
        return $price;
    }

    /**
     * Get product final price
     *
     * @param   float                     $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     */
    public function getFinalPrice($qty, $product)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = $this->getBasePrice($product, $qty);
        $product->setFinalPrice($finalPrice);
        $this->_eventManager->dispatch('catalog_product_get_final_price', array('product' => $product, 'qty' => $qty));
        $finalPrice = $product->getData('final_price');

        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice += $this->getTotalBundleItemsPrice($product, $qty);

        $product->setFinalPrice($finalPrice);
        return max(0, $product->getData('final_price'));
    }

    /**
     * Returns final price of a child product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param float                      $productQty
     * @param \Magento\Catalog\Model\Product $childProduct
     * @param float                      $childProductQty
     * @return float
     */
    public function getChildFinalPrice($product, $productQty, $childProduct, $childProductQty)
    {
        return $this->getSelectionFinalTotalPrice($product, $childProduct, $productQty, $childProductQty, false);
    }

    /**
     * Retrieve Price considering tier price
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  string|null                $which
     * @param  bool|null                  $includeTax
     * @param  bool                       $takeTierPrice
     * @return float|array
     */
    public function getTotalPrices($product, $which = null, $includeTax = null, $takeTierPrice = true)
    {
        // check calculated price index
        if ($product->getData('min_price') && $product->getData('max_price')) {
            $minimalPrice = $this->_catalogData->getTaxPrice($product, $product->getData('min_price'), $includeTax);
            $maximalPrice = $this->_catalogData->getTaxPrice($product, $product->getData('max_price'), $includeTax);
            $this->_isPricesCalculatedByIndex = true;
        } else {
            /**
             * Check if product price is fixed
             */
            $finalPrice = $product->getFinalPrice();
            if ($product->getPriceType() == self::PRICE_TYPE_FIXED) {
                $minimalPrice = $maximalPrice = $this->_catalogData->getTaxPrice($product, $finalPrice, $includeTax);
            } else {
                // PRICE_TYPE_DYNAMIC
                $minimalPrice = $maximalPrice = 0;
            }

            $options = $this->getOptions($product);
            $minPriceFounded = false;

            if ($options) {
                foreach ($options as $option) {
                    /* @var $option \Magento\Bundle\Model\Option */
                    $selections = $option->getSelections();
                    if ($selections) {
                        $selectionMinimalPrices = array();
                        $selectionMaximalPrices = array();

                        foreach ($option->getSelections() as $selection) {
                            /* @var $selection \Magento\Bundle\Model\Selection */
                            if (!$selection->isSalable()) {
                                /**
                                 * @todo CatalogInventory Show out of stock Products
                                 */
                                continue;
                            }

                            $qty = $selection->getSelectionQty();

                            $item = $product->getPriceType() == self::PRICE_TYPE_FIXED ? $product : $selection;

                            $selectionMinimalPrices[] = $this->_catalogData->getTaxPrice(
                                $item,
                                $this->getSelectionFinalTotalPrice(
                                    $product,
                                    $selection,
                                    1,
                                    $qty,
                                    true,
                                    $takeTierPrice
                                ),
                                $includeTax
                            );
                            $selectionMaximalPrices[] = $this->_catalogData->getTaxPrice(
                                $item,
                                $this->getSelectionFinalTotalPrice(
                                    $product,
                                    $selection,
                                    1,
                                    null,
                                    true,
                                    $takeTierPrice
                                ),
                                $includeTax
                            );
                        }

                        if (count($selectionMinimalPrices)) {
                            $selMinPrice = min($selectionMinimalPrices);
                            if ($option->getRequired()) {
                                $minimalPrice += $selMinPrice;
                                $minPriceFounded = true;
                            } elseif (true !== $minPriceFounded) {
                                $selMinPrice += $minimalPrice;
                                $minPriceFounded = false === $minPriceFounded ? $selMinPrice : min(
                                    $minPriceFounded,
                                    $selMinPrice
                                );
                            }

                            if ($option->isMultiSelection()) {
                                $maximalPrice += array_sum($selectionMaximalPrices);
                            } else {
                                $maximalPrice += max($selectionMaximalPrices);
                            }
                        }
                    }
                }
            }
            // condition is TRUE when all product options are NOT required
            if (!is_bool($minPriceFounded)) {
                $minimalPrice = $minPriceFounded;
            }

            $customOptions = $product->getOptions();
            if ($product->getPriceType() == self::PRICE_TYPE_FIXED && $customOptions) {
                foreach ($customOptions as $customOption) {
                    /* @var $customOption \Magento\Catalog\Model\Product\Option */
                    $values = $customOption->getValues();
                    if ($values) {
                        $prices = array();
                        foreach ($values as $value) {
                            /* @var $value \Magento\Catalog\Model\Product\Option\Value */
                            $valuePrice = $value->getPrice(true);

                            $prices[] = $valuePrice;
                        }
                        if (count($prices)) {
                            if ($customOption->getIsRequire()) {
                                $minimalPrice += $this->_catalogData->getTaxPrice($product, min($prices), $includeTax);
                            }

                            $multiTypes = array(
                                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX,
                                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
                            );

                            if (in_array($customOption->getType(), $multiTypes)) {
                                $maximalValue = array_sum($prices);
                            } else {
                                $maximalValue = max($prices);
                            }
                            $maximalPrice += $this->_catalogData->getTaxPrice($product, $maximalValue, $includeTax);
                        }
                    } else {
                        $valuePrice = $customOption->getPrice(true);

                        if ($customOption->getIsRequire()) {
                            $minimalPrice += $this->_catalogData->getTaxPrice($product, $valuePrice, $includeTax);
                        }
                        $maximalPrice += $this->_catalogData->getTaxPrice($product, $valuePrice, $includeTax);
                    }
                }
            }
            $this->_isPricesCalculatedByIndex = false;
        }

        if ($which == 'max') {
            return $maximalPrice;
        } elseif ($which == 'min') {
            return $minimalPrice;
        }

        return array($minimalPrice, $maximalPrice);
    }

    /**
     * Calculate Minimal price of bundle (counting all required options)
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getMinimalPrice($product)
    {
        return $this->getPricesTierPrice($product, 'min');
    }

    /**
     * Calculate maximal price of bundle
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getMaximalPrice($product)
    {
        return $this->getPricesTierPrice($product, 'max');
    }

    /**
     * Get Options with attached Selections collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function getOptions($product)
    {
        $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);

        $optionCollection = $product->getTypeInstance()->getOptionsCollection($product);

        $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
            $product->getTypeInstance()->getOptionsIds($product),
            $product
        );

        return $optionCollection->appendSelections($selectionCollection, false, false);
    }

    /**
     * Calculate price of selection
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float|null                 $selectionQty
     * @param null|bool                  $multiplyQty      Whether to multiply selection's price by its quantity
     * @return float
     *
     * @deprecated after 1.6.2.0
     * @see \Magento\Bundle\Model\Product\Price::getSelectionFinalTotalPrice()
     */
    public function getSelectionPrice($bundleProduct, $selectionProduct, $selectionQty = null, $multiplyQty = true)
    {
        return $this->getSelectionFinalTotalPrice($bundleProduct, $selectionProduct, 0, $selectionQty, $multiplyQty);
    }

    /**
     * Calculate selection price for front view (with applied special of bundle)
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float                    $qty
     * @return float
     */
    public function getSelectionPreFinalPrice($bundleProduct, $selectionProduct, $qty = null)
    {
        return $this->getSelectionPrice($bundleProduct, $selectionProduct, $qty);
    }

    /**
     * Calculate final price of selection
     * with take into account tier price
     *
     * @param  \Magento\Catalog\Model\Product $bundleProduct
     * @param  \Magento\Catalog\Model\Product $selectionProduct
     * @param  float                    $bundleQty
     * @param  float                    $selectionQty
     * @param  bool                       $multiplyQty
     * @param  bool                       $takeTierPrice
     * @return float
     */
    public function getSelectionFinalTotalPrice(
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    ) {
        if (null === $bundleQty) {
            $bundleQty = 1.;
        }
        if (is_null($selectionQty)) {
            $selectionQty = $selectionProduct->getSelectionQty();
        }

        if ($bundleProduct->getPriceType() == self::PRICE_TYPE_DYNAMIC) {
            $price = $selectionProduct->getFinalPrice($takeTierPrice ? $selectionQty : 1);
        } else {
            if ($selectionProduct->getSelectionPriceType()) {
                // percent
                $product = clone $bundleProduct;
                $product->setFinalPrice($this->getPrice($product));
                $this->_eventManager->dispatch(
                    'catalog_product_get_final_price',
                    array('product' => $product, 'qty' => $bundleQty)
                );
                $price = $product->getData('final_price') * ($selectionProduct->getSelectionPriceValue() / 100);
            } else {
                // fixed
                $price = $selectionProduct->getSelectionPriceValue();
            }
        }

        if ($multiplyQty) {
            $price *= $selectionQty;
        }

        return min(
            $price,
            $this->_applyGroupPrice($bundleProduct, $price),
            $this->_applyTierPrice($bundleProduct, $bundleQty, $price),
            $this->_applySpecialPrice($bundleProduct, $price)
        );
    }

    /**
     * Apply group price for bundle product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param float $finalPrice
     * @return float
     */
    protected function _applyGroupPrice($product, $finalPrice)
    {
        $result = $finalPrice;
        $groupPrice = $product->getGroupPrice();

        if (is_numeric($groupPrice)) {
            $groupPrice = $finalPrice - $finalPrice * ($groupPrice / 100);
            $result = min($finalPrice, $groupPrice);
        }

        return $result;
    }

    /**
     * Get product group price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float|null
     */
    public function getGroupPrice($product)
    {
        $groupPrices = $product->getData('group_price');

        if (is_null($groupPrices)) {
            $attribute = $product->getResource()->getAttribute('group_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $groupPrices = $product->getData('group_price');
            }
        }

        if (is_null($groupPrices) || !is_array($groupPrices)) {
            return null;
        }

        $customerGroup = $this->_getCustomerGroupId($product);

        $matchedPrice = 0;

        foreach ($groupPrices as $groupPrice) {
            if ($groupPrice['cust_group'] == $customerGroup && $groupPrice['website_price'] > $matchedPrice) {
                $matchedPrice = $groupPrice['website_price'];
                break;
            }
        }

        return $matchedPrice;
    }

    /**
     * Apply tier price for bundle
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   float                    $qty
     * @param   float                    $finalPrice
     * @return  float
     */
    protected function _applyTierPrice($product, $qty, $finalPrice)
    {
        if (is_null($qty)) {
            return $finalPrice;
        }

        $tierPrice = $product->getTierPrice($qty);

        if (is_numeric($tierPrice)) {
            $tierPrice = $finalPrice - $finalPrice * ($tierPrice / 100);
            $finalPrice = min($finalPrice, $tierPrice);
        }

        return $finalPrice;
    }

    /**
     * Get product tier price by qty
     *
     * @param   float                    $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float|array
     */
    public function getTierPrice($qty, $product)
    {
        $allGroups = CustomerGroupServiceInterface::CUST_GROUP_ALL;
        $prices = $product->getData('tier_price');

        if (is_null($prices)) {
            if ($attribute = $product->getResource()->getAttribute('tier_price')) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData('tier_price');
            }
        }

        if (is_null($prices) || !is_array($prices)) {
            if (!is_null($qty)) {
                return $product->getPrice();
            }
            return array(
                array(
                    'price' => $product->getPrice(),
                    'website_price' => $product->getPrice(),
                    'price_qty' => 1,
                    'cust_group' => $allGroups
                )
            );
        }

        $custGroup = $this->_getCustomerGroupId($product);
        if ($qty) {
            $prevQty = 1;
            $prevPrice = 0;
            $prevGroup = $allGroups;

            foreach ($prices as $price) {
                if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroups) {
                    // tier not for current customer group nor is for all groups
                    continue;
                }
                if ($qty < $price['price_qty']) {
                    // tier is higher than product qty
                    continue;
                }
                if ($price['price_qty'] < $prevQty) {
                    // higher tier qty already found
                    continue;
                }
                if ($price['price_qty'] == $prevQty && $prevGroup != $allGroups && $price['cust_group'] == $allGroups
                ) {
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                    continue;
                }

                if ($price['website_price'] > $prevPrice) {
                    $prevPrice = $price['website_price'];
                    $prevQty = $price['price_qty'];
                    $prevGroup = $price['cust_group'];
                }
            }

            return $prevPrice;
        } else {
            $qtyCache = array();
            foreach ($prices as $i => $price) {
                if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroups) {
                    unset($prices[$i]);
                } else if (isset($qtyCache[$price['price_qty']])) {
                    $j = $qtyCache[$price['price_qty']];
                    if ($prices[$j]['website_price'] < $price['website_price']) {
                        unset($prices[$j]);
                        $qtyCache[$price['price_qty']] = $i;
                    } else {
                        unset($prices[$i]);
                    }
                } else {
                    $qtyCache[$price['price_qty']] = $i;
                }
            }
        }

        return $prices ? $prices : array();
    }

    /**
     * Check is group price value fixed or percent of original price
     *
     * @return bool
     */
    public function isGroupPriceFixed()
    {
        return false;
    }

    /**
     * Calculate and apply special price
     *
     * @param float  $finalPrice
     * @param float  $specialPrice
     * @param string $specialPriceFrom
     * @param string $specialPriceTo
     * @param mixed  $store
     * @return float
     */
    public function calculateSpecialPrice(
        $finalPrice,
        $specialPrice,
        $specialPriceFrom,
        $specialPriceTo,
        $store = null
    ) {
        if (!is_null($specialPrice) && $specialPrice != false) {
            if ($this->_localeDate->isScopeDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
                $specialPrice = $finalPrice * ($specialPrice / 100);
                $finalPrice = min($finalPrice, $specialPrice);
            }
        }

        return $finalPrice;
    }

    /**
     * Returns the lowest price after applying any applicable bundle discounts
     *
     * @param /Magento/Catalog/Model/Product $bundleProduct
     * @param float|string $price
     * @param int          $bundleQty
     * @return float
     */
    public function getLowestPrice($bundleProduct, $price, $bundleQty = 1)
    {
        $price = (float)$price;
        return min(
            $price,
            $this->_applyGroupPrice($bundleProduct, $price),
            $this->_applyTierPrice($bundleProduct, $bundleQty, $price),
            $this->_applySpecialPrice($bundleProduct, $price)
        );
    }
}
