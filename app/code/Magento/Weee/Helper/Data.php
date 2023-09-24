<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Helper;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;

/**
 * WEEE data helper
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper implements ResetAfterRequestInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    public const KEY_WEEE_AMOUNT_INVOICED = 'weee_amount_invoiced';

    public const KEY_BASE_WEEE_AMOUNT_INVOICED = 'base_weee_amount_invoiced';

    public const KEY_WEEE_TAX_AMOUNT_INVOICED = 'weee_tax_amount_invoiced';

    public const KEY_BASE_WEEE_TAX_AMOUNT_INVOICED = 'base_weee_tax_amount_invoiced';

    public const KEY_WEEE_AMOUNT_REFUNDED = 'weee_amount_refunded';

    public const KEY_BASE_WEEE_AMOUNT_REFUNDED = 'base_weee_amount_refunded';

    public const KEY_WEEE_TAX_AMOUNT_REFUNDED = 'weee_tax_amount_refunded';

    public const KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED = 'base_weee_tax_amount_refunded';

    /**
     * @var array
     */
    protected $_storeDisplayConfig = [];

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $_weeeTax;

    /**
     * @var \Magento\Weee\Model\Config
     */
    protected $_weeeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var string
     */
    protected $cacheProductWeeeAmount = '_cache_product_weee_amount';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Weee\Model\Tax $weeeTax
     * @param \Magento\Weee\Model\Config $weeeConfig
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Weee\Model\Tax $weeeTax,
        \Magento\Weee\Model\Config $weeeConfig,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_weeeTax = $weeeTax;
        $this->_coreRegistry = $coreRegistry;
        $this->_taxData = $taxData;
        $this->_weeeConfig = $weeeConfig;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($context);
    }

    /**
     * Get weee amount display type on product view page
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getPriceDisplayType($store = null)
    {
        return $this->_weeeConfig->getPriceDisplayType($store);
    }

    /**
     * Get weee amount display type on product list page
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getListPriceDisplayType($store = null)
    {
        return $this->_weeeConfig->getListPriceDisplayType($store);
    }

    /**
     * Get weee amount display type in sales modules
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getSalesPriceDisplayType($store = null)
    {
        return $this->_weeeConfig->getSalesPriceDisplayType($store);
    }

    /**
     * Get weee amount display type in email templates
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getEmailPriceDisplayType($store = null)
    {
        return $this->_weeeConfig->getEmailPriceDisplayType($store);
    }

    /**
     * Check if weee tax amount should be taxable
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function isTaxable($store = null)
    {
        return $this->_weeeConfig->isTaxable($store);
    }

    /**
     * Check if weee tax amount should be included to subtotal
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function includeInSubtotal($store = null)
    {
        return $this->_weeeConfig->includeInSubtotal($store);
    }

    /**
     * Check if fixed taxes are used in system
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function isEnabled($store = null)
    {
        return $this->_weeeConfig->isEnabled($store);
    }

    /**
     * Check if the FPT totals line(s) should be displayed with tax included
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function displayTotalsInclTax($store = null)
    {
        // If catalog prices include tax, then display FPT totals with tax included
        return $this->_taxData->priceIncludesTax($store);
    }

    /**
     * Get weee tax amount for product based on website
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   mixed $website
     * @return  float
     */
    public function getAmountExclTax($product, $website = null)
    {
        if (!$product->hasData($this->cacheProductWeeeAmount)) {
            /** @var \Magento\Store\Model\Store $store */
            if ($website) {
                $store = $this->_storeManager->getWebsite($website)->getDefaultGroup()->getDefaultStore();
            } else {
                $store = $product->getStore();
            }

            $amount = 0;
            if ($this->isEnabled($store)) {
                $amount = $this->_weeeTax->getWeeeAmountExclTax($product, null, null, $website);
            }

            $product->setData($this->cacheProductWeeeAmount, $amount);
        }
        return $product->getData($this->cacheProductWeeeAmount);
    }

    /**
     * Returns display type for price accordingly to current zone
     *
     * @param int|int[]|null                 $compareTo
     * @param string                         $zone
     * @param Store|int|string               $store
     * @return bool|int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function typeOfDisplay(
        $compareTo = null,
        $zone = \Magento\Framework\Pricing\Render::ZONE_DEFAULT,
        $store = null
    ) {
        if (!$this->isEnabled($store)) {
            return false;
        }
        switch ($zone) {
            case \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW:
                $type = $this->getPriceDisplayType($store);
                break;
            case \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST:
                $type = $this->getListPriceDisplayType($store);
                break;
            case \Magento\Framework\Pricing\Render::ZONE_SALES:
            case \Magento\Framework\Pricing\Render::ZONE_CART:
                $type = $this->getSalesPriceDisplayType($store);
                break;
            case \Magento\Framework\Pricing\Render::ZONE_EMAIL:
                $type = $this->getEmailPriceDisplayType($store);
                break;
            default:
                if ($this->_coreRegistry->registry('current_product')) {
                    $type = $this->getPriceDisplayType($store);
                } else {
                    $type = $this->getListPriceDisplayType($store);
                }
                break;
        }

        if ($compareTo === null) {
            return $type;
        } else {
            if (is_array($compareTo)) {
                return in_array($type, $compareTo);
            } else {
                return $type == $compareTo;
            }
        }
    }

    /**
     * Proxy for \Magento\Weee\Model\Tax::getProductWeeeAttributes()
     *
     * @param \Magento\Catalog\Model\Product                $product
     * @param null|false|\Magento\Framework\DataObject      $shipping
     * @param null|false|\Magento\Framework\DataObject      $billing
     * @param Website                                       $website
     * @param bool                                          $calculateTaxes
     * @param bool                                          $round
     * @return \Magento\Framework\DataObject[]
     */
    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTaxes = false,
        $round = true
    ) {
        return $this->_weeeTax->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            $calculateTaxes,
            $round
        );
    }

    /**
     * Returns applied weee tax amount
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getWeeeTaxAppliedAmount($item)
    {
        return $this->getRecursiveNumericAmount($item, 'getWeeeTaxAppliedAmount');
    }

    /**
     * Returns applied weee tax amount for the row
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount($item)
    {
        return $this->getRecursiveNumericAmount($item, 'getWeeeTaxAppliedRowAmount');
    }

    /**
     * Returns applied base weee tax amount for the row
     *
     * @param QuoteAbstractItem|OrderItem $item
     * @return float
     * @since 100.2.0
     */
    public function getBaseWeeeTaxAppliedRowAmount($item)
    {
        return $this->getRecursiveNumericAmount($item, 'getBaseWeeeTaxAppliedRowAmnt');
    }

    /**
     * Returns accumulated amounts for the item
     *
     * @param QuoteAbstractItem|OrderItem $item
     * @param string $functionName
     * @return float
     */
    protected function getRecursiveNumericAmount($item, $functionName)
    {
        if ($item instanceof QuoteAbstractItem || $item instanceof OrderItem) {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $result = 0;
                $children = $item instanceof OrderItem ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    $childData = $this->getRecursiveNumericAmount($child, $functionName);
                    if (!empty($childData)) {
                        $result += $childData;
                    }
                }

                return $result;
            }
        }

        $data = $item->$functionName();
        if (empty($data)) {
            return 0;
        }
        return $data;
    }

    /**
     * Returns applied weee taxes
     *
     * @param QuoteAbstractItem $item
     * @return array
     */
    public function getApplied($item)
    {
        if ($item instanceof QuoteAbstractItem) {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $result = [];
                foreach ($item->getChildren() as $child) {
                    $childData = $this->getApplied($child);
                    if (is_array($childData)) {
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $result = array_merge($result, $childData);
                    }
                }
                return $result;
            }
        }

        // if order item data is old enough then weee_tax_applied might not be valid
        $data = $item->getWeeeTaxApplied();
        if (empty($data)) {
            return [];
        }
        return $this->serializer->unserialize($item->getWeeeTaxApplied());
    }

    /**
     * Sets applied weee taxes
     *
     * @param QuoteAbstractItem $item
     * @param array $value
     * @return $this
     */
    public function setApplied($item, $value)
    {
        $item->setWeeeTaxApplied($this->serializer->serialize($value));
        return $this;
    }

    /**
     * Returns array of weee attributes allowed for display
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Framework\DataObject[]
     */
    public function getProductWeeeAttributesForDisplay($product)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $product->getStore();

        if ($this->isEnabled($store)) {
            $calculateTax = ($this->typeOfDisplay(1) || $this->typeOfDisplay(2)) ? 1 : 0;
            return $this->getProductWeeeAttributes($product, null, null, null, $calculateTax, false);
        }
        return [];
    }

    /**
     * Get Product Weee attributes for price renderer
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|false|\Magento\Framework\DataObject $shipping Shipping Address
     * @param null|false|\Magento\Framework\DataObject $billing Billing Address
     * @param null|Website $website
     * @param bool $calculateTaxes
     * @return \Magento\Framework\DataObject[]
     */
    public function getProductWeeeAttributesForRenderer(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTaxes = false
    ) {
        /** @var \Magento\Store\Model\Store $store */
        if ($website) {
            $store = $this->_storeManager->getWebsite($website)->getDefaultGroup()->getDefaultStore();
        } else {
            $store = $product->getStore();
        }

        if ($this->isEnabled($store)) {
            return $this->getProductWeeeAttributes(
                $product,
                $shipping,
                $billing,
                $website,
                $calculateTaxes ? $calculateTaxes : $this->typeOfDisplay(1)
            );
        }
        return [];
    }

    /**
     * Get the weee tax including tax
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getWeeeTaxInclTax($item)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($item);
        $totalWeeeTaxIncTaxApplied = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            $totalWeeeTaxIncTaxApplied += max($weeeTaxAppliedAmount['amount_incl_tax'], 0);
        }
        return $totalWeeeTaxIncTaxApplied;
    }

    /**
     * Get the total base weee tax
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getBaseWeeeTaxInclTax($item)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($item);
        $totalBaseWeeeTaxIncTaxApplied = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            $totalBaseWeeeTaxIncTaxApplied += max($weeeTaxAppliedAmount['base_amount_incl_tax'], 0);
        }
        return $totalBaseWeeeTaxIncTaxApplied;
    }

    /**
     * Get the total weee including tax by row
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getRowWeeeTaxInclTax($item)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($item);
        $totalWeeeTaxIncTaxApplied = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            $totalWeeeTaxIncTaxApplied += max($weeeTaxAppliedAmount['row_amount_incl_tax'], 0);
        }
        return $totalWeeeTaxIncTaxApplied;
    }

    /**
     * Get the total base weee including tax by row
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getBaseRowWeeeTaxInclTax($item)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($item);
        $totalWeeeTaxIncTaxApplied = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            $totalWeeeTaxIncTaxApplied += max($weeeTaxAppliedAmount['base_row_amount_incl_tax'], 0);
        }
        return $totalWeeeTaxIncTaxApplied;
    }

    /**
     * Get the total tax applied on weee by unit
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getTotalTaxAppliedForWeeeTax($item)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($item);
        $totalTaxForWeeeTax = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            $totalTaxForWeeeTax += max(
                $weeeTaxAppliedAmount['amount_incl_tax']
                - $weeeTaxAppliedAmount['amount'],
                0
            );
        }
        return $totalTaxForWeeeTax;
    }

    /**
     * Get the total tax applied on weee by unit
     *
     * @param QuoteAbstractItem $item
     * @return float
     */
    public function getBaseTotalTaxAppliedForWeeeTax($item)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($item);
        $totalTaxForWeeeTax = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            $totalTaxForWeeeTax += max(
                $weeeTaxAppliedAmount['base_amount_incl_tax']
                - $weeeTaxAppliedAmount['base_amount'],
                0
            );
        }
        return $totalTaxForWeeeTax;
    }

    /**
     * Get invoiced wee amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getWeeeAmountInvoiced($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $totalAmountInvoiced = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_WEEE_AMOUNT_INVOICED])) {
                $totalAmountInvoiced = $weeeTaxAppliedAmount[self::KEY_WEEE_AMOUNT_INVOICED];
                break;
            }
        }
        return $totalAmountInvoiced;
    }

    /**
     * Get invoiced base wee amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getBaseWeeeAmountInvoiced($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $baseTotalAmountInvoiced = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_BASE_WEEE_AMOUNT_INVOICED])) {
                $baseTotalAmountInvoiced = $weeeTaxAppliedAmount[self::KEY_BASE_WEEE_AMOUNT_INVOICED];
                break;
            }
        }
        return $baseTotalAmountInvoiced;
    }

    /**
     * Get invoiced wee tax amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getWeeeTaxAmountInvoiced($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $totalTaxInvoiced = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_WEEE_TAX_AMOUNT_INVOICED])) {
                $totalTaxInvoiced = $weeeTaxAppliedAmount[self::KEY_WEEE_TAX_AMOUNT_INVOICED];
                break;
            }
        }
        return $totalTaxInvoiced;
    }

    /**
     * Get invoiced base wee tax amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getBaseWeeeTaxAmountInvoiced($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $baseTotalTaxInvoiced = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED])) {
                $baseTotalTaxInvoiced = $weeeTaxAppliedAmount[self::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED];
                break;
            }
        }
        return $baseTotalTaxInvoiced;
    }

    /**
     * Get refunded wee amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getWeeeAmountRefunded($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $totalAmountRefunded = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_WEEE_AMOUNT_REFUNDED])) {
                $totalAmountRefunded = $weeeTaxAppliedAmount[self::KEY_WEEE_AMOUNT_REFUNDED];
                break;
            }
        }
        return $totalAmountRefunded;
    }

    /**
     * Get refunded base wee amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getBaseWeeeAmountRefunded($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $baseTotalAmountRefunded = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_BASE_WEEE_AMOUNT_REFUNDED])) {
                $baseTotalAmountRefunded = $weeeTaxAppliedAmount[self::KEY_BASE_WEEE_AMOUNT_REFUNDED];
                break;
            }
        }
        return $baseTotalAmountRefunded;
    }

    /**
     * Get refunded wee tax amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getWeeeTaxAmountRefunded($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $totalTaxRefunded = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_WEEE_TAX_AMOUNT_REFUNDED])) {
                $totalTaxRefunded = $weeeTaxAppliedAmount[self::KEY_WEEE_TAX_AMOUNT_REFUNDED];
                break;
            }
        }
        return $totalTaxRefunded;
    }

    /**
     * Get refunded base wee tax amount
     *
     * @param OrderItem $orderItem
     * @return float
     */
    public function getBaseWeeeTaxAmountRefunded($orderItem)
    {
        $weeeTaxAppliedAmounts = $this->getApplied($orderItem);
        $baseTotalTaxRefunded = 0;
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount[self::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED])) {
                $baseTotalTaxRefunded = $weeeTaxAppliedAmount[self::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED];
                break;
            }
        }
        return $baseTotalTaxRefunded;
    }

    /**
     * Returns the total amount of FPT across all items.  Used for displaying the FPT totals line item.
     *
     * @param  QuoteAbstractItem[] $items
     * @param  null|string|bool|int|Store $store
     * @return float
     */
    public function getTotalAmounts($items, $store = null)
    {
        $weeeTotal = 0;
        $displayTotalsInclTax = $this->displayTotalsInclTax($store);
        foreach ($items as $item) {
            if ($displayTotalsInclTax) {
                $weeeTotal += $this->getRowWeeeTaxInclTax($item);
            } else {
                $weeeTotal += $item->getWeeeTaxAppliedRowAmount();
            }
        }
        return $weeeTotal;
    }

    /**
     * Returns the base total amount of FPT across all items.
     *
     * Used for displaying the FPT totals line item.
     *
     * @param QuoteAbstractItem[] $items
     * @param mixed $store
     * @return float
     * @since 100.1.0
     */
    public function getBaseTotalAmounts($items, $store = null)
    {
        $baseWeeeTotal = 0;
        $displayTotalsInclTax = $this->displayTotalsInclTax($store);
        foreach ($items as $item) {
            if ($displayTotalsInclTax) {
                $baseWeeeTotal += $this->getBaseRowWeeeTaxInclTax($item);
            } else {
                $baseWeeeTotal += $item->getBaseWeeeTaxAppliedRowAmnt();
            }
        }
        return $baseWeeeTotal;
    }

    /**
     * Get FPT DISPLAY_INCL setting
     *
     * @param  int|null $storeId
     * @return bool
     */
    public function isDisplayIncl($storeId = null)
    {
        return $this->typeOfDisplay(
            WeeeDisplayConfig::DISPLAY_INCL,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW,
            $storeId
        );
    }

    /**
     * Get FPT DISPLAY_INCL_DESCR setting
     *
     * @param  int|null $storeId
     * @return bool
     */
    public function isDisplayInclDesc($storeId = null)
    {
        return $this->typeOfDisplay(
            WeeeDisplayConfig::DISPLAY_INCL_DESCR,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW,
            $storeId
        );
    }

    /**
     * Get FPT DISPLAY_EXCL_DESCR_INCL setting
     *
     * @param  int|null $storeId
     * @return bool
     */
    public function isDisplayExclDescIncl($storeId = null)
    {
        return $this->typeOfDisplay(
            WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW,
            $storeId
        );
    }

    /**
     * Get FPT DISPLAY_EXCL setting
     *
     * @param  int|null $storeId
     * @return bool
     */
    public function isDisplayExcl($storeId = null)
    {
        return $this->typeOfDisplay(
            WeeeDisplayConfig::DISPLAY_EXCL,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW,
            $storeId
        );
    }

    /**
     * Get tax price display settings
     *
     * @param  null|string|bool|int|Store $store
     * @return int
     */
    public function getTaxDisplayConfig($store = null)
    {
        return $this->_taxData->getPriceDisplayType($store);
    }

    /**
     * Return an array of FPT attributes for a bundle product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getWeeeAttributesForBundle($product)
    {
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($product),
                $product
            );
            $insertedWeeeCodesArray = [];
            foreach ($selectionCollection as $selectionItem) {
                $weeeAttributes = $this->getProductWeeeAttributes(
                    $selectionItem,
                    null,
                    null,
                    $product->getStore()->getWebsiteId(),
                    true,
                    false
                );
                $priceTaxDisplay = $this->getTaxDisplayConfig();
                $priceIncludesTax = $this->displayTotalsInclTax();
                foreach ($weeeAttributes as $weeeAttribute) {
                    if ($priceTaxDisplay == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX ||
                        $priceTaxDisplay == \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH) {
                        if ($priceIncludesTax == false) {
                            $weeeAttribute['amount'] = $weeeAttribute['amount_excl_tax'] + $weeeAttribute['tax_amount'];
                        }
                    } elseif ($priceTaxDisplay == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX) {
                        if ($priceIncludesTax == true) {
                            $weeeAttribute['amount'] = $weeeAttribute['amount_excl_tax'];
                        }
                    }
                    $insertedWeeeCodesArray[$selectionItem->getId()][$weeeAttribute->getCode()] = $weeeAttribute;
                }
            }
            return $insertedWeeeCodesArray;
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_storeDisplayConfig = [];
    }
}
