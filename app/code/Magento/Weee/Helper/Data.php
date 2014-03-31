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
 * @package     Magento_Weee
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Weee\Helper;

use Magento\Core\Model\Store;
use Magento\Core\Model\Website;

/**
 * WEEE data helper
 *
 * @category Magento
 * @package  Magento_Weee
 * @author   Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\App\Helper\AbstractHelper
{
    const XML_PATH_FPT_ENABLED = 'tax/weee/enable';

    /**
     * @var array
     */
    protected $_storeDisplayConfig = array();

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $_weeeTax;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Weee\Model\Tax $weeeTax
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Weee\Model\Tax $weeeTax,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Registry $coreRegistry,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_weeeTax = $weeeTax;
        $this->_coreRegistry = $coreRegistry;
        $this->_taxData = $taxData;
        $this->_coreStoreConfig = $coreStoreConfig;
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
        return $this->_coreStoreConfig->getConfig('tax/weee/display', $store);
    }

    /**
     * Get weee amount display type on product list page
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getListPriceDisplayType($store = null)
    {
        return $this->_coreStoreConfig->getConfig('tax/weee/display_list', $store);
    }

    /**
     * Get weee amount display type in sales modules
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getSalesPriceDisplayType($store = null)
    {
        return $this->_coreStoreConfig->getConfig('tax/weee/display_sales', $store);
    }

    /**
     * Get weee amount display type in email templates
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getEmailPriceDisplayType($store = null)
    {
        return $this->_coreStoreConfig->getConfig('tax/weee/display_email', $store);
    }

    /**
     * Check if weee tax amount should be discounted
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function isDiscounted($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag('tax/weee/discount', $store);
    }

    /**
     * Check if weee tax amount should be taxable
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function isTaxable($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag('tax/weee/apply_vat', $store);
    }

    /**
     * Check if weee tax amount should be included to subtotal
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function includeInSubtotal($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag('tax/weee/include_in_subtotal', $store);
    }

    /**
     * Get weee tax amount for product based on shipping and billing addresses, website and tax settings
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   null|\Magento\Customer\Model\Address\AbstractAddress $shipping
     * @param   null|\Magento\Customer\Model\Address\AbstractAddress $billing
     * @param   null|bool|int|string|Website $website
     * @param   bool $calculateTaxes
     * @return  float
     */
    public function getAmount($product, $shipping = null, $billing = null, $website = null, $calculateTaxes = false)
    {
        if ($this->isEnabled()) {
            return $this->_weeeTax->getWeeeAmount($product, $shipping, $billing, $website, $calculateTaxes);
        }
        return 0;
    }

    /**
     * Returns diaplay type for price accordingly to current zone
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int|int[]|null                 $compareTo
     * @param string                         $zone
     * @param Store                          $store
     * @return bool|int
     */
    public function typeOfDisplay($product, $compareTo = null, $zone = null, $store = null)
    {
        if (!$this->isEnabled($store)) {
            return false;
        }
        switch ($zone) {
            case 'product_view':
                $type = $this->getPriceDisplayType($store);
                break;
            case 'product_list':
                $type = $this->getListPriceDisplayType($store);
                break;
            case 'sales':
                $type = $this->getSalesPriceDisplayType($store);
                break;
            case 'email':
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

        if (is_null($compareTo)) {
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
     * @param \Magento\Catalog\Model\Product $product
     * @param null|false|\Magento\Object     $shipping
     * @param null|false|\Magento\Object     $billing
     * @param Website                        $website
     * @param bool                           $calculateTaxes
     * @return \Magento\Object[]
     */
    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTaxes = false
    ) {
        return $this->_weeeTax->getProductWeeeAttributes($product, $shipping, $billing, $website, $calculateTaxes);
    }

    /**
     * Returns applied weee taxes
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return array
     */
    public function getApplied($item)
    {
        if ($item instanceof \Magento\Sales\Model\Quote\Item\AbstractItem) {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $result = array();
                foreach ($item->getChildren() as $child) {
                    $childData = $this->getApplied($child);
                    if (is_array($childData)) {
                        $result = array_merge($result, $childData);
                    }
                }
                return $result;
            }
        }

        /**
         * if order item data is old enough then weee_tax_applied cab be
         * not valid serialized data
         */
        $data = $item->getWeeeTaxApplied();
        if (empty($data)) {
            return array();
        }
        return unserialize($item->getWeeeTaxApplied());
    }

    /**
     * Sets applied weee taxes
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param array $value
     * @return $this
     */
    public function setApplied($item, $value)
    {
        $item->setWeeeTaxApplied(serialize($value));
        return $this;
    }

    /**
     * Returns array of weee attributes allowed for display
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Object[]
     */
    public function getProductWeeeAttributesForDisplay($product)
    {
        if ($this->isEnabled()) {
            return $this->getProductWeeeAttributes($product, null, null, null, $this->typeOfDisplay($product, 1));
        }
        return array();
    }

    /**
     * Get Product Weee attributes for price renderer
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|false|\Magento\Object $shipping Shipping Address
     * @param null|false|\Magento\Object $billing Billing Address
     * @param null|Website $website
     * @param bool $calculateTaxes
     * @return \Magento\Object[]
     */
    public function getProductWeeeAttributesForRenderer(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTaxes = false
    ) {
        if ($this->isEnabled()) {
            return $this->getProductWeeeAttributes(
                $product,
                $shipping,
                $billing,
                $website,
                $calculateTaxes ? $calculateTaxes : $this->typeOfDisplay($product, 1)
            );
        }
        return array();
    }

    /**
     * Returns amount to display
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getAmountForDisplay($product)
    {
        if ($this->isEnabled()) {
            return $this->_weeeTax->getWeeeAmount($product, null, null, null, $this->typeOfDisplay($product, 1));
        }
        return 0;
    }

    /**
     * Returns original amount
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getOriginalAmount($product)
    {
        if ($this->isEnabled()) {
            return $this->_weeeTax->getWeeeAmount($product, null, null, null, false, true);
        }
        return 0;
    }

    /**
     * Adds HTML containers and formats tier prices accordingly to the currency used
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array                          &$tierPrices
     * @return $this
     */
    public function processTierPrices($product, &$tierPrices)
    {
        $weeeAmount = $this->getAmountForDisplay($product);
        $store = $this->_storeManager->getStore();
        foreach ($tierPrices as $index => &$tier) {
            $html = $store->formatPrice(
                $store->convertPrice($this->_taxData->getPrice($product, $tier['website_price'], true) + $weeeAmount),
                false
            );
            $tier['formated_price_incl_weee'] = '<span class="price tier-' .
                $index .
                '-incl-tax">' .
                $html .
                '</span>';
            $html = $store->formatPrice(
                $store->convertPrice($this->_taxData->getPrice($product, $tier['website_price']) + $weeeAmount),
                false
            );
            $tier['formated_price_incl_weee_only'] = '<span class="price tier-' . $index . '">' . $html . '</span>';
            $tier['formated_weee'] = $store->formatPrice($store->convertPrice($weeeAmount));
        }
        return $this;
    }

    /**
     * Check if fixed taxes are used in system
     *
     * @param Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_FPT_ENABLED, $store);
    }

    /**
     * Returns all summed WEEE taxes with all local taxes applied
     *
     * @param \Magento\Object[] $attributes Array of \Magento\Object, result from getProductWeeeAttributes()
     * @return float
     * @throws \Magento\Exception
     */
    public function getAmountInclTaxes($attributes)
    {
        if (is_array($attributes)) {
            $amount = 0;
            foreach ($attributes as $attribute) {
                /* @var $attribute \Magento\Object */
                $amount += $attribute->getAmount() + $attribute->getTaxAmount();
            }
        } else {
            throw new \Magento\Exception('$attributes must be an array');
        }

        return (double)$amount;
    }
}
