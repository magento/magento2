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
namespace Magento\Weee\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Website;
use Magento\Customer\Model\Converter as CustomerConverter;
use Magento\Tax\Model\Calculation;

class Tax extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Including FPT only
     */
    const DISPLAY_INCL = 0;

    /**
     * Including FPT and FPT description
     */
    const DISPLAY_INCL_DESCR = 1;

    /**
     * Excluding FPT, FPT description, final price
     */
    const DISPLAY_EXCL_DESCR_INCL = 2;

    /**
     * Excluding FPT
     */
    const DISPLAY_EXCL = 3;

    /**
     * @var array|null
     */
    protected $_allAttributes = null;

    /**
     * @var array
     */
    protected $_productDiscounts = array();

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Tax\Model\CalculationFactory
     */
    protected $_calculationFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var CustomerConverter
     */
    protected $customerConverter;

    /**
     * Weee config
     *
     * @var \Magento\Weee\Model\Config
     */
    protected $weeeConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Model\CalculationFactory $calculationFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Tax\Helper\Data $taxData
     * @param Resource\Tax $resource
     * @param CustomerConverter $customerConverter
     * @param Config $weeeConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\CalculationFactory $calculationFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Weee\Model\Resource\Tax $resource,
        CustomerConverter $customerConverter,
        \Magento\Weee\Model\Config $weeeConfig,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_storeManager = $storeManager;
        $this->_calculationFactory = $calculationFactory;
        $this->_customerSession = $customerSession;
        $this->_taxData = $taxData;
        $this->customerConverter = $customerConverter;
        $this->weeeConfig = $weeeConfig;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Weee\Model\Resource\Tax');
    }

    /**
     * @param Product $product
     * @param null|false|\Magento\Framework\Object $shipping
     * @param null|false|\Magento\Framework\Object $billing
     * @param Website $website
     * @param bool $calculateTax
     * @param bool $ignoreDiscount
     * @return int
     */
    public function getWeeeAmount(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = false,
        $ignoreDiscount = false
    ) {
        $amount = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            $calculateTax,
            $ignoreDiscount
        );
        foreach ($attributes as $attribute) {
            $amount += $attribute->getAmount();
        }
        return $amount;
    }

    /**
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeAttributeCodes($forceEnabled = false)
    {
        return $this->getWeeeTaxAttributeCodes($forceEnabled);
    }

    /**
     * Retrieve Wee tax attribute codes
     *
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeTaxAttributeCodes($forceEnabled = false)
    {
        if (!$forceEnabled && !$this->weeeConfig->isEnabled()) {
            return array();
        }

        if (is_null($this->_allAttributes)) {
            $this->_allAttributes = $this->_attributeFactory->create()->getAttributeCodesByFrontendType('weee');
        }
        return $this->_allAttributes;
    }

    /**
     * @param Product $product
     * @param null|false|\Magento\Sales\Model\Quote\Address $shipping
     * @param null|false|\Magento\Sales\Model\Quote\Address $billing
     * @param Website $website
     * @param bool $calculateTax
     * @param bool $ignoreDiscount
     * @return \Magento\Framework\Object[]
     */
    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $ignoreDiscount = false
    ) {
        $result = array();
        $allWeee = $this->getWeeeTaxAttributeCodes();
        if (!$allWeee) {
            return $result;
        }

        $websiteId = $this->_storeManager->getWebsite($website)->getId();
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getWebsite($website)->getDefaultGroup()->getDefaultStore();

        /** @var \Magento\Tax\Model\Calculation $calculator */
        $calculator = $this->_calculationFactory->create();

        if ($shipping) {
            $customerTaxClass = $shipping->getQuote()->getCustomerTaxClassId();
        } else {
            $customerTaxClass = null;
        }

        $rateRequest = $calculator->getRateRequest(
            $shipping,
            $billing,
            $customerTaxClass,
            $store
        );
        $defaultRateRequest = $calculator->getDefaultRateRequest($store);

        $discountPercent = 0;
        if (!$ignoreDiscount && $this->weeeConfig->isDiscounted($store)) {
            $discountPercent = $this->_getDiscountPercentForProduct($product);
        }

        $productAttributes = $product->getTypeInstance()->getSetAttributes($product);
        foreach ($productAttributes as $code => $attribute) {
            if (in_array($code, $allWeee)) {

                $attributeSelect = $this->getResource()->getReadConnection()->select();
                $attributeSelect->from(
                    $this->getResource()->getTable('weee_tax'),
                    'value'
                )->where(
                    'attribute_id = ?',
                    (int)$attribute->getId()
                )->where(
                    'website_id IN(?)',
                    array($websiteId, 0)
                )->where(
                    'country = ?',
                    $rateRequest->getCountryId()
                )->where(
                    'state IN(?)',
                    array($rateRequest->getRegionId(), '*')
                )->where(
                    'entity_id = ?',
                    (int)$product->getId()
                )->limit(
                    1
                );

                $order = array('state ' . \Magento\Framework\DB\Select::SQL_DESC,
                    'website_id ' . \Magento\Framework\DB\Select::SQL_DESC);
                $attributeSelect->order($order);

                $value = $this->getResource()->getReadConnection()->fetchOne($attributeSelect);
                if ($value) {
                    if ($discountPercent) {
                        $value = $this->priceCurrency->round(
                            $value - $value * $discountPercent / 100
                        );
                    }

                    $taxAmount = $amount = 0;
                    $amount = $value;
                    if ($calculateTax && $this->weeeConfig->isTaxable($store)) {
                        /** @var \Magento\Tax\Model\Calculation $calculator */
                        $defaultPercent = $calculator->getRate(
                            $defaultRateRequest->setProductClassId($product->getTaxClassId())
                        );
                        $currentPercent = $calculator->getRate(
                            $rateRequest->setProductClassId($product->getTaxClassId())
                        );
                        if ($this->_taxData->priceIncludesTax($store)) {
                            $amountInclTax = $value / (100 + $defaultPercent) * (100 + $currentPercent);
                            //round the "golden price"
                            $amountInclTax = $this->priceCurrency->round($amountInclTax);
                            $taxAmount = $amountInclTax - $amountInclTax / (100 + $currentPercent) * 100;
                            $taxAmount = $this->priceCurrency->round($taxAmount);
                        } else {
                            $appliedRates = $this->_calculationFactory->create()->getAppliedRates($rateRequest);
                            if (count($appliedRates) > 1) {
                                $taxAmount = 0;
                                foreach ($appliedRates as $appliedRate) {
                                    $taxRate = $appliedRate['percent'];
                                    $taxAmount += $this->priceCurrency->round($value * $taxRate / 100);
                                }
                            } else {
                                $taxAmount = $this->priceCurrency->round(
                                    $value * $currentPercent / 100
                                );
                            }
                            $taxAmount = $this->priceCurrency->round($value * $currentPercent / 100);
                        }
                    }

                    $one = new \Magento\Framework\Object();
                    $one->setName(__($attribute->getFrontend()->getLabel()))
                        ->setAmount($amount)
                        ->setTaxAmount($taxAmount)
                        ->setCode($attribute->getAttributeCode());

                    $result[] = $one;
                }
            }
        }
        return $result;
    }

    /**
     * @param Product $product
     * @return int
     */
    protected function _getDiscountPercentForProduct($product)
    {
        $website = $this->_storeManager->getStore()->getWebsiteId();
        $group = $this->_customerSession->getCustomerGroupId();
        $key = implode('-', array($website, $group, $product->getId()));
        if (!isset($this->_productDiscounts[$key])) {
            $this->_productDiscounts[$key] = (int)$this->getResource()->getProductDiscountPercent(
                $product->getId(),
                $website,
                $group
            );
        }
        if ($value = $this->_productDiscounts[$key]) {
            return 100 - min(100, max(0, $value));
        } else {
            return 0;
        }
    }

    /**
     * Update discounts for FPT amounts of all products
     *
     * @return $this
     */
    public function updateDiscountPercents()
    {
        $this->getResource()->updateDiscountPercents();
        return $this;
    }

    /**
     * Update discounts for FPT amounts base on products condiotion
     *
     * @param  mixed $products
     * @return $this
     */
    public function updateProductsDiscountPercent($products)
    {
        $this->getResource()->updateProductsDiscountPercent($products);
        return $this;
    }
}
