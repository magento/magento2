<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Website;
use Magento\Tax\Model\Calculation;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Catalog\Model\Product\Type;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
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
     * Excluding FPT. Including FPT description and final price
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
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Model\CalculationFactory $calculationFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Weee\Model\ResourceModel\Tax $resource
     * @param Config $weeeConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\CalculationFactory $calculationFactory,
        \Magento\Customer\Model\Session $customerSession,
        AccountManagementInterface $accountManagement,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Weee\Model\ResourceModel\Tax $resource,
        \Magento\Weee\Model\Config $weeeConfig,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_storeManager = $storeManager;
        $this->_calculationFactory = $calculationFactory;
        $this->_customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->_taxData = $taxData;
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
        $this->_init(\Magento\Weee\Model\ResourceModel\Tax::class);
    }

    /**
     * @param Product $product
     * @param null|false|\Magento\Framework\DataObject $shipping
     * @param null|false|\Magento\Framework\DataObject $billing
     * @param Website $website
     * @param bool $calculateTax
     * @return float
     */
    public function getWeeeAmount(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = false
    ) {
        $amount = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            $calculateTax
        );
        foreach ($attributes as $attribute) {
            $amount += $attribute->getAmount();
        }
        return $amount;
    }

    /**
     * @param Product $product
     * @param null|false|\Magento\Framework\DataObject $shipping
     * @param null|false|\Magento\Framework\DataObject $billing
     * @param Website $website
     * @return float
     */
    public function getWeeeAmountExclTax(
        $product,
        $shipping = null,
        $billing = null,
        $website = null
    ) {
        $amountExclTax = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            true,
            false
        );
        if (Type::TYPE_BUNDLE !== $product->getTypeId() || $product->getPriceType()) {
            foreach ($attributes as $attribute) {
                $amountExclTax += $attribute->getAmountExclTax();
            }
        }
        return $amountExclTax;
    }

    /**
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeAttributeCodes($forceEnabled = false)
    {
        return $this->getWeeeTaxAttributeCodes(null, $forceEnabled);
    }

    /**
     * Retrieve Wee tax attribute codes
     *
     * @param  null|string|bool|int|Store $store
     * @param  bool $forceEnabled
     * @return array
     */
    public function getWeeeTaxAttributeCodes($store = null, $forceEnabled = false)
    {
        if (!$forceEnabled && !$this->weeeConfig->isEnabled($store)) {
            return [];
        }

        if ($this->_allAttributes === null) {
            $this->_allAttributes = $this->_attributeFactory->create()->getAttributeCodesByFrontendType('weee');
        }
        return $this->_allAttributes;
    }

    /**
     * @param Product $product
     * @param null|false|\Magento\Quote\Model\Quote\Address $shipping
     * @param null|false|\Magento\Quote\Model\Quote\Address $billing
     * @param Website $website
     * @param bool $calculateTax
     * @param bool $round
     * @return \Magento\Framework\DataObject[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $round = true
    ) {
        $result = [];

        $websiteId = $this->_storeManager->getWebsite($website)->getId();
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getWebsite($website)->getDefaultGroup()->getDefaultStore();

        $allWeee = $this->getWeeeTaxAttributeCodes($store);
        if (!$allWeee) {
            return $result;
        }

        /** @var \Magento\Tax\Model\Calculation $calculator */
        $calculator = $this->_calculationFactory->create();

        $customerId = $this->_customerSession->getCustomerId();
        if ($shipping && $shipping->getCountryId()) {
            $customerTaxClass = $shipping->getQuote()->getCustomerTaxClassId();
        } else {
            // if customer logged use it default shipping and billing address
            if ($customerId) {
                $shipping = $this->accountManagement->getDefaultShippingAddress($customerId);
                $billing = $this->accountManagement->getDefaultBillingAddress($customerId);
                $customerTaxClass = null;
            } else {
                $shippingAddressArray = $this->_customerSession->getDefaultTaxShippingAddress();
                $billingAddressArray = $this->_customerSession->getDefaultTaxBillingAddress();
                if (!empty($billingAddressArray)) {
                    $billing = new \Magento\Framework\DataObject($billingAddressArray);
                }
                if (!empty($shippingAddressArray)) {
                    $shipping = new \Magento\Framework\DataObject($shippingAddressArray);
                }
                $customerTaxClass = $this->_customerSession->getCustomerTaxClassId();
            }
        }

        $rateRequest = $calculator->getRateRequest(
            $shipping,
            $billing,
            $customerTaxClass,
            $store,
            $customerId
        );
        $defaultRateRequest = $calculator->getDefaultRateRequest($store);

        $productAttributes = $this->getResource()->fetchWeeeTaxCalculationsByEntity(
            $rateRequest->getCountryId(),
            $rateRequest->getRegionId(),
            $websiteId,
            $store->getId(),
            $product->getId()
        );

        foreach ($productAttributes as $attribute) {
            $value = $attribute['weee_value'];
            if ($value) {
                $taxAmount = $amount = 0;
                $amount = $value;
                $amountExclTax = $value;
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
                        if ($round) {
                            $amountInclTax = $this->priceCurrency->round($amountInclTax);
                        }
                        $taxAmount = $amountInclTax - $amountInclTax / (100 + $currentPercent) * 100;
                        if ($round) {
                            $taxAmount = $this->priceCurrency->round($taxAmount);
                        }
                        $amountExclTax = $amountInclTax - $taxAmount;
                    } else {
                        $appliedRates = $this->_calculationFactory->create()->getAppliedRates($rateRequest);
                        if (count($appliedRates) > 1) {
                            $taxAmount = 0;
                            foreach ($appliedRates as $appliedRate) {
                                $taxRate = $appliedRate['percent'];
                                if ($round) {
                                    $taxAmount += $this->priceCurrency->round($value * $taxRate / 100);
                                } else {
                                    $taxAmount += $value * $taxRate / 100;
                                }
                            }
                        } else {
                            if ($round) {
                                $taxAmount = $this->priceCurrency->round(
                                    $value * $currentPercent / 100
                                );
                            } else {
                                $taxAmount = $value * $currentPercent / 100;
                            }
                        }
                    }
                }

                $one = new \Magento\Framework\DataObject();
                $one->setName(
                    $attribute['label_value'] ? __($attribute['label_value']) : __($attribute['frontend_label'])
                )
                    ->setAmount($amount)
                    ->setTaxAmount($taxAmount)
                    ->setAmountExclTax($amountExclTax)
                    ->setCode($attribute['attribute_code']);

                $result[] = $one;
            }
        }
        return $result;
    }

    /**
     * @param int $countryId
     * @param int $regionId
     * @param int $websiteId
     * @return boolean
     */
    public function isWeeeInLocation($countryId, $regionId, $websiteId)
    {
        return $this->getResource()->isWeeeInLocation($countryId, $regionId, $websiteId);
    }
}
